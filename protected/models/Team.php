<?php
/**
 * Modelo para a tabela "Trunk".
 * MagnusSolution.com <info@magnussolution.com>
 * 25/06/2012
 */

class Team extends Model
{
    protected $_module = 'team';

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
        return 'pkg_team';
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
            array('name', 'required'),
            array('name', 'unique', 'caseSensitive' => 'true'),
            array('name', 'length', 'max' => 20),
            array('description', 'length', 'max' => 300),
        );
    }

}
