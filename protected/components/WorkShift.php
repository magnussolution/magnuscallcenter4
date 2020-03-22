<?php
/**
 * Classe de com funcionalidades globais
 *
 * MagnusCallCenter <info@magnussolution.com>
 * 08/06/2013
 */

class WorkShift
{

    //verifica se o operador cumprio os WorkShift marcada
    public static function checkTime()
    {

        if (Yii::app()->session['isOperator']) {
            $msg          = '';
            $currentyTime = date('H:i:s');

            $sql            = "SELECT config_value FROM pkg_configuration WHERE config_key = 'tardanza'";
            $tardanzaResult = Yii::app()->db->createCommand($sql)->queryAll();
            $valorTardanza  = $tardanzaResult[0]['config_value'];

            $sql             = "SELECT * FROM pkg_work_shift WHERE day = '" . date('Y-m-d') . "' ORDER BY turno ASC";
            $workShiftResult = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($workShiftResult) == 0) {
                return;
            }

            if ($currentyTime < $workShiftResult[0]['stop_time']) {
                $turno        = 'M';
                $id_workshift = $workShiftResult[0]['id'];
                $start_time   = $workShiftResult[0]['start_time'];
            } elseif ($currentyTime < $workShiftResult[1]['stop_time']) {
                $turno        = 'T';
                $id_workshift = $workShiftResult[1]['id'];
                $start_time   = $workShiftResult[1]['start_time'];
            }

            $sql             = "SELECT * FROM pkg_user_workshift WHERE id_workshift = " . $id_workshift . " AND id_user =" . Yii::app()->session['id_user'];
            $workShiftResult = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($workShiftResult) > 0 && $workShiftResult[0]['status'] == 0) {

                if (isset($turno)) {
                    if ($currentyTime > $start_time) {
                        //llego atrasado, verifico si ya fue cobrado una tardanza en este turno
                        $sql                  = "SELECT id FROM pkg_billing WHERE `date` = '" . date('Y-m-d') . "' AND id_user_online IS NULL AND id_campaign = -1 AND turno = '$turno' AND id_user = " . Yii::app()->session['id_user'];
                        $workShiftCheckResult = Yii::app()->db->createCommand($sql)->queryAll();

                        if (count($workShiftCheckResult) == 0) {

                            $description = "Usted llego a las " . date('Y-m-d H:i:s') . " y el WorkShift inicio a las $start_time";

                            $sql = "INSERT INTO pkg_billing (id_user, id_campaign, `date`, total_price, turno, description) VALUES (" . Yii::app()->session['id_user'] . ", -1, '" . date('Y-m-d') . "', '-$valorTardanza', '$turno', '$description')";
                            try {
                                Yii::app()->db->createCommand($sql)->execute();
                            } catch (Exception $e) {

                            }

                            $msg = ". <font color=red>Usted llego atrasado, sera descontado una tardanza</font>";
                        }
                    }
                    $sql = "UPDATE pkg_user_workshift SET status = 1 WHERE id = " . $workShiftResult[0]['id'];
                    Yii::app()->db->createCommand($sql)->execute();
                }
            }
        }

        return $msg;
    }

    //avisa o operador que tiene aistencia disponivel para se anotar
    public static function check()
    {

        if (Yii::app()->session['isOperator']) {

            if (date('d') <= 15) {
                //quincena Actual
                $quinzenaActual = "day >= '" . date('Y-m-') . "01' AND day <= '" . date('Y-m-') . "15' ";
                $quinzenaNext   = "day >= '" . date('Y-m-') . "16' AND day <= '" . date('Y-m-') . "31' ";
            } else {
                $nextMonth = date('Y-m-d', strtotime('+1 month'));

                $quinzenaActual = "day >= '" . date('Y-m-') . "16' AND day <= '" . date('Y-m-') . "31' ";
                $quinzenaNext   = "day >= '" . date('Y-') . $nextMonth . "-01' AND day <= '" . date('Y-') . $nextMonth . "15' ";
            }

            //verifica se existe aberto na quinzena atual
            $sql                  = "SELECT * FROM pkg_work_shift WHERE " . $quinzenaActual;
            $resultQuinzenaActual = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($resultQuinzenaActual)) {
                //existe turno, verificar se o operador esta cadastrado em algum
                $sql                 = "SELECT * FROM pkg_user_workshift a JOIN pkg_work_shift b ON a.id_workshift = b.id WHERE a.id_user = " . Yii::app()->session['id_user'] . " AND " . $quinzenaActual;
                $resultWorkShiftUser = Yii::app()->db->createCommand($sql)->queryAll();
                if (count($resultWorkShiftUser) < 1) {
                    Yii::app()->session['noticeSignupActually'] = true;
                } else {
                    Yii::app()->session['noticeSignupActually'] = false;
                }
            } else {
                Yii::app()->session['noticeSignupActually'] = false;
            }

            //verifica se existe aberto na proxima quinzena
            $sql                = "SELECT * FROM pkg_work_shift WHERE " . $quinzenaNext;
            $resultQuinzenaNext = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($resultQuinzenaNext)) {
                //existe turno, verificar se o operador esta cadastrado em algum
                $sql                 = "SELECT * FROM pkg_user_workshift a JOIN pkg_work_shift b ON a.id_workshift = b.id WHERE a.id_user = " . Yii::app()->session['id_user'] . " AND " . $quinzenaNext;
                $resultWorkShiftUser = Yii::app()->db->createCommand($sql)->queryAll();
                if (count($resultWorkShiftUser) < 1) {
                    Yii::app()->session['noticeSignupNext'] = true;
                } else {
                    Yii::app()->session['noticeSignupNext'] = false;
                }
            } else {
                Yii::app()->session['noticeSignupActually'] = false;
            }

        }
    }

}
