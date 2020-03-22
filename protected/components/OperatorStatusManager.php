<?php
/**
 *
 */
class OperatorStatusManager
{

    public static function unPause($id_user, $modelCampaign, $categorizing = 0, $username = null)
    {

        $modeUser = User::model()->findByPk($id_user);

        //nao estava categorizando
        if ($categorizing == 0) {

            $modelLoginsCampaign = LoginsCampaign::model()->find(
                "id_user = :id_user AND stoptime = :stoptime AND
                id_campaign = :id_campaign AND login_type = :login_type",
                array(
                    ":id_campaign" => $modelCampaign->id,
                    ":id_user"     => $id_user,
                    ":stoptime"    => '0000-00-00 00:00:00',
                    ":login_type"  => 'PAUSE',
                ));

            if ($modelLoginsCampaign->idBreak->mandatory and
                $modelLoginsCampaign->idBreak->stop_time > date('H:i:s')) {
                $msg = Yii::t('yii', 'You cannot unbreak becouse this break is mandatory');
                return json_encode(array(
                    $this->nameSuccess => false,
                    $this->nameMsg     => $msg,
                ));
                exit;
            }

            if (Yii::app()->session['isOperator']) {

                $modelBreaks = Breaks::model()->findByPk((int) $modelLoginsCampaign->id_breaks);
                $deadline    = $modelBreaks->maximum_time;

                if (strtotime($modelLoginsCampaign->starttime . ' +' . $modelBreaks->maximum_time . ' minutes') < strtotime('now')) {

                    return json_encode(array(
                        $this->nameSuccess => false,
                        $this->nameMsg     => Yii::t('yii', 'Pause time is over, call the supervisor to unlock the system'),
                    ));
                    exit();

                }
            }

            $modelLoginsCampaign->stoptime   = date('Y-m-d H:i:s');
            $modelLoginsCampaign->total_time = strtotime(date('Y-m-d H:i:s')) - strtotime($modelLoginsCampaign->starttime);
            try {
                $modelLoginsCampaign->save();
            } catch (Exception $e) {
                Yii::log(print_r($modelLoginsCampaign->errors, true), 'info');
            }

            return true;

        }

        $username = $username == null ? $modeUser->username : $username;

        AsteriskAccess::instance()->queueUnPauseMember($username, $modelCampaign->name);

    }
}
