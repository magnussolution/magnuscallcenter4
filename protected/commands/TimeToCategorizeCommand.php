<?php
class TimeToCategorizeCommand extends ConsoleCommand
{
    public $config;

    public function run($args)
    {
        for (;;) {

            $modelOperatorStatus = OperatorStatus::model()->findAll('categorizing = 1');

            foreach ($modelOperatorStatus as $key => $operatorStatus) {
                $modelCampaign = Campaign::model()->findByPk((int) $operatorStatus->id_campaign);
                $modeUser      = User::model()->findByPk($operatorStatus->id_user);

                //calcula a media do tempo gasto para categorizar e set o time que categorizou para pegar e calcular o tempo livre ate a proxima chamada
                if (isset($operatorStatus->time_start_cat) && $operatorStatus->time_start_cat > 0) {

                    echo $time_to_call = time() - $operatorStatus->time_start_cat;

                    if ($time_to_call > $args[0]) {

                        Cdr::model()->updateAll(array('id_category' => 11), 'sessiontime > 0 AND id_category IS NULL');

                        $modeUser->id_current_phonenumber = null;
                        $modeUser->save();

                        OperatorStatus::model()->updateByPk($operatorStatus->id, array(
                            'time_start_cat' => 0,
                            'media_to_cat'   => $args[0],
                            'time_free'      => time(),
                            'cant_cat'       => $operatorStatus->cant_cat + 1,
                            'categorizing'   => 0,
                        ));

                        OperatorStatusManager::unPause($operatorStatus->id_user, $modelCampaign, 1, $modeUser->username);
                    }
                }
            }
            sleep(3);
        }
    }
}
