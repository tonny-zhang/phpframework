<?php
/**
 * 新模型层
 *
 */
class Ml_TempImage {
	protected $dtData;
	protected $uploadConfig = array();
	public function __construct(){
		$this->dtData = new Dt_Common('default', 'fan_temp_image', false, false, false);
		$this->uploadConfig = Core::GetConfig( 'Upload_Config' );
	}
	
	public function uploadImage($tempName, $fileName, $type = 'show', $filedata = '') {
		if ( !$this->uploadConfig[$type] ) return -1;

		$imgHash = md5(uniqid(rand()));
		if(empty($filedata)) {
			$filePath = $this->moveToTemp($tempName, $fileName, $imgHash);
		} else {
			$filePath = $this->moveToTempForIpone($filedata, $imgHash);
		}
		if ( !$filePath ) return -2;

		$imgTime = time();
		$result = $this->send($filePath, $imgHash, $imgTime, $type);
		$result = json_decode($result, true);

		@unlink( $filePath );
		if ( !is_array( $result ) ) return -3;

		$result['img_hash'] = $imgHash;
		$result['img_time'] = $imgTime;
		if ( $result['status'] == '200' ) {
			$this->addTempImage($imgHash, $imgTime, $type);
		}
		return $result;
	}
	
	public function uploadImageOnly($type, $originFile, $postData) {
		$filePath = $this->uploadConfig['tmp_dir'] . md5(uniqid(rand())) . ".jpg";
		@file_put_contents($filePath, $originFile);
		
		$resultRaw = $this->send($filePath, false, false, $type, $postData);
		$result = json_decode($resultRaw, true);

		@unlink($filePath);
		if ( !is_array( $result ) ) {
			$tmp = array();
			$tmp['status'] = 404;
			$tmp['info'] = $resultRaw;
			$result = $tmp;
		}
		return $result;
	}
	
	public function delImage($imgHash, $imgTime, $type = 'show') {		
		if ( !$this->uploadConfig[$type] ) return -1;

		$config = $this->uploadConfig[$type];
		$deleteUrl = sprintf($config['delete'], $imgHash, $imgTime);

		$ctx = stream_context_create( array(  
			'http' => array(  
				'timeout' => 2 //设置一个超时时间，单位为秒  
			)
		) );

		$result = file_get_contents( $deleteUrl, 0, $ctx );
		if ( $result != 200 ) return -2;
		return 200;
	}
	
	protected function addTempImage($imgHash, $imgTime, $type) {
		$data = array();
		$data['img_hash'] = $imgHash;
		$data['img_time'] = $imgTime;
		$data['type'] = $type;
		$data['add_time'] = time();
		return $this->dtData->addCommonInfo($data);
	}

	public function delTempImage($imgHash) {
		return $this->dtData->delCommonInfoNoCheck('img_hash', $imgHash);
	}
	
	protected function send($filePath, $imgHash, $imgTime, $type, $postData = array()) {
		$config = $this->uploadConfig[$type];

		if ( $imgHash ) $postData['name'] = $imgHash;
		if ( $imgTime ) $postData['time'] = $imgTime;
		$postData['imagefile'] = "@" . realpath( $filePath );

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $config['upload'] );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $postData );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/4.0" );

		$result = curl_exec( $curl );
		return $result;
	}

	protected function moveToTemp( $tempName, $fileName, $hash=NULL ) {
		$ext = strtolower( end( explode( '.', $fileName) ) );

		$newTempPath = $this->uploadConfig['tmp_dir'] . $hash . '.' . $ext;
		if ( @move_uploaded_file( $tempName, $newTempPath ) ) {
			return $newTempPath;
		}
		return false;
	}
	
	protected function moveToTempForIpone($data, $hash=NULL) {
		$newTempPath = $this->uploadConfig['tmp_dir'] . $hash . '.jpg';
		try{
			$hand = fopen($newTempPath,'wb');
			fwrite($hand, $data);
			fclose($hand);
			return $newTempPath;
		} catch(Exception $ex) {
			return false;
		}
	}
	
}