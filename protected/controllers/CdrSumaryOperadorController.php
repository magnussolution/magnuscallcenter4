<?php
/**
 * Acoes do modulo "CdrSummaryOperator".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/04/2017
 */

class CdrSumaryOperadorController extends BaseController
{
    public $attributeOrder = 'login_type, t.turno ASC, t.id_user ASC, t.starttime ASC';
    public $group          = 't.id_user, t.id_campaign , t.turno';
    public $join           = 'JOIN pkg_user  ON t.id_user = pkg_user.id
                            JOIN pkg_campaign ON t.id_campaign = pkg_campaign.id';
    public $extraValues = array('idUser' => 'username,name', 'idCampaign' => 'name');
    public $day;

    public $user_id;
    public $campaign_id;

    public $promedio = array();

    public $fieldsFkReport = array(
        'id_user'     => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => "CONCAT(username, ' ', name) ",
        ),
        'id_campaign' => array(
            'table'       => 'pkg_campaign',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );

    public function init()
    {
        if (isset(Yii::app()->session['group'])) {
            unset(Yii::app()->session['group']);
        }

        $this->instanceModel = new LoginsCampaign;
        $this->abstractModel = LoginsCampaign::model();
        $this->titleReport   = Yii::t('yii', 'Calls Summary by operator');

        parent::init();
    }

    public function extraFilterCustomTeam($filter)
    {

        $sql = "SELECT id FROM pkg_campaign WHERE id_team = " . Yii::app()->session['id_team'];

        $filter .= ' AND t.id_campaign IN ( ' . $sql . ')';

        return $filter;
    }

    public function extraFilterCustom($filter)
    {

        if (!preg_match("/starttime/", $filter)) {
            $filter .= " AND (starttime > :keyday AND starttime < :keydayend)";
            $this->paramsFilter[':keyday']    = date('Y-m-d');
            $this->paramsFilter[':keydayend'] = date('Y-m-d') . ' 23:59:59';

        }

        if (Yii::app()->session['isOperator']) {
            $filter = $this->extraFilterCustomOperator($filter);
        } else if (Yii::app()->session['isTeam']) {
            $filter = $this->extraFilterCustomTeam($filter);
        }

        return $filter;
    }

    public function setAttributesModels($attributes, $models)
    {
        /*
        tenho tabela onde tem todos os registros de login e pausa

        mostrar relatorio agrupodo por usuario, campanha e turno
         */
        //echo '<pre>';
        // print_r($attributes);

        /*
        72 id
        82 campanha
        21 usuario
        NULL
        2017-04-17 12:54:50
        0000-00-00 00:00:00
        0
        M  turno
        LOGIN
         */
        for ($i = 0; $i < count($attributes) && is_array($attributes); $i++) {

            $date = explode(" ", $attributes[$i]['starttime']);
            $day  = $date[0];

            $attributes[$i]['day'] = $day;

            $modelLoginsCampaign = LoginsCampaign::model()->findAll(
                array(
                    'condition' => "id_campaign = " . $attributes[$i]['id_campaign'] . " AND id_user = '" . $attributes[$i]['id_user'] . "' AND (starttime > '$day' AND starttime < '$day 23:59:59') AND turno = '" . $attributes[$i]['turno'] . "'",
                    'order'     => 'id ASC',
                )
            );

            $modelCampaign = Campaign::model()->findByPk($attributes[$i]['id_campaign']);

            $attributes[$i]['status'] = $this->getStatus($attributes[$i], $modelCampaign,
                $models[$i]->idUser->id_campaign, $modelLoginsCampaign);

            $sumTotalTime = $this->sumTotalTime($modelLoginsCampaign, $attributes[$i]['status']);

            $attributes[$i]['total_time']  = $sumTotalTime['totalTime'];
            $attributes[$i]['total_pause'] = $sumTotalTime['totalTimePause'];

            $morningStart   = $modelCampaign->daily_morning_start_time;
            $morningStop    = $modelCampaign->daily_morning_stop_time;
            $afternoonStart = $modelCampaign->daily_afternoon_start_time;
            $afternoonStop  = $modelCampaign->daily_afternoon_stop_time;

            if ($attributes[$i]['turno'] == 'M') {
                $filterDateTurno = "(starttime > '" . $day . ' ' . $morningStart . "'  AND stoptime < '" . $day . ' ' . $morningStop . "')";
            } elseif ($attributes[$i]['turno'] == 'T') {
                $filterDateTurno = "(starttime > '" . $day . ' ' . $afternoonStart . "'  AND stoptime < '" . $day . ' ' . $afternoonStop . "')";
            }

            $filterDateTurno .= " AND id_user = '" . $attributes[$i]['id_user'] . "' AND id_campaign = " . $modelCampaign->id;

            $modelCdr = Cdr::model()->find(array(
                'select'    => 'sum(sessiontime) AS sessiontime, count(*) as dnid, sum(case when sessiontime>0 then 1 else 0 end) as sessionid',
                'condition' => $filterDateTurno,
                'group'     => 'id_user',
            ));

            $attributes[$i]['totalCalls'] = isset($modelCdr->dnid) ? $modelCdr->dnid : 0;
            //ttime in call
            $attributes[$i]['timeTotalCalls'] = isset($modelCdr->sessiontime) ? $modelCdr->sessiontime : 0;

            //efectivas
            $modelCdr = Cdr::model()->find(array(
                'select'    => 'count(*) dnid, sum(sessiontime) AS sessiontime',
                'condition' => $filterDateTurno . " AND id_category = 11",
                'group'     => 'id_user',
            ));
            $attributes[$i]['efectivastotal']     = isset($modelCdr->dnid) ? $modelCdr->dnid : 0;
            $attributes[$i]['timeTotalEfectivas'] = isset($modelCdr->sessiontime) ? $modelCdr->sessiontime : 0;

            //ratio

            /*
            1:45 minutos -> 6300 segundos e fez 5 efetivas =
            6300 / 3600 =
            5 / 1.75 = 2,86
             */
            if ($attributes[$i]['efectivastotal'] == 0) {
                $ratio = 0;
            } else if ($attributes[$i]['total_time'] == 0) {
                $ratio = $attributes[$i]['efectivastotal'] / (1 / 3600);
            } else {
                $ratio = $attributes[$i]['efectivastotal'] / ($attributes[$i]['total_time'] / 3600);
            }

            $attributes[$i]['ratio'] = number_format($ratio, 2);

            //chamadas por hora

            $hora    = gmdate('H', $attributes[$i]['total_time']);
            $minutos = gmdate('i', $attributes[$i]['total_time']);
            if ($minutos == 0) {
                $fracaoHoras = $hora + 0;
                if ($attributes[$i]['totalCalls'] == 0 || $fracaoHoras == 0) {
                    $attributes[$i]['barridos'] = number_format($attributes[$i]['totalCalls']);
                } else {
                    $attributes[$i]['barridos'] = number_format($attributes[$i]['totalCalls'] / $fracaoHoras);
                }

            } else {
                $fracaoHoras                = $hora + ($minutos / 60);
                $attributes[$i]['barridos'] = number_format($attributes[$i]['totalCalls'] / $fracaoHoras);

            }

        }

        return $attributes;
    }

    public function getStatus($attribute, $modelCampaign, $id_user_campaign, $modelLoginsCampaign)
    {
        if ($attribute['turno'] == 'M'
            && (date('H:i:s') < $modelCampaign->daily_morning_start_time
                || date('H:i:s') > $modelCampaign->daily_morning_stop_time)) {
            $atual_status = 'LOGOUT';
        } elseif ($attribute['turno'] == 'T'
            && (date('H:i:s') < $modelCampaign->daily_afternoon_start_time
                || date('H:i:s') > $modelCampaign->daily_afternoon_stop_time)) {
            $atual_status = 'LOGOUT';
        } else if (is_null($id_user_campaign)
            || $id_user_campaign != $attribute['id_campaign']
        ) {
            $atual_status = 'LOGOUT';
        } elseif (count($modelLoginsCampaign) && end($modelLoginsCampaign)->login_type == 'PAUSE' && end($modelLoginsCampaign)->stoptime == '0000-00-00 00:00:00') {
            $atual_status = 'PAUSE';
        } else {
            $atual_status = 'LOGIN';
        }

        return $atual_status;
    }

    public function sumTotalTime($modelLoginsCampaign, $atual_status)
    {
        //somatorio dos tempos de login e logout
        $totalTime      = 0;
        $totalTimePause = 0;

        //somar o tempo de todas as acoes realizada
        foreach ($modelLoginsCampaign as $key => $value) {

            if ($value->login_type == 'LOGIN') {
                //se estiver logado e o tempo final for igual a 0000-00-00 00:00:00 o totaltime sera: agora - o starttime
                if ($value->stoptime == '0000-00-00 00:00:00') {
                    $totalTime += time() - strtotime($value->starttime);
                } else {
                    $totalTime += $value->total_time;
                }

            } elseif ($value->login_type == 'PAUSE') {
                if ($value->stoptime == '0000-00-00 00:00:00' && $atual_status == 'PAUSE') {
                    $totalTimePause += time() - strtotime($value->starttime);
                } else {
                    $totalTimePause += $value->total_time;
                }

            }

            // echo $value->id . ' '.$totalTime."<br>";
        }

        // echo end($modelLoginsCampaign)->starttime."<br>";

        if ($atual_status == 'PAUSE') {
            $totalTime = $totalTime - (time() - strtotime(end($modelLoginsCampaign)->starttime));
        }
        //$totalTime =  $atual_status == 'PAUSE' ? $totalTime : $totalTime - $totalTimePause ;
        return array(
            'totalTime'      => $totalTime,
            'totalTimePause' => $totalTimePause,
        );
    }

    public function actionCsv()
    {

        $this->checkActionAccess(array(), $this->instanceModel->getModule(), 'canRead');

        $this->beforeRead($_GET);

        $this->setStart($_GET);

        $this->sort = $this->attributeOrder;

        $this->setOrder();

        $this->setLimit($_GET);

        $this->setfilter($_GET);

        $records = $this->abstractModel->findAll($this->readModel());

        $records = $this->getAttributesModels($records);

        $pathCsv = $this->magnusFilesDirectory . $this->nameFileReport . '.csv';
        if (!is_dir($this->magnusFilesDirectory)) {
            mkdir($this->magnusFilesDirectory, 777, true);
        }

        $fileOpen  = fopen($pathCsv, 'w');
        $separador = Yii::app()->session['language'] == 'pt_BR' ? ';' : ',';

        $fieldsCsv = array();

        $csv_fields   = array();
        $csv_fields[] = Yii::t('yii', 'Campaign');
        $csv_fields[] = Yii::t('yii', 'Username') . ' (' . Yii::t('yii', 'Nome') . ')';
        $csv_fields[] = Yii::t('yii', 'First Login');
        $csv_fields[] = Yii::t('yii', 'Loged Time');
        $csv_fields[] = Yii::t('yii', 'Shift');
        $csv_fields[] = Yii::t('yii', 'Day');
        $csv_fields[] = Yii::t('yii', 'State');
        $csv_fields[] = Yii::t('yii', 'Pause time');
        $csv_fields[] = Yii::t('yii', 'Total Calls');
        $csv_fields[] = Yii::t('yii', 'Time in Call');
        $csv_fields[] = Yii::t('yii', 'Success Call');
        $csv_fields[] = Yii::t('yii', 'Average');

        fputcsv($fileOpen, $csv_fields);

        for ($i = 0; $i < count($records) && is_array($records); $i++) {

            $fieldsCsv = array();

            $ratio                = $records[$i]['efectivastotal'] / ($records[$i]['total_time'] / 3600);
            $records[$i]['ratio'] = number_format($ratio, 2);

            $modelUser              = User::model()->findByPk((int) $records[$i]['id_user']);
            $records[$i]['id_user'] = $modelUser->username . '(' . $modelUser->name . ')';

            $modelCampaign              = Campaign::model()->findByPk((int) $records[$i]['id_campaign']);
            $records[$i]['id_campaign'] = $modelCampaign->name;

            $records[$i]['turno'] = $records[$i]['turno'] == 'M' ? Yii::t('yii', 'Morning') : Yii::t('yii', 'Afternoon');

            $records[$i]['total_time'] = gmdate("H:i:s", $records[$i]['total_time']);

            $records[$i]['total_pause'] = gmdate("H:i:s", $records[$i]['total_pause']);

            unset($records[$i]['id_breaks']);
            unset($records[$i]['id']);
            unset($records[$i]['stoptime']);
            unset($records[$i]['timeTotalEfectivas']);
            unset($records[$i]['barridos']);
            unset($records[$i]['login_type']);

            foreach ($records[$i] as $key => $value) {
                array_push($fieldsCsv, $value);
            }

            fputcsv($fileOpen, $fieldsCsv, $separador);
        }

        fclose($fileOpen);

        header('Content-type: application/csv');
        header('Content-Disposition: inline; filename="' . $pathCsv . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        ob_clean();
        flush();
        if (readfile($pathCsv)) {
            unlink($pathCsv);
        }
    }

    public function actionDestroy()
    {
        $values = $this->getAttributesRequest();

        $resultCampaign = $this->abstractModel->findByPk($values['id']);
        $id_campaign    = $resultCampaign->id_campaign;

        $modelCampaign = Campaign::model()->findByPk($id_campaign);

        $modelUser = User::model()->findByPk($resultCampaign->id_user);
        $username  = $modelUser->username;

        if ($id_campaign > 0) {
            AsteriskAccess::instance()->queueRemoveMember($username, $modelCampaign->name);
            $modelPhonenumber = PhoneNumber::model()->findByPk($modelUser->id_current_phonenumber);
            if (count($modelPhonenumber)) {
                $modelPhonenumber->id_user = null;
                $modelPhonenumber->save();
            }

            $modelUser->id_current_phonenumber = null;
            $modelUser->id_campaign            = null;
            $modelUser->force_logout           = 1;
            $modelUser->save();

            $modelLoginsCampaign = LoginsCampaign::model()->find(
                "id_user = :id_user AND stoptime = :stoptime AND
                    id_campaign = :id_campaign AND login_type = :login_type",
                array(
                    ":id_campaign" => $modelCampaign->id,
                    ":id_user"     => $modelUser->id,
                    ":stoptime"    => '0000-00-00 00:00:00',
                    ":login_type"  => 'LOGIN',
                ));
            if (count($modelLoginsCampaign)) {
                $modelLoginsCampaign->stoptime   = date('Y-m-d H:i:s');
                $modelLoginsCampaign->total_time = strtotime(date('Y-m-d H:i:s')) - strtotime($modelLoginsCampaign->starttime);
                try {
                    $modelLoginsCampaign->save();
                } catch (Exception $e) {
                    Yii::log(print_r($modelLoginsCampaign->errors, true), 'info');
                }
            }

            LoginsCampaign::model()->deleteAll('id_user = :key AND stoptime = :key1', array(
                ':key'  => $modelUser->id,
                ':key1' => '0000-00-00 00:00:00',
            ));

            OperatorStatus::model()->deleteAll("id_user = " . $modelUser->id);
            $success = true;
            $msn     = Yii::t('yii', 'Operation was successful.');
        }

        echo json_encode(array(
            'success' => $success,
            'msg'     => $msn,
        ));
        exit();
    }

    public function actionNotProduction()
    {

        if (isset($_POST['id_users'])) {
            $id_users = substr($_POST['id_users'], 1, -1);
        }

        if (isset($_POST['hour']) && $_POST['hour'] != '00:00:00') {

            if (strlen($id_users) > 0) {
                $sql = "SELECT * FROM pkg_user_online WHERE id_user IN ($id_users) AND pause IN (0,1) AND stoptime = '0000-00-00 00:00:00' ";
            } else {
                $sql = "SELECT * FROM pkg_user_online WHERE pause IN (0,1) AND stoptime = '0000-00-00 00:00:00'";
            }

            $usersResult = Yii::app()->db->createCommand($sql)->queryAll();

            $timeNotProduction = strtotime($_POST['hour']) - strtotime('TODAY');

            foreach ($usersResult as $key => $user) {

                $newStartTime = strtotime('+' . $timeNotProduction . ' second', strtotime($user['starttime']));
                $newStartTime = date('Y-m-d H:i:s', $newStartTime);

                if ($user['pause'] == 1) {
                    $sql = "UPDATE pkg_user_online SET starttime = '" . $newStartTime . "' WHERE id  = " . $user['id'];
                    Yii::app()->db->createCommand($sql)->execute();
                } elseif ($user['pause'] == 0) {

                    $newPauseTime = strtotime('+' . $timeNotProduction . ' second', strtotime($user['pausetime']));
                    $newPauseTime = date('Y-m-d H:i:s', $newPauseTime);
                    $sql          = "UPDATE pkg_user_online SET starttime = '" . $newStartTime . "', pausetime = '" . $newPauseTime . "' WHERE id  = " . $user['id'];
                    Yii::app()->db->createCommand($sql)->execute();
                }

                $sql          = "SELECT config_value FROM pkg_configuration WHERE config_key = 'valor_hora'";
                $configResult = Yii::app()->db->createCommand($sql)->queryAll();
                $valorSegundo = $configResult[0]['config_value'] / 3600;
                $total_price  = number_format($timeNotProduction * $valorSegundo, 2);

                $sql = "INSERT INTO pkg_billing (id_user, id_campaign, `date`, total_price, total_time, turno, description) VALUES ( " . $user['id_user'] . ", '-3', '" . date('Y-m-d') . "', '$total_price', '$timeNotProduction', '" . $user['turno'] . "', '" . $_POST['description'] . "')";
                Yii::app()->db->createCommand($sql)->execute();

            }

            echo json_encode(array(
                'success' => true,
                'msg'     => 'ok',
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'msg'     => 'El tiempo no puede ser igual a 00:00:00',
            ));
        }
    }
}
