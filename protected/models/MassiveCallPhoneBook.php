<?php
/**
 * Modelo para a tabela "Call".
 * MagnusSolution.com <info@magnussolution.com> 
 * 19/09/2012
 */

class MassiveCallPhoneBook extends Model
{
	protected $_module = 'massivecallphonebook';
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
		return 'pkg_massive_call_phonebook';
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
			array('id_trunk, status, portabilidadeFixed, portabilidadeMobile', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>30),
			array('description', 'length', 'max'=>100)
		);
	}

	public function relations()
	{
		return array(
			'idTrunk' => array(self::BELONGS_TO, 'Trunk', 'id_trunk')
		);
	}
}