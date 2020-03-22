<?php

/**
* 
*/
class PeerFileHandler
{
	
	public static function generateSipFile()
	{
		$select = 'name, defaultuser, accountcode, secret, regexten, amaflags, callerid, language, cid_number, disallow, allow, canreinvite, context, dtmfmode, insecure, nat, qualify, type, host, directrtpsetup, calllimit';
		$modelSip = Sip::model()->findAll(
			array(
				'select'=>$select
				));

		if (is_array($modelSip)>0)
		{
			AsteriskAccess::instance()->writeAsteriskFile($modelSip, '/etc/asterisk/sip_callcenter_user.conf','name');		
			
		}
	}

}