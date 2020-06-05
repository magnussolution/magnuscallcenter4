<?php
/**
 * Classe de com funcionalidades globais
 *
 * MagnusCallCenter <info@magnussolution.com>
 * 08/06/2013
 */

class AsteriskAccess
{
    private $asmanager;
    private static $instance;

    public static function instance($host = 'localhost', $user = 'magnus', $pass = 'magnussolution')
    {
        if (is_null(self::$instance)) {
            self::$instance = new AsteriskAccess();
        }
        self::$instance->connectAsterisk($host, $user, $pass);
        return self::$instance;
    }

    private function __construct()
    {
        $this->asmanager = new AGI_AsteriskManager;
    }

    private function connectAsterisk($host, $user, $pass)
    {
        $this->asmanager->connect($host, $user, $pass);
    }

    public function queueAddMember($member, $queue)
    {
        $this->asmanager->Command("queue add member PJSIP/" . $member . " to " . preg_replace("/ /", "\ ", $queue));
    }

    public function queueRemoveMember($member, $queue)
    {
        $this->asmanager->Command("queue remove member PJSIP/" . $member . " from " . preg_replace("/ /", "\ ", $queue));
    }

    public function queuePauseMember($member, $queue, $reason = 'normal')
    {
        $this->asmanager->Command("queue pause member PJSIP/" . $member . " queue " . preg_replace("/ /", "\ ", $queue) . " reason " . $reason);
    }

    public function queueUnPauseMember($member, $queue, $reason = 'normal')
    {
        $this->asmanager->Command("queue unpause member PJSIP/" . $member . " queue " . preg_replace("/ /", "\ ", $queue) . " reason " . $reason);
    }

    public function queueShow($queue)
    {
        return $this->asmanager->Command("queue show " . $queue);
    }

    public function queueReload()
    {
        return $this->asmanager->Command("queue reload all");
    }

    public function queueReseteStats($queue)
    {
        return $this->asmanager->Command("queue reset stats " . $queue);
    }

    public function hangupRequest($channel)
    {
        return $this->asmanager->Command("hangup request " . $channel);
    }

    public function pjsipReload()
    {
        return $this->asmanager->Command("pjsip reload");
    }

    public function sipShowPeers()
    {
        return $this->asmanager->Command("sip show peers");
    }

    public function pjsipListRegistrations()
    {
        return $this->asmanager->Command("pjsip list registrations");
    }

    public function iaxReload()
    {
        return @$this->asmanager->Command("iax reload");
    }

    public function coreShowChannelsConcise()
    {
        return @$this->asmanager->Command("core show channels concise");
    }

    public function coreShowChannel($channel)
    {
        return @$this->asmanager->Command("core show channel " . $channel);
    }
    public function sipShowChannel($channel)
    {
        return @$this->asmanager->Command("sip show channel " . $channel);
    }

    public function queueGetMemberStatus($member, $campaign_name)
    {
        $queueData = AsteriskAccess::instance()->queueShow($campaign_name);
        $queueData = explode("\n", $queueData["data"]);
        $status    = "error";
        foreach ($queueData as $key => $data) {

            $data = trim($data);

            if (preg_match("/SIP\/" . Yii::app()->session['username'] . "/", $data)) {
                $line   = explode('(', $data);
                $status = trim($line[3]);
                $status = explode(")", $status);

                $status = $status[0];
                break;
            }
        }
        return $status;
    }

    public function writeQueueFile($model, $file, $head_field = 'name')
    {
        $rows = Util::getColumnsFromModel($model);

        $fd = fopen($file, "w");

        LinuxAccess::exec('touch ' . $file);

        if (!$fd) {
            echo "</br><center><b><font color=red>" . gettext("Could not open buddy file") . $file . "</font></b></center>";
        } else {
            foreach ($rows as $key => $data) {
                $line         = "\n\n[" . $data[$head_field] . "]\n";
                $registerLine = '';
                foreach ($data as $key => $option) {
                    if ($key == $head_field) {
                        continue;
                    }

                    $line .= $key . '=' . $option . "\n";

                }

                if (fwrite($fd, $line) === false) {
                    echo "Impossible to write to the file ($buddyfile)";
                    break;
                }
            }

            fclose($fd);

            AsteriskAccess::instance()->queueReload();

        }
    }

    //model , file, e o nome para o contexto
    public function writeAsteriskFile($model, $file, $head_field = 'name')
    {
        $rows = Util::getColumnsFromModel($model);

        $fd = fopen($file, "w");

        LinuxAccess::exec('touch ' . $file);

        if ($head_field == 'trunkcode' && preg_match("/sip/", $file)) {
            $registerFile = '/etc/asterisk/sip_magnus_register.conf';
            LinuxAccess::exec('touch ' . $registerFile);
            $fr = fopen($registerFile, "w");
        } elseif ($head_field == 'trunkcode' && preg_match("/iax/", $file)) {
            $registerFile = '/etc/asterisk/iax_magnus_register.conf';
            LinuxAccess::exec('touch ' . $registerFile);
            $fr = fopen($registerFile, "w");
        }

        if (!$fd) {
            echo "</br><center><b><font color=red>" . "Could not open buddy file" . $file . "</font></b></center>";
        } else {
            foreach ($rows as $key => $data) {
                $registerLine = $line = "\n";

                //registrar tronco
                if ($data['register'] == 1) {

                    $line .= "\n\n[reg_" . $data[$head_field] . '_' . $data['user'] . '_' . $data['host'] . "]\n";
                    $line .= "type = registration\n";
                    $line .= "retry_interval = 20\n";
                    $line .= "max_retries = 10\n";
                    $line .= "expiration = 120\n";
                    $line .= "transport = transport-udp\n";
                    $line .= "outbound_auth = auth_reg_" . $data[$head_field] . '_' . $data['user'] . '_' . $data['host'] . "\n";
                    $line .= "client_uri = sip:" . $data['user'] . '@' . $data['host'] . "\n";
                    $line .= "server_uri = sip:" . $data['host'] . "\n";
                    $line .= "contact_user = " . $data['user'] . "\n";
                }

                if (strlen($data['user']) && strlen($data['secret'])) {
                    $line .= "\n[auth_reg_" . $data[$head_field] . '_' . $data['user'] . '_' . $data['host'] . "]\n";
                    $line .= "type = auth\n";
                    $line .= "username = " . $data['user'] . "\n";
                    $line .= "password = " . $data['secret'] . "\n";
                }

                $line .= "\n[" . $data[$head_field] . "]\n";
                $line .= "type = aor\n";
                if (strlen($data['user'])) {
                    $line .= "contact = sip:" . $data['user'] . '@' . $data['host'] . "\n";
                } else {
                    $line .= "contact = sip:" . $data['host'] . "\n";
                }
                if ($data['qualify'] > 29) {
                    $line .= "qualify_frequency = " . $data['qualify'] . "\n";
                }
                $line .= "max_contacts = 1\n";

                $line .= "\n[" . $data[$head_field] . "]\n";
                $line .= "type = identify\n";
                $line .= "endpoint = " . $data[$head_field] . "\n";
                $line .= "match = " . strtok($data['host'], ':') . "\n";

                $line .= "\n[" . $data[$head_field] . "]\n";
                $line .= "type = endpoint\n";
                $line .= "context = " . $data['context'] . "\n";
                $line .= "dtmf_mode = rfc4733\n";
                $line .= "disallow = all\n";
                $line .= "allow = " . $data['allow'] . "\n";
                $line .= "rtp_symmetric = yes\n";
                $line .= "force_rport = yes\n";
                $line .= "rewrite_contact = yes\n";
                $line .= "direct_media = " . $data['directmedia'] . "\n";
                $line .= "language = " . $data['language'] . "\n";
                $line .= "allow_subscribe = yes\n";
                $line .= "aors = " . $data[$head_field] . "\n";
                if (strlen($data['fromuser'])) {
                    $line .= "from_user = " . $data['fromuser'] . "\n";
                }
                if (strlen($data['fromuser'])) {
                    $line .= "from_domain = " . $data['fromdomain'] . "\n";
                }
                if (strlen($data['user']) && strlen($data['secret'])) {
                    $line .= "auth = auth_reg_" . $data[$head_field] . '_' . $data['user'] . '_' . $data['host'] . "\n";
                    $line .= "outbound_auth = auth_reg_" . $data[$head_field] . '_' . $data['user'] . '_' . $data['host'] . "\n";
                }

                if (fwrite($fd, $line) === false) {
                    echo "Impossible to write to the file ($buddyfile)";
                    break;
                }
            }

            fclose($fd);

            if (preg_match("/sip/", $file)) {
                AsteriskAccess::instance()->pjsipReload();
            } elseif (preg_match("/iax/", $file)) {
                AsteriskAccess::instance()->iaxReload();
            } else {
                AsteriskAccess::instance()->queueReload();
            }

        }
    }
    //call file , time in seconds to create the file
    public static function generateCallFile($callFile, $time = 0)
    {
        $aleatorio    = str_replace(" ", "", microtime(true));
        $arquivo_call = "/tmp/$aleatorio.call";
        $fp           = fopen("$arquivo_call", "a+");
        fwrite($fp, $callFile);
        fclose($fp);

        $time += time();

        touch("$arquivo_call", $time);
        @chown("$arquivo_call", "asterisk");
        @chgrp("$arquivo_call", "asterisk");
        chmod("$arquivo_call", 0755);

        LinuxAccess::system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
    }

    public function getCallsPerUser($accountcode)
    {
        $channelsData = AsteriskAccess::instance()->coreShowChannelsConcise();
        $channelsData = explode("\n", $channelsData["data"]);
        $modelSip     = Sip::model()->findAll('accountcode = :key', array(':key' => $accountcode));
        $sipAccounts  = '';
        foreach ($modelSip as $key => $sip) {
            $sipAccounts .= $sip->name . '|';
        }

        $sipAccounts = substr($sipAccounts, 0, -1);
        $calls       = 0;
        foreach ($channelsData as $key => $line) {
            if (preg_match("/^SIP\/($sipAccounts)-/", $line)) {
                $calls++;
            }
        }

        return $calls;
    }

    public function groupTrunk($agi, $ipaddress, $maxuse)
    {
        if ($maxuse > 0) {

            $agi->verbose('Trunk have channels limit', 8);
            //Set group to count the trunk call use
            $agi->set_variable("GROUP()", $ipaddress);

            $groupData = @$this->asmanager->Command("group show channels");

            $arr   = explode("\n", $groupData["data"]);
            $count = 0;
            if ($arr[0] != "") {
                foreach ($arr as $temp) {
                    $linha = explode("  ", $temp);

                    if (trim($linha[4]) == $ipaddress) {
                        $channel = @$this->asmanager->Command("core show channel " . $linha[0]);
                        $arr2    = explode("\n", $channel["data"]);

                        foreach ($arr2 as $temp2) {
                            if (strstr($temp2, 'State:')) {
                                $arr3   = explode("State:", $temp2);
                                $status = trim(rtrim($arr3[1]));
                            }
                        }

                        if (preg_match("/Up |Ring /", $status)) {
                            $count++;
                        }
                    }
                }
            }
            if ($count > $maxuse) {
                $agi->verbose('Trunk ' . $ipaddress . ' have  ' . $count . ' calls, and the maximun call is ' . $maxuse, 2);
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function getSipShowPeers()
    {
        $modelServers = Servers::model()->getAllAsteriskServers();
        $result       = array();
        foreach ($modelServers as $key => $server) {
            $data = AsteriskAccess::instance($server['host'], $server['username'], $server['password'])->sipShowPeers();

            if (!isset($data['data']) || strlen($data['data']) < 10) {
                continue;
            }

            $linesSipResult = explode("\n", $data['data']);

            $column  = 'Name/username             Host                                    Dyn Forcerport Comedia    ACL Port     Status      Description';
            $columns = preg_split("/\s+/", $column);

            $index = array();

            for ($i = 0; $i < 10; $i++) {
                $index[] = @strpos($column, $columns[$i]);
            }

            foreach ($linesSipResult as $key => $line) {
                $element = array();
                foreach ($index as $key => $value) {
                    $startIndex               = $value;
                    $lenght                   = @$index[$key + 1] - $value;
                    @$element[$columns[$key]] = trim(isset($index[$key + 1]) ? substr($line, $startIndex, $lenght) : substr($line, $startIndex));
                }
                $result[] = $element;

            }
        }
        return $result;
    }

    public static function getCoreShowChannels()
    {

        $modelServers = Servers::model()->getAllAsteriskServers();
        $channels     = array();
        foreach ($modelServers as $key => $server) {

            $columns = array('Channel', 'Context', 'Exten', 'Priority', 'Stats', 'Application', 'Data', 'CallerID', 'Accountcode', 'Amaflags', 'Duration', 'Bridged');
            $data    = AsteriskAccess::instance($server['host'], $server['username'], $server['password'])->coreShowChannelsConcise();

            if (!isset($data) || !isset($data['data'])) {
                return;
            }

            $linesCallsResult = explode("\n", $data['data']);

            if (count($linesCallsResult) < 1) {
                return;
            }

            for ($i = 0; $i < count($linesCallsResult); $i++) {
                $call = explode("!", $linesCallsResult[$i]);
                if (!preg_match("/\//", $call[0])) {
                    continue;
                }
                $call['server'] = $server['host'];
                $channels[]     = $call;

            }

        }
        return $channels;
    }
    public function getCoreShowChannel($channel)
    {

        $modelServers = Servers::model()->getAllAsteriskServers();
        $channels     = array();
        foreach ($modelServers as $key => $server) {

            $data = AsteriskAccess::instance($server['host'], $server['username'], $server['password'])->coreShowChannel($channel);
            if (!isset($data['data']) || strlen($data['data']) < 10 || preg_match("/is not a known channe/", $data['data'])) {
                continue;
            }
            $linesCallResult = explode("\n", $data['data']);
            if (count($linesCallResult) < 1) {
                continue;
            }
            $result = array();
            for ($i = 2; $i < count($linesCallResult); $i++) {
                if (preg_match("/level 1: /", $linesCallResult[$i])) {
                    $data = explode("=", substr($linesCallResult[$i], 9));
                } elseif (preg_match("/: /", $linesCallResult[$i])) {
                    $data = explode(":", $linesCallResult[$i]);
                } elseif (preg_match("/=/", $linesCallResult[$i])) {
                    $data = explode("=", $linesCallResult[$i]);
                }
                // echo '<pre>';
                //print_r($data);
                $key   = isset($data[0]) ? $data[0] : '';
                $value = isset($data[1]) ? $data[1] : '';

                if ($key == 'SIPCALLID') {
                    $result[trim($key)] = AsteriskAccess::instance($server['host'], $server['username'], $server['password'])->sipShowChannel(trim($value));

                } else {
                    $result[trim($key)] = trim($value);
                }

            }
            break;
        }

        return $result;

    }

    public static function generatePJSipPeers()
    {

        /*
        [1000]
        type = aor
        max_contacts = 1

        [1000]
        type = auth
        username = 1000
        password = magnus

        [1000]
        type = endpoint
        context = magnuscallcenter
        dtmf_mode = none
        disallow = all
        allow = g729
        allow = gsm
        allow = alaw
        allow = ulaw
        rtp_symmetric = yes
        rewrite_contact = yes
        from_user = 1000
        from_domain = dynamic
        auth = 1000
        outbound_auth = 1000
        aors = 1000

         */
        $select = 'id, accountcode, name, defaultuser, secret, regexten, amaflags, callerid, language, cid_number, disallow, allow, context, dtmfmode, insecure, nat, qualify, type, host, calllimit'; // add

        $list_friend = Sip::model()->findAll();

        $buddyfile = '/etc/asterisk/pjsip_magnus_user.conf';

        if (is_array($list_friend)) {

            $fd = fopen($buddyfile, "w");

            if ($fd) {
                foreach ($list_friend as $key => $data) {

                    $line = '';
                    $line = "\n\n\n[" . $data['name'] . "]\n";
                    $line .= "type = aor\n";
                    $line .= "max_contacts = 1\n";
                    $line .= "remove_existing = yes\n";
                    $line .= "qualify_frequency = 60\n";

                    $line .= "\n[" . $data['name'] . "]\n";
                    $line .= "type = auth\n";
                    $line .= 'username = ' . $data['name'] . "\n";
                    $line .= 'password = ' . $data['secret'] . "\n";

                    $line .= "\n[" . $data['name'] . "]\n";
                    $line .= "type = endpoint\n";
                    $line .= "context = magnuscallcenter\n";
                    $line .= "dtmf_mode = rfc4733\n";
                    $line .= "disallow = all\n";
                    $codecs = explode(",", $data['allow']);
                    foreach ($codecs as $codec) {
                        $line .= 'allow = ' . $codec . "\n";
                    }
                    $line .= "rtp_symmetric = yes\n";
                    $line .= "rewrite_contact = yes\n";
                    $line .= 'auth = ' . $data['name'] . "\n";
                    $line .= 'outbound_auth = ' . $data['name'] . "\n";
                    $line .= 'aors = ' . $data['name'] . "\n";
                    $line .= "direct_media = yes\n";
                    $line .= "media_use_received_transport = yes\n";
                    $line .= "force_rport = yes\n";

                    if ($data->idUser->webphone == 1) {
                        $line .= "dtls_auto_generate_cert = yes\n";
                        $line .= "webrtc = yes\n";
                    }

                    if (fwrite($fd, $line) === false) {
                        echo gettext("Impossible to write to the file") . " ($buddyfile)";
                        break;
                    }

                }

                fclose($fd);
            }

        }
        try {
            AsteriskAccess::instance()->pjsipReload();
        } catch (Exception $e) {

        }

    }
    public function generateIaxPeers()
    {

        $select = 'id, accountcode, name, defaultuser, secret, regexten, amaflags, callerid, language, cid_number, disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, calllimit'; // add

        $list_friend = Iax::model()->findAll();

        $buddyfile = '/etc/asterisk/iax_magnus_user.conf';

        Yii::log($buddyfile, 'error');

        if (is_array($list_friend)) {

            $fd = fopen($buddyfile, "w");

            if ($fd) {
                foreach ($list_friend as $key => $data) {
                    $line = "\n\n[" . $data['name'] . "]\n";
                    if (fwrite($fd, $line) === false) {
                        echo "Impossible to write to the file ($buddyfile)";
                        break;
                    } else {
                        $line = '';

                        $line .= 'host=' . $data['host'] . "\n";

                        $line .= 'fromdomain=' . $data['host'] . "\n";
                        $line .= 'accountcode=' . $data['accountcode'] . "\n";
                        $line .= 'disallow=' . $data['disallow'] . "\n";

                        $codecs = explode(",", $data['allow']);
                        foreach ($codecs as $codec) {
                            $line .= 'allow=' . $codec . "\n";
                        }

                        if (strlen($data['context']) > 1) {
                            $line .= 'context=' . $data['context'] . "\n";
                        }

                        if (strlen($data['dtmfmode']) > 1) {
                            $line .= 'dtmfmode=rfc4733' . "\n";
                        }

                        if (strlen($data['insecure']) > 1) {
                            $line .= 'insecure=' . $data['insecure'] . "\n";
                        }

                        if (strlen($data['nat']) > 1) {
                            $line .= 'nat=' . $data['nat'] . "\n";
                        }

                        if (strlen($data['qualify']) > 1) {
                            $line .= 'qualify=' . $data['qualify'] . "\n";
                        }

                        if (strlen($data['type']) > 1) {
                            $line .= 'type=' . $data['type'] . "\n";
                        }

                        if (strlen($data['regexten']) > 1) {
                            $line .= 'regexten=' . $data['regexten'] . "\n";
                        }

                        if (strlen($data['amaflags']) > 1) {
                            $line .= 'amaflags=' . $data['amaflags'] . "\n";
                        }

                        if (strlen($data['language']) > 1) {
                            $line .= 'language=' . $data['language'] . "\n";
                        }

                        if (strlen($data['username']) > 1) {
                            $line .= 'username=' . $data['username'] . "\n";
                        }

                        if (strlen($data['fromuser']) > 1) {
                            $line .= 'fromuser=' . $data['fromuser'] . "\n";
                        }

                        if (strlen($data['callerid']) > 1) {
                            $line .= 'callerid=' . $data['callerid'] . "\n";
                        }

                        if (strlen($data['secret']) > 1) {
                            $line .= 'secret=' . $data['secret'] . "\n";
                        }

                        if ($data['calllimit'] > 0) {
                            $line .= 'call-limit=' . $data['calllimit'] . "\n";
                        }

                        if (fwrite($fd, $line) === false) {
                            echo gettext("Impossible to write to the file") . " ($buddyfile)";
                            break;
                        }
                    }
                }
                fclose($fd);
            }
        }

        AsteriskAccess::instance()->iaxReload();

    }

}
