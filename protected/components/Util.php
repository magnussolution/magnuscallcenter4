<?php
/**
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 *
 */

class Util
{

    public static function arrayFindByProperty($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subArray) {
                $results = array_merge($results, Util::arrayFindByProperty($subArray, $key, $value));
            }
        }

        return $results;
    }

    public static function getColumnsFromModel($model)
    {

        foreach ($model as $values) {
            $result[] = array_filter($values->attributes);
        }

        return $result;
    }
    public static function getNewUsername()
    {
        $existsUsername = true;

        $config = LoadConfig::getConfig();

        $generate_username = $config['global']['username_generate'];

        if ($generate_username == 1) {
            $length = $config['global']['generate_length'] == 0 ? 5 : $config['global']['generate_length'];
            $prefix = $config['global']['generate_prefix'] == 0 ? '' : $config['global']['generate_prefix'];
            while ($existsUsername) {
                $randUserName   = $prefix . Util::generatePassword($length, false, false, true, false) . "\n";
                $countUsername  = User::model()->count('username LIKE :key', array(':key' => $randUserName));
                $existsUsername = ($countUsername > 0);
            }
        } else {
            $randUserName = Util::getNewUsername2();
        }

        return trim($randUserName);
    }

    public static function getNewUsername2()
    {
        $existsUsername = true;

        while ($existsUsername) {
            $randUserName  = mt_rand(10000, 99999);
            $countUsername = User::model()->count('username LIKE :key', array(':key' => $randUserName));

            $existsUsername = ($countUsername > 0);
        }
        return $randUserName;
    }

    public static function generatePinCallingcard()
    {
        $existsVoucher = true;
        while ($existsVoucher) {
            $randVoucher = Util::generatePassword(6, false, false, true, false);
            $sql         = "SELECT count(id) FROM pkg_voucher
            WHERE voucher LIKE :randVoucher OR (SELECT count(id) FROM pkg_user WHERE callingcard_pin LIKE :randVoucher) > 0";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":randVoucher", $randVoucher, PDO::PARAM_STR);
            $countVoucher = $command->queryAll();

            if (count($countVoucher) > 0) {
                $existsVoucher = false;
                break;
            }
        }
        return $randVoucher;
    }

    public static function generatePassword($tamanho, $maiuscula, $minuscula, $numeros, $codigos)
    {
        $maius = "ABCDEFGHIJKLMNOPQRSTUWXYZ";
        $minus = "abcdefghijklmnopqrstuwxyz";
        $numer = "0123456789";
        $codig = '!@#%';

        $base = '';
        $base .= ($maiuscula) ? $maius : '';
        $base .= ($minuscula) ? $minus : '';
        $base .= ($numeros) ? $numer : '';
        $base .= ($codigos) ? $codig : '';

        srand((float) microtime() * 10000000);
        $password = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $password .= substr($base, rand(0, strlen($base) - 1), 1);
        }

        return $password;
    }

    public static function getNewLock_pin()
    {
        $existsLock_pin = true;

        while ($existsLock_pin) {
            $randLock_Pin  = mt_rand(100000, 999999);
            $countLock_pin = Signup::model()->count(array(
                'condition' => "callingcard_pin LIKE '$randLock_Pin'",
            ));

            $existsLock_pin = ($countLock_pin > 0);
        }
        return $randLock_Pin;
    }

    public static function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i          = 0;
        $key_array  = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]  = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function unique_multidim_obj($obj, $key)
    {
        $temp_array = array();
        $i          = 0;
        $key_array  = array();

        foreach ($obj as $val) {
            if (!in_array($val->$key, $key_array)) {
                $key_array[$i]  = $val->$key;
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function getDataFromMethodPay($code)
    {
        $code = explode("-", $code);
        if (count($code) != 3) {
            return false;
        } else {
            return array(
                'date'     => $code[0],
                'username' => $code[1],
                'id_user'  => $code[2],
            );
        }
    }

    public static function detectTurno($modelCampaign)
    {
        $currentyTime = date('H:i:s');
        if ($currentyTime > $modelCampaign->daily_morning_start_time &&
            $currentyTime < $modelCampaign->daily_morning_stop_time) {
            $turno = 'M';
        } elseif ($currentyTime > $modelCampaign->daily_afternoon_start_time &&
            $currentyTime < $modelCampaign->daily_afternoon_stop_time) {
            $msg   = "$currentyTime -> the operator is in the afternoon\n";
            $turno = 'T';
        } else {
            $msg = Yii::t('yii',
                "Você nāo pode entrar nesta campanha porque nao esta dentro de nenhum turno.") .
            $modelCampaign->name . Yii::t('yii', ".<br><br>Selecione outra campanha");

            echo json_encode(array(
                'success' => false,
                'msg'     => $msg,
            ));
            exit;
        }

        return $turno;
    }
}
