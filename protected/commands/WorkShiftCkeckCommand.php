<?php
class WorkShiftCkeckCommand extends ConsoleCommand
{

    public function run($args)
    {
        $today     = date('Y-m-d');
        $yestarday = date('Y-m-d', strtotime("-1 days"));
        /*
        pego los ids dos WorkShift de hoy por turno
        pego los operadores que marcaran WorkShift
         */
        for ($i = 0; $i < 2; $i++) {

            $day = $i == 0 ? $today : $yestarday;

            $valorFalta = $this->config['global']['valor_falta'];
            $turnos     = ['M', 'T'];
            for ($t = 0; $t < 2; $t++) {

                $modelUserWorkShift = UserWorkShift::model()->findAll(array(
                    'with' => array(
                        'idWorkShift' => array(
                            'condition' => "idWorkShift.day LIKE :key AND turno = :key1",
                            'params'    => array(
                                ':key'  => $day,
                                ':key1' => $turnos[$t]),
                        ),
                    ),
                ));

                //turno manha
                foreach ($modelUserWorkShift as $key => $userWorkShift) {

                    //verifico si el operador tiene login en el dia de hoy
                    $modelLoginCampaig = LoginsCampaign::model()->findAll('id_user = :key AND starttime > :key1 AND turno = :key2',
                        array(
                            ':key'  => $userWorkShift->id_user,
                            ':key1' => $day,
                            ':key2' => $turnos[$t],
                        ));

                    if (!count($modelLoginCampaig)) {
                        $modelBilling = Billing::model()->findAll('date = :key AND id_user_online IS NULL AND id_campaign = -2 AND id_user = :key1 AND turno = :key2',
                            array(
                                ':key'  => $day,
                                ':key1' => $userWorkShift->id_user,
                                ':key2' => $turnos[$t],
                            ));

                        if (!count($modelBilling)) {

                            $modelBilling              = new Billing();
                            $modelBilling->id_user     = $userWorkShift->id_user;
                            $modelBilling->id_campaign = -2;
                            $modelBilling->date        = $day;
                            $modelBilling->total_price = $valorFalta * -1;
                            $modelBilling->turno       = $turnos[$t];
                            $modelBilling->save();
                        }
                    }
                }

            }
        }

    }
}
