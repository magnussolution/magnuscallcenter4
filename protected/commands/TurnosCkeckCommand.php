<?php
class TurnosCkeckCommand extends ConsoleCommand
{

    public function run($args)
    {

        sleep(1);

        $modelLoginsCampaign = LoginsCampaign::model()->findAll(
            array(
                'condition' => 'stoptime = :key',
                'order'     => 'id DESC',
                'params'    => array(':key' => '0000-00-00 00:00:00'),
            )

        );

        foreach ($modelLoginsCampaign as $key => $userOnline) {

            $currentyTime = date('H:i:s');
            echo "CurrentTime $currentyTime \n";

            $startTimeUser = date('H:i:s', strtotime($userOnline->starttime));

            $campaignName = preg_replace("/ /", "\ ", $userOnline->idCampaign->name);
            echo $userOnline->login_type . "\n";

            if ($userOnline->login_type == 'PAUSE') {

                if ($userOnline->turno == 'M' && $currentyTime > $userOnline->idCampaign->daily_morning_stop_time
                    || $userOnline->turno == 'T' && $currentyTime > $userOnline->idCampaign->daily_afternoon_stop_time) {
                    echo "Operator in pause and out campaign time. UNPOUSE FIST\n";
                    if ($userOnline->turno == 'T') {
                        $userOnline->stoptime = $userOnline->idCampaign->daily_afternoon_stop_time;
                    } else {
                        $userOnline->stoptime = $userOnline->idCampaign->daily_morning_stop_time;
                    }
                    $userOnline->stoptime   = date('Y-m-d', strtotime($userOnline->starttime)) . ' ' . $userOnline->stoptime;
                    $userOnline->total_time = strtotime($userOnline->stoptime) - strtotime($startTimeUser);

                    try {
                        $userOnline->save();
                    } catch (Exception $e) {
                        Yii::log(print_r($userOnline->errors, true), 'info');
                    }

                    AsteriskAccess::instance()->queueUnPauseMember($userOnline->idUser->username, $userOnline->idCampaign->name);

                }
            }

            if ($userOnline->login_type == 'LOGIN') {
                if ($userOnline->starttime < date('Y-m-d')) {
                    echo "operator is loged from yestaday\n";
                    $this->logoutOperator($userOnline, $startTimeUser);

                }

                //se a hora que o cliente inicio o login é menor que a hora final do turno, desloguear o cliente
                if ($userOnline->turno == 'M' && $currentyTime > $userOnline->idCampaign->daily_morning_stop_time
                    || $userOnline->turno == 'T' && $currentyTime > $userOnline->idCampaign->daily_afternoon_stop_time) {
                    echo "operator is loged but out campaign time\n";
                    $this->logoutOperator($userOnline, $startTimeUser);
                }

            }
        }
    }

    public function logoutOperator($userOnline, $startTimeUser)
    {

        $modelOperatorStatus = OperatorStatus::model()->find('id_user = :key', array(':key' => $userOnline->idUser->id));
        if ($modelOperatorStatus->categorizing == 1 || $modelOperatorStatus->in_call == 1) {
            echo "Loged out campaign time but categorizing or in call. Wait.....";
            return;
        }

        AsteriskAccess::instance()->queueRemoveMember($userOnline->idUser->username, $userOnline->idCampaign->name);

        $modelPhonenumber = PhoneNumber::model()->findByPk($userOnline->idUser->id_current_phonenumber);

        if (count($modelPhonenumber)) {
            $modelPhonenumber->id_user = null;
            $modelPhonenumber->save();
        }

        $userOnline->idUser->id_current_phonenumber = null;
        $userOnline->idUser->id_campaign            = null;
        $userOnline->idUser->force_logout           = 1;
        $userOnline->idUser->save();

        $modelLoginsCampaign = LoginsCampaign::model()->find(
            "id_user = :id_user AND stoptime = :stoptime AND  id_campaign = :id_campaign AND login_type = :login_type",
            array(
                ":id_campaign" => $userOnline->id_campaign,
                ":id_user"     => $userOnline->id_user,
                ":stoptime"    => '0000-00-00 00:00:00',
                ":login_type"  => 'LOGIN',
            ));
        if (count($modelLoginsCampaign)) {

            if ($userOnline->turno == 'T') {
                $modelLoginsCampaign->stoptime = $userOnline->idCampaign->daily_afternoon_stop_time;
            } else {
                $modelLoginsCampaign->stoptime = $userOnline->idCampaign->daily_morning_stop_time;
            }
            $modelLoginsCampaign->stoptime = date('Y-m-d', strtotime($userOnline->starttime)) . ' ' . $modelLoginsCampaign->stoptime;
            echo 'stoptime = ' . $modelLoginsCampaign->stoptime;

            $modelLoginsCampaign->total_time = strtotime($modelLoginsCampaign->stoptime) - strtotime($startTimeUser);
            try {
                $modelLoginsCampaign->save();
            } catch (Exception $e) {
                Yii::log(print_r($modelLoginsCampaign->errors, true), 'info');
            }
        }

        LoginsCampaign::model()->deleteAll('id_user = :key AND stoptime = :key1', array(
            ':key'  => $userOnline->id_user,
            ':key1' => '0000-00-00 00:00:00',
        ));

        OperatorStatus::model()->deleteAll("id_user = " . $userOnline->id_user);
    }
}
