<?php
/**
 * Acoes do modulo "CcCallOnLine".
 *
 * MagnusSolution.com <info@magnussolution.com> 
 * 19/09/2012
 */

class CallOnLineController extends Controller
{
    public $attributeOrder     = 't.status DESC';
    public $extraValues        = array('idUser' => 'username,name');
    public $fieldCard          = 'id_user';

    public $join               = 'JOIN pkg_user c ON t.id_user = c.id';
    private $host = 'localhost';
    private $user = 'magnus';
    private $password = 'magnussolution';

    public $fieldsInvisibleOperator = array(
        'canal',
        'tronco'
        );

    public $fieldsInvisibleAgent = array(
        'canal',
        'tronco'
        );
    

    public function init()
    {
        $this->instanceModel = new CallOnLine;
        $this->abstractModel = CallOnLine::model();
        $this->titleReport   = Yii::t('yii','CallOnLine');
        parent::init();
    }

    public function actionRead()
    {
        
            $this->asteriskCommand();
        return parent::actionRead();
    }

    public function actionCheck()
    {
        $this->asteriskCommand();
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {
            $attributes[$key] = $item->attributes;

            if (isset($item->id_campaign) && $item->id_campaign > 0 && Yii::app()->controller->action->id === 'read') 
            {
                //get username for did number
                $sql = "SELECT * FROM pkg_campaign WHERE id =".$item->id_campaign;
                $resultCampaign = Yii::app()->db->createCommand($sql)->queryAll();
                $attributes[$key]['id_campaign'] = $resultCampaign[0]['name'];
            }
            
            if(isset($_SESSION['isOperator']) && $_SESSION['isOperator'])
            {
                foreach ($this->fieldsInvisibleOperator as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if($_SESSION['isOperator']) {
                        foreach ($this->fieldsInvisibleOperator as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }
        return $attributes;
    }


    
    public function actionDestroy()
    {
        $asmanager = new AGI_AsteriskManager;
        $conectaServidor = $conectaServidor = $asmanager->connect($this->host, $this->user, $this->password);
        $values = $this->getAttributesRequest();
        $result = $this->abstractModel->findByPk($values['id']);
       
        if(count($result) > 0)
        {
            $server = $asmanager->Command("hangup request $result->canal");
            $success = true;
            $msn = Yii::t('yii', 'Operation was successful.').Yii::app()->language;
        }
        else
        {
            $success = false;
            $msn = Yii::t('yii', 'Disallowed action');
        }

        echo json_encode(array(
                'success' => $success,
                'msg' => $msn
            ));
        exit();
    }


    public function asteriskCommand ()
    {
        $modelClear = $this->instanceModel;
        //$success = $modelClear->deleteAll();
        
        $asmanager = new AGI_AsteriskManager;
        $conectaServidor = $conectaServidor = $asmanager->connect($this->host, $this->user, $this->password);

           
        $sql = array();

        $server = $asmanager->Command("core show channels concise");
        $arr = explode("\n", $server["data"]);

        foreach ($arr as $temp)
        {
            //echo '<pre>';
            
            $linha = explode("!", $temp);
            if(!isset($linha[1]))
                continue;  



            $canal = $linha[0];
            if (preg_match("/AppQueue/", $linha[5])){
                //se for uma usuario atendendo preditive
                $username = explode("-",substr($linha[0],4) );
                $username = isset($username[0]) ? $username[0] : 0;

                $tronco = explode("-",substr($linha[12],4) );
                $tronco = isset($tronco[0]) ? $tronco[0] : 0;

            }
                
            else{
                $username = isset($linha[7]) ? $linha[7] : 0;
                $tronco = explode("/",substr($linha[6],4) );
                $tronco = isset($tronco[0]) ? $tronco[0] : 0;
            }
                


              //print_r($linha); 



            if (!preg_match("/SIP\/$username/", $canal))
                continue;


            $ndiscado = isset($linha[2]) ? $linha[2] : NULL;

            $result = $asmanager->Command("core show channel $canal");



            $arr2 = explode("\n", $result["data"]);
            
            

            $sql = "SELECT * FROM pkg_user  WHERE username = '$username'";
            $resultUsersOnline = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($resultUsersOnline) < 1) {
                continue;
            }
            $id_user = $resultUsersOnline[0]['id'];

            foreach ($arr2 as $temp2)
            {

                //pega codec
                if (strstr($temp2, 'NativeFormat')) 
                {
                    $arr3 = explode("NativeFormat:", $temp2);
                    $arr3 = explode("(", $arr3[0]);                        
                    $codec = preg_replace("/\)/","", $arr3[1]);
                }
               

                if (strstr($temp2, 'duration')) 
                {
                    $arr3 = explode("duration=", $temp2);                
                    $duration = trim(rtrim($arr3[1]));
                }
            }


            $sql = "UPDATE pkg_call_online SET canal = '$canal', ndiscado = '$ndiscado', tronco = '$tronco', codec = '$codec',  duration = '$duration'   WHERE id_user = $id_user ";        
            try {
               Yii::app()->db->createCommand($sql)->execute();
            } catch (Exception $e) {
                
            }
            
            
      
        }

        $sql = "SELECT id_campaign, status FROM pkg_call_online GROUP BY id_campaign";
        $resultCampaignCall = Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($resultCampaignCall as $key => $Campaign) {


            $sql = "SELECT * FROM pkg_campaign WHERE id =".$Campaign['id_campaign'] ;
            $resultCampaign = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($resultCampaign) == 0) {
                continue;
            }
            $server = $asmanager->Command("queue show ".$resultCampaign[0]['name']);
            $arr = explode("\n", $server["data"]);

            foreach ($arr as $key => $line) {
                $line = trim($line);

                if (substr($line, 0,3) == 'SIP') {

                   
                    $data = explode('(', $line);

                   

                    $username = explode("/", $data[0]);
                    $username = trim($username[1]);

                    if (preg_match("/dynamic/",  $data[2])) {
                        $status = trim($data[3]);
                        $status = explode(")", $status);
                        $status = $status[0];

                        if (isset($data[5])) {
                            $lastCall = explode(" ", $data[5]);

                           

                            $lastCall = isset($lastCall[2]) ? $lastCall[2] : 0;
                        }
                        else{
                            $lastCall = 0;
                        }
                    }else{
                        $status = trim($data[2]);
                        $status = explode(")", $status);
                        $status = $status[0];

                        if (isset($data[3])) {
                            $lastCall = explode(" ", $data[3]);
                            $lastCall = isset($lastCall[2]) ? $lastCall[2] : 0;
                        }
                        else{
                            $lastCall = 0;
                        }
                    }

                    

                    $sql = "SELECT id,id_campaign FROM pkg_user WHERE username = '$username'";
                    $resultuser = Yii::app()->db->createCommand($sql)->queryAll();
                    if (isset($resultuser[0]['id'])) {

                        $sql = "SELECT categorizando FROM pkg_call_online WHERE id_user = ". $resultuser[0]['id'];
                        $resultuserCAtegorizando = Yii::app()->db->createCommand($sql)->queryAll();

                        if ($status != 'in call' && $status != 'In use' ) {
                            $updateUser = ", tronco = '', duration = 0, codec = '', ndiscado = ''";
                        }
                        else{
                            $updateUser = '';
                            $lastCall = 0;
                            $status = 'in call';
                        }

                        if ($status == 'paused') {
                            

                            
                       
                            $sql = "SELECT status FROM pkg_call_online WHERE id_user = ". $resultuser[0]['id'];
                            $resultuserStatus = Yii::app()->db->createCommand($sql)->queryAll();
                            
                            if (isset($resultuserStatus[0]['status']) && $resultuserStatus[0]['status'] == 'categorizando' ) {
                                $status = 'categorizando';
                                $updateUser .= ", duration = media_to_cat";
                            }else{
                                //nao esta categorizando, mas esta em pausa, calcular o tempo
                                $sql = "SELECT starttime FROM pkg_logins_campaign WHERE id_user = ".$resultuser[0]['id']." 
                                        AND type = 'pause' AND stoptime = '0000-00-00 00:00:00' AND starttime > '".date("Y-m-d")."'";
                                $resultuserPause = Yii::app()->db->createCommand($sql)->queryAll();
                                if (isset($resultuserPause[0]['starttime'])) {
                                    $timePause = time() - strtotime($resultuserPause[0]['starttime']);
                                    $updateUser .= ", duration = ".$timePause;
                                }else{
                                    //if agent is paused
                                    $turno = $this->detectTurno($resultCampaign, date('H:i:s'));
                                    $sql = "INSERT INTO pkg_logins_campaign (id_campaign, id_user,total_time,type,turno) VALUES 
                                        (".$Campaign['id_campaign'].",".$resultuser[0]['id'].",0,'pause','".$turno."')";
                                    Yii::app()->db->createCommand($sql)->execute();
                                }
                                
                                
                                $status = "Descanso";                           

                            }

                        }

                         if ($status == 'Not in use') {
                            $sql = "SELECT time_free FROM pkg_call_online WHERE id_user = ". $resultuser[0]['id'];
                            $resultuserStatus = Yii::app()->db->createCommand($sql)->queryAll();

                            if (isset($resultuserStatus[0]['time_free']) && $resultuserStatus[0]['time_free'] > 0) {
                                $time_free = time() - $resultuserStatus[0]['time_free'];
                                $updateUser .= ", duration = $time_free";
                            }
                        }

                        if (isset($resultuserCAtegorizando[0]['categorizando']) && $resultuserCAtegorizando[0]['categorizando'] == '1' ) {
                            $status = 'categorizando';
                            $updateUser .= ", duration = media_to_cat";
                        }
                    
                    
                        $sql = "UPDATE pkg_call_online SET status = '$status', lastcall = '$lastCall' $updateUser WHERE id_user = ". $resultuser[0]['id'];
                        try {
                            Yii::app()->db->createCommand($sql)->execute();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
 
                 
               
            }
            
        }
    }

    public function actionSpyCall()
    {

        $sql          = "SELECT config_value FROM pkg_configuration JOIN pkg_sip on config_value = name WHERE config_key LIKE 'channel_spy' ";
        $result       = Yii::app()->db->createCommand($sql)->queryAll();


        if(count($result) == 0){
            echo json_encode(array(
                'success' => false,
                'msg' => 'Invalid SIP for spy call'
            ));
            exit;
        }

        $dialstr = 'SIP/'.$result[0]['config_value'];
        // gerar os arquivos .call
        $call = "Action: Originate\n";
        $call .= "Channel: " . $dialstr . "\n";
        $call .= "Callerid: " . Yii::app()->session['username']  . "\n";
        $call .= "Context: billing\n";
        $call .= "Extension: 5555\n";
        $call .= "Priority: 1\n";
        $call .= "Set:USERNAME=" . Yii::app()->session['username'] . "\n";
        $call .= "Set:SPY=1\n";
        $call .= "Set:CHANNELSPY=" . $_POST['channel'] . "\n";

        Yii::log($call, 'info');
        $aleatorio = str_replace(" ", "", microtime(true));
        $arquivo_call = "/tmp/$aleatorio.call";
        $fp = fopen("$arquivo_call", "a+");
        fwrite($fp, $call);
        fclose($fp);

        touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
        chown("$arquivo_call", "asterisk");
        chgrp("$arquivo_call", "asterisk");
        chmod("$arquivo_call", 0755);
        exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");


        echo json_encode(array(
                'success' => true,
                'msg' => 'Start Spy'
            ));
    }
    
}