<?php
/**
 * Arquivo de configuracao da aplicacao
 * Propriedade da classe CWebApplication
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/06/2012
 */
$configFile = '/etc/asterisk/res_config_mysql.conf';
$array      = parse_ini_file($configFile);
return array(
    'basePath'       => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'           => 'MagnusCallCenter',
    'preload'        => array('log'),
    'language'       => 'pt_BR',
    'sourceLanguage' => 'pt_BR',
    'import'         => array(
        'application.models.*',
        'application.components.*',
        'application.components.AGI.*',
        'ext.yii-mail.YiiMailMessage',
        'ext.phpAGI.AGI',
        'ext.phpAGI.AGI_AsteriskManager',
        'ext.fpdf.FPDF',
    ),
    'components'     => array(
        'mail'         => array(
            'class'            => 'ext.yii-mail.YiiMail',
            'transportType'    => 'smtp',
            'transportOptions' => array(
                'host'       => '',
                'encryption' => '',
                'username'   => '',
                'password'   => '',
                'port'       => '',
                'encryption' => '',
            ),
            'viewPath'         => 'application.views.mails',
            'logging'          => true,
            'dryRun'           => false,
        ),
        'db'           => array(
            'connectionString' => 'mysql:host=' . $array['dbhost'] . ';dbname=' . $array['dbname'] . '',
            'emulatePrepare'   => true,
            'username'         => $array['dbuser'],
            'password'         => $array['dbpass'],
            'charset'          => 'utf8',
        ),
        'coreMessages' => array(
            'basePath' => 'locale/php',
        ),
        'log'          => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'   => 'CFileLogRoute',
                    'logFile' => 'cron.log',
                    'levels'  => 'error',
                ),
            ),
        ),
    ),
);
