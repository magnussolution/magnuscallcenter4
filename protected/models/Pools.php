<?php
/**
 * Modelo para a tabela "Cdr".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class Pools extends Model
{
	protected $_module = 'pools';
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
		return 'pkg_pools';
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
			array('id_polls_0, id_polls_1, id_polls_2, id_polls_3, id_polls_4, id_polls_5, id_polls_6, id_polls_7, id_polls_8,id_polls_9', 'numerical', 'integerOnly'=>true),
            	array('question, type, answer_0, answer_1, answer_2, answer_3, answer_4, answer_5, answer_6, answer_7, answer_8, answer_9', 'length', 'max'=>200),
     	);
	}
}