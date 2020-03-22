<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class UserWorkShift extends Model
{
	protected $_module = 'workshift';
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
		return 'pkg_user_workshift';
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
			array('id_user, id_workshift', 'numerical', 'integerOnly'=>true)
		);
	}

	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idWorkShift' => array(self::BELONGS_TO, 'WorkShifts', 'id_workshift')
		);
	}	
}