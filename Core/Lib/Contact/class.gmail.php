 <?php
include "ContactBase.php";
class Gmail extends ContactBase
{
	public function GetLoginInfo( &$cookie )
	{
		$content = $this -> GetUrl( "https://www.google.com/accounts/ServiceLogin", "GET", false, $cookie );
		return $content['content'];
	}
	public function GetAddressList( $uname, $upass )
	{
		$content = $this -> GetLoginInfo( $cookie );
		$p = "/<input type=\"hidden\" name=\"dsh\" id=\"dsh\"\s*value=\"(.+?)\"\s*\/>/i";
		preg_match( $p, $content, $arr );
		$dsh = $arr[1];
		$p = '/<input type="hidden"\s*name="GALX"\s*value="(.+?)"\s*\/>/i';
		preg_match( $p, $content, $arr );
		$GALX = $arr[1];
		$fields = "dsh=" . $dsh . "&GALX=" . $GALX . "&timeStmp=&secTok=&Email=" . $uname . "&Passwd=" . $upass . "&rmShown=1&signIn=Sign in&asts=";
		$content = $this -> GetUrl( "https://www.google.com/accounts/ServiceLoginAuth", "POST", $fields, $cookie );

		$content = $this -> GetUrl( "http://mail.google.com/mail/contacts/data/contacts?thumb=true&groups=true&show=ALL&enums=true&psort=Name&max=300&out=js&rf=&jsx=true", "GET", false, $cookie ); 
		// die($content['content']);
		$jsoncode = str_replace( "while (true); &&&START&&&", "", $content['content'] );
		$jsoncode = str_replace( "&&&END&&&", "", $jsoncode );
		$json = json_decode( trim( $jsoncode ), true );
		//$p = "/([\\w_-])+@([\\w])+([\\w.]+)/";
		//$rs = preg_match_all( $p, $content['content'], $arr );

		$result = array();

		if ( $json['Body']['Contacts']  )
		{
			foreach( $json['Body']['Contacts'] as $a )
			{
				if ( $a['Emails'] )
				{
					$rs = array();

					$rs['nickname'] = $a['DisplayName'] ?: $a['Emails'][0];
					$rs['email'] = $a['Emails'][0]['Address'];
					$result[] = $rs;
				}
			}
		}

		return $result;
	}
}
