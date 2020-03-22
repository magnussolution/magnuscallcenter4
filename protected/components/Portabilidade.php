<?php
/**
 *
 */
class Portabilidade
{

    public function getDestination($destination, $id_phonebook = null, $massiveCall = false, $cron = false)
    {
        if (!isset($cron)) {
            define('LOGFILE', 'protected/runtime/Portabilidade.log');
            define('DEBUG', 0);
        }

        $destination_e164 = $destination;

        $phoneBookTable = $massiveCall == true ? 'pkg_massive_call_phonebook' : 'pkg_phonebook';

        $sql    = "SELECT portabilidadeFixed, portabilidadeMobile FROM $phoneBookTable  WHERE id = '$id_phonebook' LIMIT 1";
        $result = Yii::app()->db->createCommand($sql)->queryAll();

        $mobile = false;
        $fixed  = false;

        if (strlen($destination) >= 10 && substr($destination, 0, 2) == 55) {

            if (in_array(substr($destination, 2, 1), array(1, 2, 9)) && substr($destination, 4, 1) >= 7) {
                $mobile = true;
            } else if (substr($destination, 4, 1) >= 7) {
                $mobile = true;
            } else {
                $fixed = true;
            }

            if (($mobile == true && $result[0]['portabilidadeMobile'] == 1) || ($fixed == true && $result[0]['portabilidadeFixed'] == 1)) {
                $sql            = 'SELECT config_value FROM pkg_configuration WHERE config_key = "portabilidadeUsername"';
                $resultUsername = Yii::app()->db->createCommand($sql)->queryAll();

                $sql        = 'SELECT config_value FROM pkg_configuration WHERE config_key = "portabilidadePassword"';
                $resultPass = Yii::app()->db->createCommand($sql)->queryAll();

                if (strlen($resultUsername[0]['config_value']) > 3 && strlen($resultPass[0]['config_value']) > 3) {
                    $user        = $resultUsername[0]['config_value'];
                    $pass        = $resultPass[0]['config_value'];
                    $url         = "http://portabilidadecelular.com/painel/consulta_numero.php?user=" . $user . "&pass=" . $pass . "&seache_number=" . $destination . "";
                    $operadora   = file_get_contents($url);
                    $destination = $operadora . $destination;
                } else {
                    $ddd = substr($destination, 2);

                    $sql = "SELECT company FROM pkg_portabilidade  WHERE number = '$ddd' ORDER BY id DESC LIMIT 1";
                    try {
                        $result = Yii::app()->db->createCommand($sql)->queryAll();
                    } catch (Exception $e) {
                        return $destination;
                    }

                    if (is_array($result) && isset($result[0]['company'])) {
                        $destination = $company . $result[0]['company'];
                    } else {
                        echo $destination;
                        if (strlen($ddd) == 11) {
                            $sql = "SELECT company FROM pkg_portabilidade_prefix WHERE number = " . substr($ddd, 0, 7) . " ORDER BY number DESC LIMIT 1";
                        } else {
                            $sql = "SELECT company FROM pkg_portabilidade_prefix WHERE number = " . substr($ddd, 0, 6) . " ORDER BY number DESC LIMIT 1";
                        }
                        try {
                            $result = Yii::app()->db->createCommand($sql)->queryAll();
                        } catch (Exception $e) {
                            return $destination;
                        }

                        if (is_array($result) && isset($result[0]['company'])) {
                            $destination = $result[0]['company'] . $destination;
                        } else {
                            $company     = 55399;
                            $destination = $company . $destination;
                        }
                    }
                    if (!isset($result[0]['company'])) {
                        $company     = 55399;
                        $destination = $company . $destination;
                        echo 'Operadora nao encontrada';
                    }
                    //nao aceita chamadas com 8 digitos nos DDD com nono digito, somente NEXTEL
                    else if ($result[0]['company'] != 55377 && strlen($ddd) == 10 && in_array(substr($ddd, 0, 1), array('1', '2', '9'))) {
                        $company     = 55399;
                        $destination = $company . $destination;
                    }
                }
            }
        }
        return $destination;
    }
}
