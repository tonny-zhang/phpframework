
<?php
/**
 * 新模型层专访类
 *
 */
class Ml_Interview {
	protected $dtData;
	protected static $Gearman = NULL;
	private static $date_flag;
	public function __construct(){
		$this->dtData = new Dt_Interview();
	}

	public function getTotal(){
		return $this->dtData->getCommonTotal();
	}
	public function getList($offset = 0, $limit = 0){
		return $this->dtData->getCommonList($offset,$limit,'date_flag');
	}
	private function compareDateFlag($interview){
		return $interview['date_flag']<self::$date_flag;
	}
	/**
	 * 得到往期专访
	 */
	public function getBeforeList($date_flag,$num = 2){
		$list = $this->getList();
		self::$date_flag = $date_flag;
		$beforeList = array_filter($list,array($this,'compareDateFlag'));
		return array_slice($beforeList,0,$num);
	}
	public function getInterviewById($id){
		return $this->dtData->getCommonInfo($id);
	}
	public function getInterviewByDateFlag($date_flag){
		return $this->dtData->getInterviewByDateFlag($date_flag,'date_flag');
	}
	public function addInterview($interviewInfo){
		if( is_array($interviewInfo['content']) || is_object($interviewInfo['content'])){
			$interviewInfo['content'] = json_encode($interviewInfo['content']);
		}
		if( is_array($interviewInfo['bg_style']) || is_object($interviewInfo['bg_style'])){
			$interviewInfo['bg_style'] = json_encode($interviewInfo['bg_style']);
		}
		return $this->dtData->addCommonInfo($interviewInfo);
	}
	public function deleInterview($interviewId){
		return $this->dtData->deleInterview($interviewId);
	}
	public function updateInterview($interviewInfo){
		$interviewId = $interviewInfo['id'];
		if( is_array($interviewInfo['content']) || is_object($interviewInfo['content'])){
			$interviewInfo['content'] = json_encode($interviewInfo['content']);
		}
		if( is_array($interviewInfo['bg_style']) || is_object($interviewInfo['bg_style'])){
			$interviewInfo['bg_style'] = json_encode($interviewInfo['bg_style']);
		}
		return $this->dtData->updateInterview($interviewId,$interviewInfo);
	}
}