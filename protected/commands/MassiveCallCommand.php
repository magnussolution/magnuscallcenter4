<?php
/**
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 *
 */
class MassiveCallCommand extends ConsoleCommand
{

    public function run($args)
    {

        $UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

        $tab_day  = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $num_day  = date('N');
        $name_day = $tab_day[$num_day];

        $modelMassiveCallCampaign = MassiveCallCampaign::model()->findAll(array(
            'select'    => 't.id, t.frequency, t.name',
            'condition' => "status = 1 AND daily_start_time <= CURRENT_TIME AND daily_stop_time > CURRENT_TIME",
        )
        );
        define('DEBUG', 5);

        if (DEBUG >= 1) {
            echo "\nFound " . count($modelMassiveCallCampaign) . " Campaign\n\n";
        }

        foreach ($modelMassiveCallCampaign as $campaign) {

            if (DEBUG >= 1) {
                echo "SEARCH NUMBER IN CAMPAIGN " . $campaign->name . "\n";
            }
            if ($campaign->frequency == 0) {
                echo "Call per minutes =0\n";
                continue;
            }
            //calculo de chamadas por segundos.

            if ($campaign->frequency < 60) {
                //se for menos de 60 por minutos, pausar 10 segundos e dividir o total por 6
                //sleep(10);
                $nbpage = $campaign->frequency / 6;
                $sleep  = 60 / $campaign->frequency;
            } else {
                $nbpage = $campaign->frequency / 60;
                $sleep  = $campaign->frequency / 60;
            }

            if ($nbpage < 1) {
                $nbpage = 1;
            }

            $modelMassiveCallPhoneNumber = MassiveCallPhoneNumber::getPhoneNumbertoSend($campaign->id, intval($nbpage));

            if (DEBUG >= 1) {
                echo 'Found ' . count($modelMassiveCallPhoneNumber) . ' Numbers in Campaign ' . "\n";
            }

            if (count($modelMassiveCallPhoneNumber) == 0) {
                if (DEBUG >= 1) {
                    echo "NO PHONE FOR CALL" . "\n\n\n";
                }

                continue;
            }
            $i         = 0;
            $sleepNext = 1;
            $ids       = array();
            foreach ($modelMassiveCallPhoneNumber as $phone) {
                $i++;
                $name_number = $phone['name'];
                $destination = $phone['number'];

                $old_destination = $destination;
                $destination     = Portabilidade::getDestination($destination, $phone['id_massive_call_phonebook'], true);
                if ($destination != $old_destination) {
                    echo 'Usa portabilidade';
                    $rn1 = substr($phonenumber, 0, 5);

                    $modelTrunk = Trunk::model()->find(array(
                        'condition' => "id IN (SELECT id_trunk FROM pkg_codigos_trunks WHERE id_codigo IN (SELECT id FROM pkg_codigos WHERE company = (SELECT company FROM pkg_codigos WHERE prefix = '$rn1')) )",
                        'order'     => ' RAND()',
                    ));

                    if (count($resultPortabilidade) == 0) {

                        echo ('Portabilidade ativa, mas sem tronco para ' . $rn1);
                    }
                }

                if (!isset($modelTrunk)) {
                    $modelTrunk = Trunk::model()->findByPk($phone['id_trunk']);
                }

                $destination = $old_destination;
                if (count($modelTrunk) == 0) {
                    continue;
                }

                $idTrunk        = $modelTrunk->id;
                $trunkcode      = $modelTrunk->trunkcode;
                $trunkprefix    = $modelTrunk->trunkprefix;
                $removeprefix   = $modelTrunk->removeprefix;
                $providertech   = $modelTrunk->providertech;
                $status         = $modelTrunk->status;
                $failover_trunk = $modelTrunk->failover_trunk;

                //retiro e adiciono os prefixos do tronco
                if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                    $destination = substr($destination, strlen($removeprefix));
                }

                $destination = $trunkprefix . $destination;

                $dialstr = "$providertech/$destination@$trunkcode";

                //$dialstr = "$providertech/$trunkcode/24316";

                // gerar os arquivos .call
                $call = "MaxRetries: 0\n";
                $call = "Channel: " . $dialstr . "\n";
                $call .= "CallerID:" . $phone['number'] . "\n";
                $call .= "MaxRetries: 2\n";
                $call .= "RetryTime: 100\n";
                $call .= "WaitTime: 60\n";
                $call .= "Context: magnuscallcenter\n";
                $call .= "Extension: " . $destination . "\n";
                $call .= "Priority: 1\n";
                $call .= "Set:CALLED=" . $old_destination . "\n";
                $call .= "Set:PHONENUMBER_ID=" . $phone['id'] . "\n";
                $call .= "Set:CAMPAIGN_ID=" . $campaign->id . "\n";
                $call .= "Set:IDTRUNK=" . $idTrunk . "\n";
                $call .= "Set:MASSIVE_CALL=1\n";

                if (DEBUG == 2) {
                    echo $call . "\n\n";
                }

                echo $call;

                AsteriskAccess::generateCallFile($call, $sleepNext);

                if ($campaign->frequency <= 60) {
                    $sleepNext += $sleep;
                } else {
                    //a cada multiplo do resultado, passo para o proximo segundo
                    if (($i % $sleep) == 0) {
                        $sleepNext += 1;
                    }

                }
                $ids[] = $phone['id'];

            }

            $criteria = new CDbCriteria();
            $criteria->addInCondition('id', $ids);
            MassiveCallPhoneNumber::model()->updateAll(
                array(
                    'status'    => '2',
                    'try'       => new CDbExpression('try + 1'),
                    'dial_date' => date('Y-m-d H:i:s'),
                ),
                $criteria
            );

            echo "Campain " . $campaign->name . " sent " . $i . " calls \n\n";
        }
    }
}
