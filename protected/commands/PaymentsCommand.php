<?php
class PaymentsCommand extends ConsoleCommand
{

    public function run($args)
    {

        $hoje = date('Y-m-d', strtotime("-1 days"));

        $filterDay = "starttime > '$hoje' AND stoptime < '$hoje 23:59:59' AND stoptime != '0000-00-00 00:00:00'";

        //pego totas las campanhas que fueran trabajadas ayer
        $sql                 = "SELECT * FROM pkg_logins_campaign WHERE $filterDay GROUP BY id_campaign, turno";
        $campaingTurnoResult = Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($campaingTurnoResult as $key => $value) {

            //echo "Campanha ID  $value[id_campaign] e turno $value[turno] \n";
            //dados da camanha
            $sql            = "SELECT * FROM pkg_campaign WHERE id =" . $value['id_campaign'];
            $resultCampaign = Yii::app()->db->createCommand($sql)->queryAll();
            $morningStart   = $resultCampaign[0]['daily_morning_start_time'];
            $morningStop    = $resultCampaign[0]['daily_morning_stop_time'];
            $afternoonStart = $resultCampaign[0]['daily_afternoon_start_time'];
            $afternoonStop  = $resultCampaign[0]['daily_afternoon_stop_time'];

            //pego todos os operadores desta campanha deste turno
            $sql              = "SELECT * FROM pkg_logins_campaign WHERE $filterDay AND id_campaign = " . $value['id_campaign'] . " AND turno = '" . $value['turno'] . "'";
            $userOnlineResult = Yii::app()->db->createCommand($sql)->queryAll();
            //echo $sql;

            //pego o tempo total trabalhado nesta camnhana neste turno
            $sql                       = "SELECT SUM(total_time) sumtotaltime  FROM pkg_logins_campaign WHERE $filterDay AND id_campaign = " . $value['id_campaign'] . " AND turno = '" . $value['turno'] . "'";
            $userOnlineTotalTimeResult = Yii::app()->db->createCommand($sql)->queryAll();

            //diminuio o total_time - total_pause
            $sumtotaltimeCampaign = $userOnlineTotalTimeResult[0]['sumtotaltime']; //- $userOnlineTotalTimeResult[0]['sumtotaltimePause'];

            if ($value['turno'] == 'M') {
                $filterDateTurno = "(starttime > '" . $hoje . ' ' . $morningStart . "'  AND stoptime < '" . $hoje . ' ' . $morningStop . "')";
            } elseif ($value['turno'] == 'T') {
                $filterDateTurno = "(starttime > '" . $hoje . ' ' . $afternoonStart . "'  AND stoptime < '" . $hoje . ' ' . $afternoonStop . "')";
            }

            //calcular promedito del ratio de la campanha
            //pegar todas as efetivas do turno da campanha
            $sql                          = "SELECT count(*) total FROM pkg_cdr WHERE $filterDateTurno AND id_campaign = " . $value['id_campaign'] . " AND id_category = 3 AND id_user > 0";
            $resultTotalEfectivasCampaign = Yii::app()->db->createCommand($sql)->queryAll();

            if ($resultTotalEfectivasCampaign[0]['total'] == 0) {
                $ratioCampaing = 0;
            } else {
                $ratioCampaing = number_format($resultTotalEfectivasCampaign[0]['total'] / ($sumtotaltimeCampaign / 3600), 2);
            }

            foreach ($userOnlineResult as $key => $user) {

                $sql           = "SELECT SUM(total_time) FROM pkg_logins_campaign WHERE $filterDay AND id_campaign = " . $value['id_campaign'] . " AND turno = '" . $value['turno'] . "' AND id_user = " . $user['id_user'];
                $userTotalTime = Yii::app()->db->createCommand($sql)->queryAll();
                $totalTime     = $userTotalTime[0]['total_time'];

                $filterDateTurnoUser = " AND id_user = " . $user['id_user'] . " AND id_campaign = " . $user['id_campaign'];

                $sql = "SELECT count(*) total FROM pkg_cdr WHERE $filterDateTurno $filterDateTurnoUser AND id_category = 3";

                $resultEfectivasUSer = Yii::app()->db->createCommand($sql)->queryAll();
                $efectivastotalUser  = $resultEfectivasUSer[0]['total'];

                if ($efectivastotalUser == 0) {
                    $ratioUser = 0;
                } else {
                    $ratioUser = $efectivastotalUser / ($totalTime / 3600);
                    $ratioUser = number_format($ratioUser, 2);
                }

                //calular ganho
                $totalArray = $this->calculateTotal($ratioCampaing, $ratioUser, $totalTime);

                $totalPrice = $totalArray['total_price'];
                $incremento = $totalArray['incremento'];

                $fields = "id_user_online, id_user, id_campaign, date, total_price, turno, efetivas, total_time, ratio, ratio_total, incremento";
                $insert = $userOnlineResult[0]['id'] . ',' . $user['id_user'] . ',' . $user['id_campaign'] . ", '$hoje', " .
                    "'" . $totalPrice . "', '" . $user['turno'] . "', " . $efectivastotalUser . ", " . $totalTime . ", '$ratioUser', '$ratioCampaing', '$incremento'";
                $sql = "INSERT INTO pkg_billing ($fields) VALUES ($insert)";

                echo $sql . "\n";

                Yii::app()->db->createCommand($sql)->execute();

            }

        }

    }

    public function calculateTotal($ratioCampaing, $ratioUser, $total_time)
    {

        $sql          = "SELECT config_value FROM pkg_configuration WHERE config_key = 'valor_colectivo'";
        $configResult = Yii::app()->db->createCommand($sql)->queryAll();
        $valorOnibus  = $configResult[0]['config_value'];

        $sql          = "SELECT config_value FROM pkg_configuration WHERE config_key = 'valor_hora'";
        $configResult = Yii::app()->db->createCommand($sql)->queryAll();
        $valorHora    = $configResult[0]['config_value'];

        $sql           = "SELECT config_value FROM pkg_configuration WHERE config_key = 'valor_hora_zero'";
        $configResult  = Yii::app()->db->createCommand($sql)->queryAll();
        $valorHoraZero = $configResult[0]['config_value'];

        if ($ratioCampaing == 0) {
            $total_price = $valorOnibus;
            $ratio       = 0;
        } else if ($ratioCampaing <= 0.5) {
            //operador com ratio 0, pagar 11 la proporcion hora.
            if ($ratioUser == 0) {
                $total_price = $valorHoraZero * ($total_time / 3600);
                $ratio       = $total_time / 3600;
            }
            //operador com ratio > 0 pagar 16 pesos la hora,
            else {

                $ratio = $ratioUser / $ratioCampaing;

                $totalHora = $valorHora * $ratio;

                //Se o valor da hora for superior a duas veces, limitar em maximo 32 pesos
                $totalHora = $totalHora > ($valorHora * 2) ? $valorHora * 2 : $totalHora;

                $total_price = $totalHora * ($total_time / 3600);
            }

        } else {

            if ($ratioUser == 0) {
                $total_price = $valorOnibus;
                $ratio       = 0;

            } else {

                $ratio = $ratioUser / $ratioCampaing;

                $totalHora = $valorHora * $ratio;

                $total_price = $totalHora * ($total_time / 3600);
            }
        }

        return array(
            'total_price' => number_format($total_price, 2),
            'incremento'  => number_format($ratio, 2),
        );
    }
}
