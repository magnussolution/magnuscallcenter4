<?php
/**
 * Acoes do modulo "Campaign".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2018 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 28/10/2012
 * index.php/mBillingSoftphone/read?l=felipe|137DCEC44002170DB2D2DCD9C70DBEBF
 */

class MBillingSoftphoneController extends Controller
{
    public $attributeOrder = 'id';
    public $filterByUser   = false;
    private $l;

    public function actionRead($asJson = true, $condition = null)
    {

        if (isset($_GET['l'])) {
            $data = explode('|', $_GET['l']);
            $user = $data[0];
            $pass = $data[1];

            $modelUser = User::model()->find('username = :key', array(':key' => $user));

            if (!count($modelUser)) {
                echo 'false';
                exit;
            }

            if (strtoupper(md5($modelUser->password)) != strtoupper($pass)) {
                echo 'false';
                exit;
            }
            $result                 = [];
            $result[0]['username']  = 'username';
            $result[0]['firstname'] = $modelUser->name;
            $result[0]['lastname']  = '';

            $result[0]['credit']   = $this->actionOperatorCheckStatus($modelUser->id);
            $result[0]['currency'] = '';

            if (count($result) == 0) {
                echo 'false';
                exit;
            }
            //$result[0]['version'] = 'MPhone-1.0.5';

            $result = json_encode(array(
                $this->nameRoot  => $result,
                $this->nameCount => 1,
                $this->nameSum   => '',
            ));

            $result = json_decode($result, true);

            echo '<pre>';
            print_r($result);
        }
    }
    public function actionOperatorCheckStatus($id_user)
    {

        $modelOperatorStatus = OperatorStatus::model()->find("id_user = " . $id_user);

        if (count($modelOperatorStatus) > 0) {
            if ($modelOperatorStatus->categorizing == 1) {
                $status = 'CATEGORIZING';
            } else if ($modelOperatorStatus->queue_paused == 1) {
                $status = 'PAUSED';
            } else {
                switch ($modelOperatorStatus->queue_status) {
                    case 0:
                        $status = 'UNKNOWN';
                        break;
                    case 1:
                        $status = 'NOT_INUSE';
                        break;
                    case 2:
                        $status = 'INUSE';
                        break;
                    case 3:
                        $status = 'BUSY';
                        break;
                    case 4:
                        $status = 'INVALID';
                        break;
                    case 5:
                        $status = 'NOT LOGED ON SOFTPHONE';
                        break;
                    case 6:
                        $status = 'RINGING';
                        break;
                    case 7:
                        $status = 'RINGINUSE';
                        break;
                    case 8:
                        $status = 'ONHOLD';
                        break;
                    default:
                        $status = 'UNKNOWN';
                        break;
                }
            }

        } elseif (count($modelOperatorStatus) == 0) {
            $status = 'NO CAMPAING';
        }
        //check if exist a mandaroyBreak
        Yii::import('application.controllers.BreaksController');
        $status = BreaksController::checkMandatoryBreak($status);

        return $status[0];
    }

}
