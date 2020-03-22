<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class Category extends Model
{
    protected $_module = 'category';
    /**
     * Retorna a classe estatica da model.
     * @return Campaign classe estatica da model.
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
        return 'pkg_category';
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
            array('name', 'required'),
            array('status,use_in_efetiva', 'numerical', 'integerOnly' => true),
            array('description', 'length', 'max' => 100),
            array('type,color', 'length', 'max' => 15),
        );
    }

}
