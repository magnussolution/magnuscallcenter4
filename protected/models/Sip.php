<?php
/**
 * Modelo para a tabela "CcSipbuddies".
 * MagnusSolution.com <info@magnussolution.com>
 * 25/06/2012
 */

class Sip extends Model
{
    protected $_module = 'sip';
    /**
     * Retorna a classe estatica da model.
     * @return CcSipbuddies classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return nome da tabela.
     */
    public function tableName()
    {
        return 'pkg_sip';
    }

    /**
     * @return nome da(s) chave(s) primaria(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        return array(
            array('id_user', 'required'),
            array('id_user, calllimit', 'numerical', 'integerOnly' => true),
            array('name, callerid, context, fromuser, fromdomain, md5secret, secret, fullcontact', 'length', 'max' => 80),
            array('regexten, canreinvite, insecure, regserver, directrtpsetup, vmexten, callingpres, musicclass, mohsuggest, allowtransfer', 'length', 'max' => 20),
            array('amaflags, dtmfmode, qualify', 'length', 'max' => 7),
            array('callgroup, pickupgroup, auth, subscribemwi, usereqphone, incominglimit, autoframing, compactheaders', 'length', 'max' => 10),
            array('DEFAULTip, accountcode, ipaddr, maxcallbitrate, rtpkeepalive', 'length', 'max' => 15),
            array('host', 'length', 'max' => 31),
            array('language', 'length', 'max' => 2),
            array('mailbox, nat', 'length', 'max' => 50),
            array('rtptimeout, rtpholdtimeout, cancallforward', 'length', 'max' => 3),
            array('deny, permit, mask', 'length', 'max' => 95),
            array('port', 'length', 'max' => 5),
            array('restrictcid', 'length', 'max' => 1),
            array('type', 'length', 'max' => 6),
            array('disallow, allow, musiconhold, setvar, useragent', 'length', 'max' => 100),
            array('lastms', 'length', 'max' => 11),
            array('defaultuser, cid_number, subscribecontext, outboundproxy', 'length', 'max' => 40),
            array('relaxdtmf', 'length', 'max' => 4),
        );
    }

    public function relations()
    {
        return array(
            'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }
}
