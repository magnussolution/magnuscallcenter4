<?php
class PredictiveCommand extends ConsoleCommand
{
    public $portabilidade = false;
    public $config;

    public function run($args)
    {
        $this->debug = 10;

        //Tempo de pausa entre cada campanha
        $pause      = 1;
        $operadores = array();

        $log = $this->debug >= 0 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' start predictive ' . date('Y-m-d H:i:s')) : null;

        for (;;) {

            //select active campaign
            $modelCampaign = Campaign::model()->findAll('predictive = 1 AND status = 1');

            if (!count($modelCampaign)) {
                $msg = 'Not exists campaign with active predictive';
                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                sleep($pause);
                continue;
            }

            $msg = "\n\n\n\nEsperar $pause ";
            $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;

            sleep($pause);

            //da um loop pela quantidade de campanha encontrada
            for ($i = 0; $i < count($modelCampaign); $i++) {
                $time = date('H:i:s');

                //verificar se esta dentro de uma pausa obrigatoria. Se estiver nao mandar chamada.
                $modelBreaks = Breaks::model()->find('mandatory = 1 AND :key > start_time AND :key < stop_time', array(':key' => $time));

                if (count($modelBreaks)) {
                    echo "Nao enviar chamada porque estamos em pausa obrigatoria";
                    sleep(1);
                    continue;
                }

                $nowtime = date('H:s');

                if ($nowtime > $modelCampaign[$i]->daily_morning_start_time &&
                    $nowtime < $modelCampaign[$i]->daily_morning_stop_time) {
                    //echo "turno manha";
                } elseif ($nowtime > $modelCampaign[$i]->daily_afternoon_start_time &&
                    $nowtime < $modelCampaign[$i]->daily_afternoon_stop_time) {
                    //echo "Turno Tarde";
                } else {
                    echo "sem turno agora";
                    $log = $this->debug >= 0 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ .
                        ' Campanha fora de turno' . $modelCampaign[$i]['name']) : null;
                    continue;
                }

                /*
                tempo a ser esperado entre cada tentativa de envio para o operadora.
                EX: se for 30, o preditivo envia as chamadas, e se o operadora continuar livre por
                30 segundos, o sistema ira enviar mais chamada para ele.
                 */
                $sleepTime = $modelCampaign[$i]->call_next_try;

                /*
                call_limit = 0, sera calculado automatico o total de chamada a ser enviado por cada operador usando $nbpage.
                call_limit > 0, subscreveta a varialvem $nbpage e sera usando como o total de chamada por cada operador
                 */

                $call_limit = $modelCampaign[$i]->call_limit;

                //se call_limit > 0, nao precisa calcular o $nbpage
                if ($call_limit == 0) {
                    /*
                    Total de chamadas / pelas atendidas: Ex: foi realizado 100 chamadas e atendidas 40. $nbpage sera 2.5 intval 2
                    Esta variavel $nbpage, sera usada para calcular quantas chamadas devera ser enviada para cada operadora livre.
                     */

                    //verifico o total de chamadas que foram ATENDIDAS da campanha,
                    $criteria            = new CDbCriteria();
                    $criteria->condition = 'ringing_time > 1 AND id_phonebook IN (SELECT id_phonebook FROM pkg_campaign_phonebook  WHERE id_campaign = :key) ';
                    $criteria->params    = array(':key' => $modelCampaign[$i]->id);
                    $totalAnswerdCalls   = PredictiveGen::model()->count($criteria);

                    //pego o total de chamadas, atendidas ou nao.
                    $criteria            = new CDbCriteria();
                    $criteria->condition = 'id_phonebook IN (SELECT id_phonebook FROM pkg_campaign_phonebook  WHERE id_campaign = :key) ';
                    $criteria->params    = array(':key' => $modelCampaign[$i]->id);
                    $totalCalls          = PredictiveGen::model()->count($criteria);

                    $nbpage = @intval($totalCalls / $totalAnswerdCalls);
                }

                //calculo o tempo medio do RING que as chamadas ATENDIDAS estao demorando
                $criteria            = new CDbCriteria();
                $criteria->select    = 'AVG( ringing_time ) AS AVG_ringing_time';
                $criteria->condition = 'ringing_time > 1 AND id_phonebook IN (SELECT id_phonebook FROM pkg_campaign_phonebook  WHERE id_campaign = :key) ';
                $criteria->params    = array(':key' => $modelCampaign[$i]->id);
                $averageRingingTime  = PredictiveGen::model()->findAll($criteria);
                $averageRingingTime  = intval($averageRingingTime);

                $userNotInUse = 0;
                //Inicio a verificacao do status dos operadores da campanha
                $server = AsteriskAccess::instance()->queueShow($modelCampaign[$i]->name);

                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . 'queue show ' . $modelCampaign[$i]->name) : null;

                $log = $this->debug >= 2 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . print_r(explode("\n", $server['data']), true)) : null;

                //$operadores contem os operadores livres e o time que foi enviado a ultima chamada.

                $queue = explode("\n", $server["data"]);
                foreach ($queue as $value) {
                    echo $value . "\n\n";

                    //Quantos operadores estao com status not in use
                    if (!preg_match("/paused/", $value) && preg_match("/Not in use/", $value)) {

                        $operador = explode(" ", substr(trim($value), 6));
                        $operador = $operador[0];
                        echo "tem operador livre $operador\n";
                        $s = 0;

                        foreach ($operadores as $key => $value2) {

                            //se o operadora esta na array de operadores, entao verificamos se temos que
                            //reenviar chamadas ou nao enviar porque esta dentro do  $sleeoTime
                            if (array_key_exists($operador, $value2)) {

                                if (($value2[$operador] + $sleepTime) > time()) {
                                    $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ .
                                        " Acabamos de gerar uma chamada para operador $operador nao gerar outra: " . date("Y-m-d H:i:s", $value2[$operador])) : null;
                                    continue 2;
                                } else {
                                    if (isset($operadores[$s])) {
                                        $msg = "Refazer chamada para o $operador unset(" . print_r($operadores[$s], true) . ")";
                                        $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                                        //removemos de $operadores para adicionaremos abaixo com o novo tempo.
                                        unset($operadores[$s]);
                                    }
                                }
                                $s++;
                            }

                        }

                        $msg = "Tem operador livre $operador";
                        $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                        //adicionamos em operadores com o time
                        $operadores[] = array($operador => time());
                        $userNotInUse++;

                    } else if (preg_match("/paused/", $value)) {
                        // operadores pausados
                        $operador = explode(" ", substr(trim($value), 4));
                        $operador = $operador[0];

                        $modelOperatorStatus = OperatorStatus::model()->findAll(array(
                            'params' => array(':key' => $operador),
                            'with'   => array(
                                'idUser' => array(
                                    'condition' => "idUser.username = :key",
                                ),
                            ),
                        ));

                        /*
                        vamos tentar prever quando o operador ficara livre, pegando tempo medio que ele gasta para categorizar
                         */
                        if (count($modelOperatorStatus)) {

                            $pauseTime = time() - $modelOperatorStatus[0]->time_start_cat;
                            //se o tempo em pausa for maior que (media pausa - media ring ) e menor que a media iniciar chamada
                            if ($pauseTime > ($modelOperatorStatus[0]->media_to_cat - $averageRingingTime) && $pauseTime < $modelOperatorStatus[0]->media_to_cat) {

                                $p = 0;
                                foreach ($operadores as $key => $value3) {
                                    //mesma logica de quando o operador esta livre.
                                    if (array_key_exists($operador, $value3)) {

                                        if (($value3[$operador] + $sleepTime) > time()) {

                                            $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " ---------->TENTAR Acabamos de gerar uma chamada para operador $operador nao gerar outra: " . date("Y-m-d H:i:s", $value3[$operador])) : null;
                                            break;
                                        } else {
                                            if (isset($operadores[$p])) {
                                                $msg = "TENTAR enviar chamada para operadora   " . print_r($operador, true) . " esta em pausa a " . $pauseTime . "s e sua media de categorizacao é " . $modelOperatorStatus[0]->media_to_cat . 's, e o tempo ringando é ' . $averageRingingTime . 's';
                                                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                                                $msg = "TENTAR Tem operador livre $operador";
                                                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                                                unset($operadores[$s]);
                                                $operadores[] = array($operador => time());
                                                $userNotInUse++;
                                            }
                                        }
                                        $p++;
                                    }
                                }
                            }
                        }
                    }
                    //pegamos o total de chamadas que tem na campanha
                    if (preg_match("/strategy/", $value)) {
                        $resultLimit = explode(" ", $value);
                        $totalCalls  = $resultLimit[2];
                    }
                }

                //evitamos de que se tem chamadas em espera e tem operador livre, nao geramos para evitar queimar numeros
                if ($totalCalls > $userNotInUse) {
                    $msg   = " No send call, becouse have call: total call " . $totalCalls . ', operator not in use' . $userNotInUse;
                    $log   = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                    $pause = 4;
                    continue;
                }

                if ($userNotInUse == 0) {
                    $msg   = "Not have free operador";
                    $log   = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;
                    $pause = 4;
                    //if no have user free, continue to next.
                    continue;
                }

                $msg = "Tem $userNotInUse operador disponivel";
                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;

                $msg = "Tentar enviar chamadas\n";
                $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;

                if ($call_limit == 0) {
                    $nbpage = $nbpage * $userNotInUse;

                    if ($nbpage == 0) {
                        $nbpage = 3;
                    }

                    if ($nbpage > 10) {
                        //evita mandar mais que 10 chamadas por operador, mesmo se o ASR da campanha for ruin
                        $log    = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' O ASR da campanha ' . $modelCampaign[$i]->name . " esta muito baixo") : null;
                        $nbpage = 10;
                    }
                    $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . 'LOG:' . "LIMIT automatico $nbpage ") : null;
                } else {
                    $nbpage = $call_limit * $userNotInUse;
                    $log    = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . 'LOG:' . "LIMIT manual= $nbpage ") : null;
                }

                //get all campaign phonebook
                $modelCampaignPhonebook = CampaignPhonebook::model()->findAll('id_campaign = :key', array(':key' => $modelCampaign[$i]->id));
                $ids_phone_books        = array();
                foreach ($modelCampaignPhonebook as $key => $phonebook) {
                    $ids_phone_books[] = $phonebook->id_phonebook;
                }

                $id_category_next_call = $this->config['global']['continue_next_number'] > 1 ? ', ' . $this->config['global']['continue_next_number'] : '';

                PhoneNumber::model()->updateAll(array('id_category' => '0', 'status' => '0', 'last_trying_number' => '1', 'id_user' => null), 'last_trying_number >= 6');

                $datebackcall = date('Y-m-d H:i', mktime(date('H'), date('i') - 10, date('s'), date('m'), date('d'), date('Y')));

                $criteria = new CDbCriteria();
                $criteria->addCondition('last_trying_number <=5 AND number > 0 AND id_phonebook IN ( SELECT id_phonebook FROM pkg_campaign_phonebook WHERE id_campaign = :key ) AND id_category  = 2 AND datebackcall > :key1 ');
                $criteria->params[':key']  = $modelCampaign[$i]->id;
                $criteria->params[':key1'] = $datebackcall;
                $criteria->order           = 'datebackcall DESC';
                $criteria->limit           = $nbpage * 5;
                $modelPhoneNumber          = PhoneNumber::model()->findAll($criteria);

                if (!isset($modelPhoneNumber[0]->id)) {

                    echo "buscar phone numbers normal\n";
                    $criteria = new CDbCriteria();
                    $criteria->addCondition('last_trying_number <=5 AND number > 0 AND id_phonebook IN ( SELECT id_phonebook FROM pkg_campaign_phonebook WHERE id_campaign = :key ) AND id_category  IN (1 ' . $id_category_next_call . ') ');
                    $criteria->params[':key'] = $modelCampaign[$i]->id;
                    $criteria->limit          = $nbpage * 5;

                    $modelPhoneNumber = PhoneNumber::model()->findAll($criteria);
                }

                if (!count($modelPhoneNumber)) {
                    echo $sql;
                    echo 'NO PHONE FOR CALL';
                    $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " NO PHONE FOR CALL") : null;
                    continue;
                }
                $ids = array();

                foreach ($modelPhoneNumber as $phone) {

                    //echo 'PhoneID=' . $phone->id . "\n\n";

                    if (count($ids) >= $nbpage) {
                        break;
                    }

                    MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " \n\n\n");

                    for ($u = 0; $u < 5; $u++) {

                        switch ($phone->last_trying_number) {
                            case '1':
                                $destination = $phone->number;
                                break;
                            case '2':
                                $destination = $phone->mobile;
                                break;
                            case '3':
                                $destination = $phone->mobile_2;
                                break;
                            case '4':
                                $destination = $phone->number_home;
                                break;
                            case '5':
                                $destination = $phone->number_office;
                                break;
                            default:
                                break;
                        }

                        if (!is_numeric($destination) || strlen($destination) < 8) {
                            //$log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . "o numero " . $phone->last_trying_number . " nao é numerico, entao tentamos o proximo " . $phone->id) : null;
                            $phone->last_trying_number++;
                        } else {
                            break;
                        }
                    }

                    PhoneNumber::model()->updateByPk($phone->id, array('status' => 0, 'last_trying_number' => $phone->last_trying_number));

                    $extension = $destination;

                    if (!is_numeric($destination) || strlen($destination) < 8) {
                        $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " Temtamos todos os numeros e ja nao tem mais nenhum. PHONE->ID =" . $phone->id . ' last_trying_number=' . $phone->last_trying_number) : null;
                        continue;
                    }
                    $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " ID=" . $phone->id . ' - Destination' . $destination . ', last_trying_number=' . $phone->last_trying_number) : null;

                    $destination = Portabilidade::getDestination($destination, $phone->id_phonebook);
                    if ($phone->number != $destination) {
                        $rn1      = substr($phonenumber, 0, 5);
                        $criteria = new CDbCriteria();
                        $criteria->addCondition('id IN ( SELECT id_trunk FROM pkg_codigos_trunks WHERE id_codigo IN (SELECT id FROM pkg_codigos WHERE company = (SELECT company FROM pkg_codigos WHERE prefix = :key)) )');
                        $criteria->params[':key'] = $rn1;
                        $criteria->order          = 'RAND()';
                        $criteria->limit          = $nbpage;
                        $modelTrunkPortabilidade  = Trunk::model()->find($criteria);

                        if (count($modelTrunkPortabilidade)) {
                            $phone->idPhonebook->id_trunk = $modelTrunkPortabilidade->id;
                        } else {
                            $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . 'Portabilidade ativa, mas sem tronco para ' . $rn1) : null;
                        }
                    }

                    $log        = $this->debug >= 4 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " DESTINATION " . $destination) : null;
                    $modelTrunk = Trunk::model()->findByPk((int) $phone->idPhonebook->id_trunk);

                    if ($phone->try > 2) {
                        $log = $this->debug >= 4 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . "Number already try dial per 3 trunk. Disable number \n\n") : null;
                        PhoneNumber::model()->updateByPk($phone->id, array('status' => 0, 'id_category' => 0, 'try' => 0));
                        continue;
                    } else if ($phone->try > 0) {
                        $modelTrunk = Trunk::model()->findByPk((int) $modelTrunk->failover_trunk);
                        if (!isset($modelTrunk->id)) {
                            $log = $this->debug >= 4 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . "NOT EXIST TRUNCK BACKUP $modelTrunk->failover_trunk \n\n") : null;
                            PhoneNumber::model()->updateByPk($phone->id, array('status' => 0, 'id_category' => 0, 'try' => 0));
                            continue;
                        }

                        if ($phone->try == 2) {
                            $modelTrunk = Trunk::model()->findByPk((int) $modelTrunk->failover_trunk);
                        }

                        if (!isset($modelTrunk->id)) {
                            $log = $this->debug >= 4 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . "NOT EXIST TRUNCK BACKUP $modelTrunk->failover_trunk \n\n") : null;
                            PhoneNumber::model()->updateByPk($phone->id, array('status' => 0, 'id_category' => 0, 'try' => 0));
                            continue;
                        }
                        echo 'Trunk backup found Try per trunk  ' . $modelTrunk->trunkcode . "\n\n";
                    }

                    $idTrunk      = $modelTrunk->id;
                    $trunkcode    = $modelTrunk->trunkcode;
                    $trunkprefix  = $modelTrunk->trunkprefix;
                    $removeprefix = $modelTrunk->removeprefix;
                    $providertech = $modelTrunk->providertech;

                    //retiro e adiciono os prefixos do tronco
                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                        $destination = substr($destination, strlen($removeprefix));
                    }

                    $destination = $trunkprefix . $destination;

                    $aleatorio = str_replace(" ", "", microtime(true));

                    $dialstr = "$providertech/$destination@$trunkcode";

                    // gerar os arquivos .call
                    $call = "MaxRetries: 0\n";
                    $call .= "Channel: " . $dialstr . "\n";
                    $call .= "CallerID:" . $extension . "\n";
                    //$call .= "MaxRetries: 0\n";
                    //$call .= "RetryTime: 1\n";
                    //$call .= "WaitTime: 45\n";
                    $call .= "Account: predictive|" . $aleatorio . "|1|" . $phone->id . "|" . $phone->last_trying_number . "\n";
                    $call .= "Context: magnuscallcenterpredictive\n";
                    $call .= "Extension: " . $extension . "\n";
                    $call .= "Priority: 1\n";
                    $call .= "Set:CALLERID=" . $extension . "\n";
                    $call .= "Set:CALLED=" . $extension . "\n";
                    $call .= "Set:PHONENUMBER_ID=" . $phone->id . "\n";
                    $call .= "Set:IDPHONEBOOK=" . $phone->id_phonebook . "\n";
                    $call .= "Set:CAMPAIGN_ID=" . $modelCampaign[$i]->id . "\n";
                    $call .= "Set:IDTRUNK=" . $phone->idPhonebook->id_trunk . "\n";
                    $call .= "Set:STARTCALL=" . time() . "\n";
                    $call .= "Set:ALEARORIO=" . $aleatorio . "\n";
                    if ($modelTrunk->AMD_active == 1) {
                        $call .= "Set:AMD=" . $modelTrunk->AMD_active . "\n";
                    }
                    $call .= "Set:AMDtotalAnalysisTime=" . $modelTrunk->AMD_totalAnalysisTime . "000\n";
                    $call .= "Set:AMDmaximumNumberOfWords=" . $modelTrunk->AMD_maximumNumberOfWords . "\n";
                    $call .= "Archive: yes\n";
                    $log = $this->debug >= 4 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . $call) : null;

                    $msg = "Enviado chamada para  $extension";
                    echo 'LOG:' . $call . "\n";
                    $log = $this->debug >= 1 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . ' ' . $msg) : null;

                    $arquivo_call = "/tmp/$aleatorio.call";

                    $fp = fopen("$arquivo_call", "a+");
                    fwrite($fp, $call);
                    fclose($fp);

                    //$time += time();
                    touch("$arquivo_call", $time);
                    chown("$arquivo_call", "asterisk");
                    chgrp("$arquivo_call", "asterisk");
                    chmod("$arquivo_call", 0755);
                    LinuxAccess::system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
                    $ids[] = $phone->id;
                }

                //desativamos o numero para nao ser usado novamente.
                $criteria = new CDbCriteria();
                $criteria->addInCondition('id', $ids);
                PhoneNumber::model()->updateAll(
                    array(
                        'id_category' => 0,
                    ),
                    $criteria);

                //salvamos os dados da chamada gerada
                $modelPredictiveGen               = new PredictiveGen();
                $modelPredictiveGen->date         = time();
                $modelPredictiveGen->uniqueID     = $aleatorio;
                $modelPredictiveGen->id_phonebook = $phone->id_phonebook;
                try {
                    $modelPredictiveGen->save();
                } catch (Exception $e) {

                }
            }
        }
    }
}
