<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/Logger.php';
require __DIR__ . '/autoload.php';

use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Event\EventMessage;

while (true) {

    $options = array(
        'host'            => 'localhost',
        'scheme'          => 'tcp://',
        'port'            => 5038,
        'username'        => 'magnus',
        'secret'          => 'magnussolution',
        'connect_timeout' => 10,
        'read_timeout'    => 10,
    );

    try {
        $pamiClient = new PamiClient($options);
        // Open the connection
        $pamiClient->open();
    } catch (Exception $e) {
        Logger::write($e->getMessage() . ' at line ' . $e->getLine());
        sleep(1);
        continue;
    }

    $pamiClient->registerEventListener(function (EventMessage $event) {
        /*
        QUEUE events
        QueueCallerAbandon
        QueueMemberAdded
        QueueMemberPaused
        QueueMemberPenalty
        QueueMemberRemoved
        QueueMemberRinginuse
        QueueMemberStatus
         */

        $eventType = $event->getKeys()['event'];

        $ignoreEvents = array(
            'RTCPReceived',
            'VarSet',
            'RTCPSent',
            'Newexten',
        );
        if (in_array($eventType, $ignoreEvents)) {
            return;
        }

        //print_r($event->getKeys());
        try {
            switch ($eventType) {
                case 'DialEnd':
                    checkPredictiveCallStatus($event);
                    break;
                case 'QueueMemberPause':
                    setQueueMemberStatus($event);
                    break;
                case 'QueueMemberStatus':
                    setQueueMemberStatus($event);
                    break;
                case 'QueueCallerJoin':
                    queueJoin($event);
                    break;
                case 'QueueCallerLeave':
                    queueLeave($event);
                    break;
                case 'AgentConnect':
                    agentConnect($event);
                    break;
                case 'PeerStatus':
                    peerStatus($event);
                    break;
                case 'QueueMemberAdded':
                    setQueueMemberStatus($event);
                    break;
                case 'DeviceStateChange':
                    setMemberStatus($event);
                    break;
            }
        } catch (Exception $e) {
            Logger::write($e->getMessage() . " at line " . $e->getLine());
        }
    });
    $running = true;
    // Main loop
    while ($running) {

        try {
            $pamiClient->process();
            usleep(1000);
        } catch (Exception $e) {
            Logger::write($e->getMessage() . ' at line ' . __LINE__);
            continue;
        }
    }
    // Close the connection
    $pamiClient->close();
}

function checkPredictiveCallStatus($event)
{

    if (preg_match('/predictive/', $event->getKeys()['destaccountcode'])) {

        if (preg_match('/CONGESTION|NOANSWER|BUSY/', $event->getKeys()['dialstatus'])) {
            $data               = explode('|', $event->getKeys()['destaccountcode']);
            $dialstatus         = $event->getKeys()['dialstatus'];
            $id_phonenumber     = $data[3];
            $last_trying_number = $data[4];
            $dialed_number      = $event->getKeys()['destcalleridnum'];
            //print_r($event);
            //echo "Try call to $dialed_number id $id_phonenumber status $dialstatus last_trying_number $last_trying_number \n";
            $con     = connectDB();
            $sql     = "SELECT * FROM pkg_phonenumber WHERE id = " . $id_phonenumber . " LIMIT 1";
            $command = $con->prepare($sql);
            $command->execute();
            $row = $command->fetchAll(PDO::FETCH_ASSOC);

            $con = connectDB();
            $sql = "UPDATE pkg_phonenumber SET status = 1, id_category = 1, last_trying_number = $last_trying_number + 1  WHERE id = " . $data[3];
            echo $sql . "\n";
            $commad = $con->prepare($sql);
            $commad->execute();

            return;
        }

    }

}

function peerStatus($event)
{
    $con = connectDB();
    $sql = "UPDATE pkg_operator_status SET
            peer_status = '" . $event->getKeys()['peerstatus'] . "'
            WHERE id_user = (
                SELECT id_user FROM pkg_sip WHERE name ='" . substr($event->getKeys()['peer'], 6) . "'
                )";
    //echo $sql . "\n";
    $commad = $con->prepare($sql);
    $commad->execute();

}

function agentConnect($event)
{
    $con = connectDB();
    $sql = "UPDATE pkg_operator_status SET
            last_call_channel = '" . $event->getKeys()['channel'] . "',
            last_call_ringtime = '" . $event->getKeys()['ringtime'] . "',
            in_call = 1
            WHERE id_user = (
                SELECT id_user FROM pkg_sip WHERE name ='" . substr($event->getKeys()['membername'], 6) . "'
                )";
    //echo $sql . "\n";
    $commad = $con->prepare($sql);

    $commad->execute();
}

function queueJoin($event)
{
    $con = connectDB();
    $sql = "INSERT pkg_queue_call_waiting (channel) VALUE
                (
                    '" . $event->getKeys()['channel'] . "'
                )";
    //echo $sql;
    $commad = $con->prepare($sql);
    $commad->execute();

}

function queueLeave($event)
{
    $con = connectDB();
    $sql = "DELETE FROM pkg_queue_call_waiting WHERE channel = '" . $event->getKeys()['channel'] . "'";
    //echo $sql;
    $commad = $con->prepare($sql);
    $commad->execute();

    if (isset($event->getKeys()['membername'])) {
        $sql = "UPDATE pkg_operator_status SET
            in_call = 0
            WHERE id_user = (
                SELECT id_user FROM pkg_sip WHERE name ='" . substr($event->getKeys()['membername'], 6) . "'
                )";
        $commad = $con->prepare($sql);
        //echo $sql . "\n";
        $commad->execute();
    }
}

function connectDB()
{

    $configFile      = '/etc/asterisk/res_config_mysql.conf';
    $array           = parse_ini_file($configFile);
    $array['dbname'] = 'callcenter';
    try {
        $con = new PDO('mysql:host=localhost;dbname=' . $array['dbname'], $array['dbuser'], $array['dbpass']);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        Logger::write($e->getMessage() . ' at line ' . __LINE__);
        echo 'error DB connect';
        return;
    }
    return $con;
}

function setMemberStatus($event)
{
    $con = connectDB();
    $sql = "UPDATE pkg_operator_status SET time_free = '" . time() . "'
            WHERE id_user = (SELECT id_user FROM pkg_sip WHERE
            name = '" . substr($event->getKeys()['device'], 6) . "')";
    //echo $sql . "\n";
    $commad = $con->prepare($sql);
    try {
        $commad->execute();
    } catch (Exception $e) {
        //
    }
}

function setQueueMemberStatus($event)
{
    $con = connectDB();
    $sql = "UPDATE pkg_operator_status SET queue_status = '" . $event->getKeys()['status'] . "',
            queue_paused = '" . $event->getKeys()['paused'] . "' ,
            last_call = '" . $event->getKeys()['lastcall'] . "' ,
            calls_taken = '" . $event->getKeys()['callstaken'] . "',
            in_call = '" . $event->getKeys()['incall'] . "'
            WHERE id_user = (SELECT id_user FROM pkg_sip WHERE
            name = '" . substr($event->getKeys()['membername'], 6) . "')";
    //echo $sql . "\n";
    $commad = $con->prepare($sql);
    try {
        $commad->execute();
    } catch (Exception $e) {
        $sql = "INSERT pkg_operator_status (id_user, queue_status,queue_paused, last_call,calls_taken, in_call ) VALUE
                (
                    (SELECT id_user FROM pkg_sip WHERE name = '" . substr($event->getKeys()['membername'], 6) . "'),
                    " . $event->getKeys()['status'] . ",
                    " . $event->getKeys()['paused'] . ",
                    " . $event->getKeys()['lastcall'] . ",
                    " . $event->getKeys()['callstaken'] . ",
                    " . $event->getKeys()['incall'] . "
                )";
        echo $sql;
        $commad = $con->prepare($sql);
        $commad->execute();
    }
}
