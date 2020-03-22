<?php
/**
 * Acoes do modulo "WebPhone".
 *
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 * 17/08/2012
 */

class WebPhoneController extends BaseController
{
    public function actionRead($asJson = true, $condition = null)
    {

        if (isset(Yii::app()->session['isOperator'])) {
            $sql    = "SELECT * FROM pkg_sip WHERE id_user = " . Yii::app()->session['id_user'];
            $result = Yii::app()->db->createCommand($sql)->queryAll();

        } elseif (isset($_GET['user'])) {

            $user     = $this->sqlInjectSanitize($_GET['user']);
            $password = $this->sqlInjectSanitize($_GET['pass']);

            $sql    = "SELECT * FROM pkg_sip WHERE name = '" . $user . "'";
            $result = Yii::app()->db->createCommand($sql)->queryAll();

            if (!isset($result[0]['name']) || sha1($result[0]['secret']) != $password) {
                exit;
            }

        }
        if (preg_match("/:/", $proxy)) {
            $proxy = explode(":", $proxy);
            $proxy = $proxy[0];
        }

        if (isset($result[0]['name'])) {
            $this->render('index', array(
                'username' => $result[0]['name'],
                'password' => $result[0]['secret'],
                'proxy'    => $proxy,
            ));
        }
    }

    public function actionPlugins()
    {
        echo '<pre>';
        print_r($_SERVER);
        exit;
        header('Location: https://www.linphone.org/releases/linphone-web/' . $_SERVER['PATH_INFO']);
    }

    public function sqlInjectSanitize($data)
    {
        $lowerdata = strtolower($data);
        $data      = str_replace('--', '', $data);
        $data      = str_replace("'", '', $data);
        $data      = str_replace('=', '', $data);
        $data      = str_replace(';', '', $data);
        if (!(strpos($lowerdata, ' or ') === false)) {return false;}
        if (!(strpos($lowerdata, 'table') === false)) {return false;}
        return $data;
    }
}
