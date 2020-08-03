<?php
/**
 * Acoes do modulo "PhoneNumber".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class PhoneNumberController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idPhonebook' => 'name', 'idCategory' => 'name', 'idUser' => 'name');

    public $join = 'INNER JOIN pkg_phonebook ON t.id_phonebook = pkg_phonebook.id
    INNER JOIN pkg_campaign_phonebook ON pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id
    INNER JOIN pkg_category ON t.id_category = pkg_category.id
    ';

    public $fieldsFkReport = array(
        'id_phonebook' => array(
            'table'       => 'pkg_phonebook',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
        'id_category'  => array(
            'table'       => 'pkg_category',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );

    public function init()
    {
        $this->instanceModel = new PhoneNumber;
        $this->abstractModel = PhoneNumber::model();
        $this->titleReport   = Yii::t('yii', 'Phone Number');

        if (!Yii::app()->session['isOperator']) {
            $this->join   = '';
            $this->select = '*';
        }

        parent::init();
    }

    public function beforeSave($values)
    {
        if (Yii::app()->session['isTeam']) {
            $values['id_team'] = Yii::app()->session['id_team'];
        }

        if (isset($values['id_category']) && Yii::app()->session['isOperator'] &&
            ($values['id_category'] == 1 || $values['id_category'] == 0)) {
            echo json_encode(array(
                'success' => false,
                'msg'     => 'El número no puede ser salvo con el estado ACTIVO',
            ));
            exit;
        }

        if (isset($values['id_user']) && Yii::app()->session['isClient'] || Yii::app()->session['isOperator']) {
            $values['id_user'] = Yii::app()->session['id_user'];
            if ($this->isNewRecord) {
                $values['status'] = 1;
            }
        }

        if (isset($values['id_user']) && $values['id_user'] == 0) {
            $values['id_user'] = null;
        }
        return $values;
    }

    public function afterSave($model, $values)
    {
        if (Yii::app()->session['isOperator']) {
            $modelCampaign = Campaign::model()->findByPk((int) Yii::app()->session['id_campaign']);
            $modeUser      = User::model()->findByPk(Yii::app()->session['id_user']);

            if ($this->isNewRecord && $values['id_category'] == 8) {
                //prende o numero adionado como base externa para poder ligar.
                $modeUser->id_current_phonenumber = $model->id;
                $modeUser->save();
            } elseif (count($modelCampaign) && $modelCampaign->predictive != 1) {

                $modeUser->id_current_phonenumber = null;
                $modeUser->save(); //
            };

            $modelOperatorStatus = OperatorStatus::model()->find(
                "id_user = " . Yii::app()->session['id_user']);
            $modelOperatorStatus->categorizing = 0;

            //calcula a media do tempo gasto para categorizar e set o time que categorizou para pegar e calcular o tempo livre ate a proxima chamada
            if (isset($modelOperatorStatus->time_start_cat) && $modelOperatorStatus->time_start_cat > 0) {
                $time_to_call = time() - $modelOperatorStatus->time_start_cat;
                $media        = (($modelOperatorStatus->media_to_cat * $modelOperatorStatus->cant_cat) + $time_to_call) / ($modelOperatorStatus->cant_cat + 1);
                $media        = intval($media);

                $modelOperatorStatus->time_start_cat = 0;
                $modelOperatorStatus->media_to_cat   = $media;
                $modelOperatorStatus->time_free      = time();
                $modelOperatorStatus->cant_cat       = $modelOperatorStatus->cant_cat + 1;
            }

            $modelOperatorStatus->save();

            $modelCampaign = Campaign::model()->findByPk((int) $modeUser->id_campaign);

            OperatorStatusManager::unPause(Yii::app()->session['id_user'],
                $modelCampaign, 1);

            $this->sendCaterorizingToExternalUrl($model);
        }
    }

    public function sendCaterorizingToExternalUrl($model)
    {

        $url = $this->config['global']['notify_url_after_save_number'];

        if (strlen($url) > 5) {
            $category = explode(",", $this->config['global']['notify_url_category']);

            if (in_array($model->id_category, $category)) {
                $post = json_encode($model);
                $post = urlencode($post);
                file_get_contents($url . "?row=$post");
            }

        }
    }
    public function actionAutoLoadPhoneNumber()
    {
        //se o operador tiver auto_load_phonenumber = 1, retornar true.
        $modelUser = User::model()->find("auto_load_phonenumber = 1 AND id = :id_user", array(':id_user' => Yii::app()->session['id_user']));
        if (count($modelUser)) {
            if (strlen(Yii::app()->session['open_url_when_answer_call'] < 1)) {
                $modelCampaign                                   = Campaign::model()->findByPk($modelUser->id_campaign);
                Yii::app()->session['open_url_when_answer_call'] = $modelCampaign->open_url_when_answer_call;
            }
            if (strlen(Yii::app()->session['open_url_when_answer_call']) > 10) {
                //example
                //http://google.com?id=%dni%&numero=%number%&magnus=1&name=%name%
                if ($modelUser->id_current_phonenumber > 0) {

                    $url = Yii::app()->session['open_url_when_answer_call'];

                    if (preg_match('/\%/', $url)) {
                        $modelPhoneNumber = $this->abstractModel->findByPk($modelUser->id_current_phonenumber);
                        $parts            = parse_url($url);
                        parse_str($parts['query'], $query);
                        foreach ($query as $key => $value) {

                            if ($key == 'background') {
                                $background = true;
                                continue;
                            } elseif (preg_match('/\%sipaccount\%/', $value)) {
                                $url = preg_replace('/%sipaccount%/', Yii::app()->session['username'], $url);
                                continue;
                            }
                            if (preg_match('/\%/', $value)) {
                                $value = preg_replace('/\%/', '', $value);
                                $url   = preg_replace('/%' . $value . '%/', urlencode($modelPhoneNumber->{$value}), $url);
                            }
                        }
                    }

                    if (isset($background) && $background == 1) {
                        file_get_contents($url);
                    } else {
                        echo $url;
                    }

                } else {
                    echo '1';
                }
            } else {
                echo '1';
            }
            $modelUser->auto_load_phonenumber = 0;
            $modelUser->save();
        }
    }

    public function readSetOrder($sort)
    {
        $dir   = isset($_GET[$this->nameParamDir]) ? ' ' . $_GET[$this->nameParamDir] : null;
        $order = $sort ? $sort . $dir : null;

        return $this->replaceOrder($order);
    }

    public function setfilter($value)
    {

        if (Yii::app()->session['isOperator']) {

            $id_campaign = Yii::app()->session['id_campaign'];

            $modelUser = User::model()->findByPk(Yii::app()->session['id_user']);

            if ($modelUser->id_current_phonenumber > 0) {
                $filter = "t.id = " . $modelUser->id_current_phonenumber;
            } else {

                $this->checkScheduledNumbers();

                $filter      = ' pkg_campaign_phonebook.id_campaign = ' . $id_campaign . ' AND t.status = 1 AND (t.id_category = 1 OR t.id_category = 8) ';
                $this->order = 'id_category DESC , RAND( )';
            }

            $this->filter = $filter;
            $this->limit  = 1;
        } else {
            parent::setfilter($value);
        }
    }

    public function checkScheduledNumbers()
    {
        /*
        Se tem algum numero agendado para o operador mostart
        Se tem algum numero agendado para qualquer operador mostrar.
        intervalTime = now - 10 minutes
         */
        $timeToCall = date('Y-m-d H:i', mktime(date('H'), date('i') - 10, date('s'), date('m'), date('d'), date('Y')));

        //verifica se tem numero agendado somente para o operadora
        $modelPhoneNumber = $this->abstractModel->findAll(array(
            'condition' => "id_category = 2 AND datebackcall BETWEEN '$timeToCall' AND  NOW() AND id_user =  " . Yii::app()->session['id_user'],
        ));

        if (count($modelPhoneNumber) > 0) {
            //encontoru numero agenda para mim
            echo json_encode(array(
                $this->nameRoot  => $this->getAttributesModels($modelPhoneNumber, $this->extraValues),
                $this->nameCount => 1,
                $this->nameSum   => array(),
            ));
            $this->afterRead($modelPhoneNumber);
            exit;

        } else {
            //verifica se tem numero agendado para qualquer operador

            $modelPhoneNumber = $this->abstractModel->findAll(array(
                'condition' => "id_category = 2 AND datebackcall BETWEEN '$timeToCall' AND  NOW()",
                'order'     => 'id DESC , RAND( )',
                'limit'     => 1,
            ));

            if (count($modelPhoneNumber) > 0) {
                //encontoru numero agenda para mim
                echo json_encode(array(
                    $this->nameRoot  => $this->getAttributesModels($modelPhoneNumber, $this->extraValues),
                    $this->nameCount => 1,
                    $this->nameSum   => array(),
                ));
                $this->afterRead($modelPhoneNumber);
                exit;
            }
        }
    }

    public function beforeRead($value)
    {

        if (Yii::app()->session['isOperator'] && Yii::app()->session['id_campaign'] < 1) {

            echo json_encode(array(
                $this->nameRoot  => array(),
                $this->nameCount => 0,
            ));
            exit;
        }

    }

    public function afterRead($records)
    {
        //desativa o numero para no mostrar a otro user
        if (Yii::app()->getSession()->get('isOperator') && count($records) > 0) {
            $modelPhoneNumber          = PhoneNumber::model()->findByPk((int) $records[0]['id']);
            $modelPhoneNumber->id_user = Yii::app()->session['id_user'];
            $modelPhoneNumber->save();

            $modelUser                         = User::model()->findByPk((int) Yii::app()->session['id_user']);
            $modelUser->id_current_phonenumber = $records[0]['id'];
            $modelUser->save();
        }
    }

    public function actionSample()
    {
        if (!Yii::app()->getSession()->get('isAdmin')) {

            $modelPhonenumber = PhoneNumber::model()->findByPk((int) $_POST['id']);
            $destination      = $modelPhonenumber->{$_POST['field']};

            if (strlen($destination) < 1) {
                echo json_encode(array(
                    $this->nameSuccess => false,
                    $this->nameMsg     => 'Number not found',
                ));
                exit;
            }

            $dialstr = 'PJSIP/' . Yii::app()->getSession()->get('username');

            // gerar os arquivos .call
            $call = "Channel: " . $dialstr . "\n";
            $call .= "Callerid: " . Yii::app()->getSession()->get('username') . "\n";
            $call .= "Account:" . Yii::app()->getSession()->get('username') . "\n";
            $call .= "MaxRetries: 0\n";
            $call .= "RetryTime: 100\n";
            $call .= "WaitTime: 45\n";
            $call .= "Context: magnuscallcenter\n";
            $call .= "Extension: " . $destination . "\n";
            $call .= "Priority: 1\n";
            $call .= "Set:CALLED=" . $destination . "\n";
            $call .= "Set:accountcode=" . Yii::app()->getSession()->get('username') . "\n";
            $call .= "Set:PHONENUMBER_ID=" . $modelPhonenumber->id . "\n";

            $aleatorio    = str_replace(" ", "", microtime(true));
            $arquivo_call = "/var/spool/asterisk/outgoing/$aleatorio.call";
            $fp           = fopen("$arquivo_call", "a+");
            fwrite($fp, $call);
            fclose($fp);

            touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
            chown("$arquivo_call", "asterisk");
            chgrp("$arquivo_call", "asterisk");
            chmod("$arquivo_call", 0777);

            echo json_encode(array(
                $this->nameSuccess => true,
                $this->nameMsg     => $this->msgSuccess,
            ));
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => $this->msgError,
            ));
        }

    }

    public function importCsvSetAdditionalParams()
    {
        $values = $this->getAttributesRequest();
        return [
            ['key' => 'id_phonebook', 'value' => $values['id_phonebook']],
            ['key' => 'id_category', 'value' => 1],
            ['key' => 'id_team', 'value' => Yii::app()->session['id_team']],
        ];
    }

    public function actionReprocesar()
    {
        # recebe os parametros para o filtro
        if (isset($_POST['filter']) && strlen($_POST['filter']) > 5) {
            $filter = $_POST['filter'];
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Por favor realizar un filtro para reprocesar',
            ));
            exit;
        }
        $filter = $filter ? $this->createCondition(json_decode($filter)) : '';

        $namePhoneBook = 'Reprocesada ' . date('Y-m-d H:i:s');

        $sql = "INSERT INTO pkg_phonebook (id_trunk, name, description, status, show_numbers_operator)
            VALUES ((SELECT id FROM pkg_trunk WHERE 1 LIMIT 1), '$namePhoneBook' , 'Reprocesada', '1','1')";

        Yii::app()->db->createCommand($sql)->execute();
        $id_phonebook = Yii::app()->db->lastInsertID;

        $categorias = $this->abstractModel->findAll(array(
            'select'    => 'DISTINCT id_category',
            'join'      => $this->join,
            'condition' => $filter,
            'params'    => $this->paramsFilter,
        ));

        $sql     = "SELECT DISTINCT id_category FROM pkg_phonenumber t $this->join WHERE $filter AND id_category >= 0";
        $command = Yii::app()->db->createCommand($sql);
        foreach ($this->paramsFilter as $key => $value) {
            $command->bindValue(":$key", $value, PDO::PARAM_STR);
        }
        $categorias = $command->queryAll();

        $categorias_nomes = '<br>';
        foreach ($categorias as $value) {
            $sql             = "SELECT name FROM pkg_category  WHERE id = " . $value['id_category'];
            $categorias_name = Yii::app()->db->createCommand($sql)->queryAll();
            $categorias_nomes .= isset($categorias_name[0]['name']) ? $categorias_name[0]['name'] . "<br>" : '';
        }

        $sql     = "DESCRIBE pkg_phonenumber";
        $command = Yii::app()->db->createCommand($sql)->queryAll();
        $fields  = "NULL, $id_phonebook, ";
        foreach ($command as $key => $value) {
            if ($value['Field'] == 'id' || $value['Field'] == 'id_phonebook') {
                continue;
            }
            $fields .= $value['Field'] . ', ';
        }
        $fields = substr($fields, 0, -2);
        $sql    = "INSERT INTO pkg_phonenumber
            SELECT $fields FROM pkg_phonenumber t WHERE $filter";
        $command = Yii::app()->db->createCommand($sql);
        foreach ($this->paramsFilter as $key => $value) {
            $command->bindValue(":$key", $value, PDO::PARAM_STR);
        }
        try {
            $command->execute();
        } catch (Exception $e) {

        }

        $sql = "UPDATE pkg_phonenumber SET status = 1, id_category = 1, try = 0, id_user = NULL WHERE id_phonebook = " . $id_phonebook;
        Yii::app()->db->createCommand($sql)->execute();

        MagnusLog::insertLOG(4, 'Nova Agenda criada. Nome=' . $namePhoneBook . ', IDPHONEBOOK=' . $id_phonebook . ' Caterorias incluidas:</b> ' . $categorias_nomes);

        echo json_encode(array(
            $this->nameSuccess => $this->success,
            $this->nameMsg     => 'Nova Agenda criada<br> Nome -> ' . $namePhoneBook . ' <br><br><b> Caterorias incluidas:</b> ' . $categorias_nomes,
        ));

    }

    public function actionInactveActive()
    {
        # recebe os parametros para o filtro
        if (isset($_POST['filter']) && strlen($_POST['filter']) > 5) {
            $filter = $_POST['filter'];
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Por favor realizar um filtro',
            ));
            exit;
        }

        $filter = $filter ? $this->createCondition(json_decode($filter)) : '';

        if (!preg_match('/honebook/', $filter)) {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Por favor filtre una agenda',
            ));
            exit;
        } else {
            $filter = preg_replace("/idPhonebookname/", 'g.name', $filter);
        }

        $sql     = "UPDATE pkg_phonenumber t  JOIN pkg_phonebook g ON t.id_phonebook = g.id SET t.id_category = 1, try = 0, last_trying_number = 1 WHERE t.id_category = 0 AND $filter";
        $command = Yii::app()->db->createCommand($sql);
        foreach ($this->paramsFilter as $key => $value) {
            $command->bindValue(":$key", $value, PDO::PARAM_STR);
        }
        $resultAdmin = $command->execute();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg     => 'Números atualizados com sucesso',
        ));
    }

    public function afterImportFromCsv($values)
    {
        if ($values['allowDuplicate'] == 1) {
            //remove duplicates in the phonebook
            $sql    = "SELECT id FROM pkg_phonenumber WHERE id_phonebook = " . $values['id_phonebook'] . " GROUP BY number HAVING  COUNT(number) > 1";
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $ids    = '';
            foreach ($result as $key => $value) {
                $ids .= $value['id'] . ',';
            }

            $sql = "DELETE FROM pkg_phonenumber WHERE id IN (" . substr($ids, 0, -1) . ")";
            try {
                Yii::app()->db->createCommand($sql)->execute();
            } catch (Exception $e) {

            }
        }
        return;
    }
}
