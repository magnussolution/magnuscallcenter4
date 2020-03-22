<?php
/**
 * Arquivo que dara inicio a aplicacoa e fara inclusao do framewrok Yii.
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/11/2011
 */

# Definicao do framework e do arquivo de config da aplicacao
$yii=dirname(__FILE__).'/yii/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/cron.php';

# Remover no ambiente de producao
defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
Yii::createConsoleApplication($config)->run();

