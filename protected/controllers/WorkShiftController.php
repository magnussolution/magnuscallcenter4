<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class WorkShiftController extends BaseController
{
    public $attributeOrder = 't.id';
    public $filterByUser   = false;

    public function init()
    {
        $this->instanceModel = new WorkShifts;
        $this->abstractModel = WorkShifts::model();

        parent::init();
    }

    public function extraFilterCustom($filter)
    {
        if (!preg_match("/day/", $filter)) {
            $filter .= ' AND day > :keydate';
            $this->paramsFilter[':keydate'] = date('Y-m-d');
        }
        if (Yii::app()->session['isAdmin']) {
            $filter = $this->extraFilterCustomAdmin($filter);
        } elseif (Yii::app()->session['isOperator']) {
            $filter = $this->extraFilterCustomOperator($filter);
        } else if (Yii::app()->session['isTeam']) {
            $filter = $this->extraFilterCustomTeam($filter);
        }
        return $filter;
    }

    public function beforeSave($values)
    {
        if ($this->isNewRecord) {

            if ($values['day_start'] < date('Y-m-d')) {
                echo json_encode(array(
                    'success' => false,
                    'rows'    => array(),
                    'errors'  => array('day_start' => 'Dia de inicio no puede ser anterior a hoy'),
                ));
                exit;
            } else if ($values['day_end'] < $values['day_start']) {
                echo json_encode(array(
                    'success' => false,
                    'rows'    => array(),
                    'errors'  => array('day_end' => 'Dia final es menos que el dia de inicio'),
                ));
                exit;
            }

            $day_start = strtotime($values['day_start']);
            $day_end   = strtotime($values['day_end']);

            $turnoExist = array();

            for ($i = $day_start; $i <= $day_end; $i = $i + 86400) {
                $thisDate = date('Y-m-d', $i);

                $modelWorkShift = WorkShifts::model()->findByAttributes(array('day' => $thisDate));

                if ($modelWorkShift != null) {
                    $turnoExist[] = $thisDate;
                    continue;
                }

                $dw = date("D", strtotime($thisDate));

                $modelWorkShift             = new WorkShifts();
                $modelWorkShift->week_day   = $dw;
                $modelWorkShift->day        = $thisDate;
                $modelWorkShift->turno      = 'M';
                $modelWorkShift->start_time = $values['daily_morning_start_time'];
                $modelWorkShift->stop_time  = $values['daily_morning_stop_time'];

                try {
                    $modelWorkShift->save();

                } catch (Exception $e) {

                }

                $modelWorkShift             = $this->instanceModel;
                $modelWorkShift->week_day   = $dw;
                $modelWorkShift->day        = $thisDate;
                $modelWorkShift->turno      = 'T';
                $modelWorkShift->start_time = $values['daily_afternoon_start_time'];
                $modelWorkShift->stop_time  = $values['daily_afternoon_stop_time'];
                try {
                    $modelWorkShift->save();
                } catch (Exception $e) {

                }
            }

            $msg = count($turnoExist) > 0 ? "<br>Estes dias ya tenia turnos -> " . preg_replace("/array/", '', print_r($turnoExist, true)) : '';

            echo json_encode(array(
                'success' => true,
                'rows'    => array(),
                'msg'     => 'Operation successful.' . $msg,
            ));
            exit;

        } elseif (Yii::app()->session['isOperator'] == true) {
            $values = array_key_exists('rows', $_POST) ? json_decode($_POST['rows'], true) : $_POST;

            if ($values['signup'] == 1) {
                $sql    = "SELECT * FROM pkg_user_workshift WHERE id_user = " . Yii::app()->session['id_user'] . " AND id_workshift = " . $values['id'];
                $result = Yii::app()->db->createCommand($sql)->queryAll();

                if (count($result) > 0) {
                    echo json_encode(array(
                        'success' => false,
                        'errors'  => 'Ya estas registrado en este turno',
                    ));
                    exit;

                } else {
                    $sql = "INSERT INTO pkg_user_workshift (id_user, id_workshift) VALUES (" . Yii::app()->session['id_user'] . ", " . $values['id'] . ")";
                    Yii::app()->db->createCommand($sql)->execute();
                    echo json_encode(array(
                        'success' => true,
                        'rows'    => array(),
                        'msg'     => 'Operation successful.',
                    ));
                    exit;
                }
            } elseif ($values['signup'] == 0) {
                $sql = "DELETE FROM pkg_user_workshift WHERE id_user = " . Yii::app()->session['id_user'] . " AND id_workshift = " . $values['id'];
                Yii::app()->db->createCommand($sql)->execute();
                echo json_encode(array(
                    'success' => true,
                    'rows'    => array(),
                    'msg'     => 'Operation successful.',
                ));
                exit;
            }

        } else {
            return parent::beforeSave();
        }
    }

    public function beforeRead($values)
    {
        //not allow edit workshift after loged
        if (Yii::app()->session['isOperator'] == true && Yii::app()->session['id_campaign'] > 0) {

            echo json_encode(array(
                $this->nameRoot  => array(),
                $this->nameCount => 0,
                $this->nameSum   => null,
            ));
            exit;

        }
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item) {

            $sql     = "SELECT count(*) as count FROM pkg_user_workshift WHERE id_workshift = :id_workshift";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_workshift", $item->id, PDO::PARAM_STR);
            $workShiftResult = $command->queryAll();

            $attributes[$key]              = $item->attributes;
            $attributes[$key]['countUser'] = $workShiftResult[0]['count']; //add

            if (Yii::app()->session['isOperator'] == true) {
                $sql     = "SELECT * FROM pkg_user_workshift WHERE id_workshift = :id_workshift AND id_user = :id_user";
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(":id_workshift", $item->id, PDO::PARAM_STR);
                $command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_STR);
                $workShiftResult = $command->queryAll();

                $attributes[$key]['signup'] = count($workShiftResult) > 0 ? 1 : 0; //add
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                }
            }
        }

        return $attributes;
    }

    public function actionSignup()
    {
        $values = array_key_exists('rows', $_POST) ? json_decode($_POST['rows'], true) : $_POST;

        foreach ($values as $key => $value) {
            Yii::log($value['id'], 'info');

            $sql     = "SELECT * FROM pkg_user_workshift WHERE id_user = :id_user AND id_workshift = :id_workshift";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_workshift", $value['id'], PDO::PARAM_STR);
            $command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_STR);
            $result = $command->queryAll();

            if (count($result) == 0) {
                $sql     = "INSERT INTO pkg_user_workshift (id_user, id_workshift) VALUES (:id_user, :id_workshift)";
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(":id_workshift", $value['id'], PDO::PARAM_STR);
                $command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_STR);
                $command->execute();
            }
        }

        echo json_encode(array(
            'success' => true,
            'rows'    => array(),
            'msg'     => 'Operation successful.',
        ));
    }
}
