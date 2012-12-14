<?php
class Helper_Mail
{
	static function MailTo( $to, $subject, $content )
	{
		if ( class_exists( 'GearmanClient', false ) )
		{
			$gearmanConfig = Core::GetConfig( 'gearman' );

			$GearManClient = new GearmanClient();
			@$GearManClient->addServer( $gearmanConfig['host'], $gearmanConfig['port'] );
			@$GearManClient->doBackground(
				'www_mail', 
				serialize( array(
					'to' => $to,
					'subject' => $subject,
					'content' => $content,
				)
			) );

			return true;
		}

		$Mail = new PHPMailer();
		$Mail->IsSMTP();
		$Mail->SMTPAuth = true; 
		// $Mail->SMTPSecure = 'ssl';
		$Mail->Host = 'smtp.exmail.qq.com';
		$Mail->Port = 25;
		$Mail->CharSet = 'utf-8';
		$Mail->Username = 'no-reply@fandongxi.com';
		$Mail->Password = 'lizifdx123';

		$Mail->From = 'no-reply@fandongxi.com';
		$Mail->FromName = '翻东西';

		$Mail->Subject = $subject;
		$Mail->WordWrap = 50;
		$Mail->MsgHTML( $content );

		if ( is_array( $to ) )
		{
			foreach( $to as $m )
			{
				$Mail->AddAddress( $m );
			}
		}
		else
		{
			$Mail->AddAddress( $to );
		}

		$Mail->IsHTML( true );
		return $Mail->Send();
	}


	static function NoReplyMailTo( $to, $subject, $content )
	{
		if ( class_exists( 'GearmanClient', false ) )
		{
			$gearmanConfig = Core::GetConfig( 'gearman' );

			$GearManClient = new GearmanClient();
			@$GearManClient->addServer( $gearmanConfig['host'], $gearmanConfig['port'] );
			@$GearManClient->doBackground(
				'www_mail', 
				serialize( array(
					'to' => $to,
					'subject' => $subject,
					'content' => $content,
				)
			) );

			return true;
		}
		
		$mailConfig = Core::GetConfig( 'mail', 'invite' );
		
		$Mail = new PHPMailer();
		$Mail->IsSMTP();
		$Mail->SMTPAuth = false;

		$Mail->Host = $mailConfig['host'];
		$Mail->Port = $mailConfig['port'];
		$Mail->Helo = 'mail.fandongxi.com';
		$Mail->CharSet = 'utf-8';

		$Mail->From = 'no-reply@fandongxi.com';
		$Mail->FromName = '翻东西';

		$Mail->Subject = $subject;
		$Mail->WordWrap = 50;
		$Mail->MsgHTML( $content );

		if ( is_array( $to ) )
		{
			foreach( $to as $m )
			{
				$Mail->AddAddress( $m );
			}
		}
		else
		{
			$Mail->AddAddress( $to );
		}

		$Mail->IsHTML( true );
		return $Mail->Send();
	}
	
	static function MailToForApp( $to, $subject, $content, $sender = "huodong@fandongxi.com" )
	{
		$mailConfig = Core::GetConfig( 'mail', 'invite' );
		
		$Mail = new PHPMailer();
		$Mail->IsSMTP();
		$Mail->SMTPAuth = false;

		$Mail->Host = $mailConfig['host'];
		$Mail->Port = $mailConfig['port'];
		$Mail->Helo = 'mail.fandongxi.com';
		$Mail->CharSet = 'utf-8';

		$Mail->From = $sender;
		$Mail->FromName = '翻东西';

		$Mail->Subject = $subject;
		$Mail->WordWrap = 50;
		$Mail->MsgHTML( $content );

		if ( is_array( $to ) )
		{
			foreach( $to as $m )
			{
				$Mail->AddAddress( $m );
			}
		}
		else
		{
			$Mail->AddAddress( $to );
		}

		$Mail->IsHTML( true );
		return $Mail->Send();
	}
}
