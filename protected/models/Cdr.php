<?php
/**
 * Modelo para a tabela "Cdr".
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class Cdr extends Model
{
    public $countCall;
    protected $_module = 'cdr';
    /**
     * Retorna a classe estatica da model.
     * @return CcPrefix classe estatica da model.
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
        return 'pkg_cdr';
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
            array('id_user, id_campaign, id_phonebook, id_trunk, sessiontime, id_category, terminatecauseid', 'numerical', 'integerOnly' => true),
            array('sessionid, uniqueid, calledstation, dnid', 'length', 'max' => 50),
        );
    }

    public function relations()
    {
        return array(
            'idUser'      => array(self::BELONGS_TO, 'User', 'id_user'),
            'idCampaign'  => array(self::BELONGS_TO, 'Campaign', 'id_campaign'),
            'idTrunk'     => array(self::BELONGS_TO, 'Trunk', 'id_trunk'),
            'idPhonebook' => array(self::BELONGS_TO, 'PhoneBook', 'id_phonebook'),
            'idCategory'  => array(self::BELONGS_TO, 'Category', 'id_category'),
        );
    }
}
