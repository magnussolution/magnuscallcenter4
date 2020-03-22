<?php
/**
 * Modelo para a tabela "Trunk".
 * MagnusSolution.com <info@magnussolution.com>
 * 25/06/2012
 */

class Trunk extends Model
{
    protected $_module = 'trunk';

    /**
     * Retorna a classe estatica da model.
     * @return Trunk classe estatica da model.
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
        return 'pkg_trunk';
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
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('trunkcode, id_provider, allow, providertech, host', 'required'),
            array('AMD_active, AMD_totalAnalysisTime, AMD_maximumNumberOfWords, id_team,id_provider, failover_trunk, secondusedreal, secondusedcarrier, secondusedratecard, inuse, maxuse, status, if_max_use, register', 'numerical', 'integerOnly' => true),
            array('trunkcode, sms_res,nat,fromuser', 'length', 'max' => 50),
            array('trunkprefix, providertech, removeprefix, secret, context, insecure, disallow', 'length', 'max' => 20),
            array('providerip, user, allow, host', 'length', 'max' => 80),
            array('addparameter,fromdomain', 'length', 'max' => 120),
            array('link_sms', 'length', 'max' => 250),
            array('directmedia', 'length', 'max' => 3),
            array('dtmfmode, qualify', 'length', 'max' => 7),
            array('type', 'length', 'max' => 6),
            array('register_string', 'length', 'max' => 300),
            array('register', 'checkregister'),
        );
    }
    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idProvider' => array(self::BELONGS_TO, 'Provider', 'id_provider'),
        );
    }

    public function checkregister($attribute, $params)
    {
        if ($this->register == 1 && (!strlen($this->user) || !strlen($this->secret) || !strlen($this->host))) {
            $this->addError($attribute, Yii::t('yii', 'Para registrar um tronco, vc precisa colocar usuario, senha e HOST'));
        }

    }

    public function beforeSave()
    {
        $this->trunkcode  = preg_replace("/ /", "-", $this->trunkcode);
        $this->allow      = preg_replace("/,0/", "", $this->allow);
        $this->allow      = preg_replace("/0,/", "", $this->allow);
        $this->providerip = $this->providertech != 'sip' && $this->providertech != 'iax2' ? $this->host : $this->trunkcode;

        $this->register_string = $this->register == 1 ? $this->register_string : '';

        $this->failover_trunk = $this->failover_trunk === 0 ? null : $this->failover_trunk;
        return parent::beforeSave();
    }
}
