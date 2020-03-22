<?php
/**
 * Modelo para a tabela "Provider".
 * MagnusSolution.com <info@magnussolution.com>
 * 25/06/2012
 */

class Provider extends Model
{
    protected $_module = 'provider';
    /**
     * Retorna a classe estatica da model.
     * @return Provider classe estatica da model.
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
        return 'pkg_provider';
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
            array('provider_name', 'required'),
            array('id_team', 'numerical', 'integerOnly' => true),
            array('description, credit', 'length', 'max' => 500),
        );
    }
}
