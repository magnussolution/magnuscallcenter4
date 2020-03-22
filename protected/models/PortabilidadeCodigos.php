<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class PortabilidadeCodigos extends Model
{
	protected $_module = 'portabilidadecodigos';
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
		return 'pkg_codigos';
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
			array('prefix,favorito', 'numerical', 'integerOnly'=>true),
			array('company', 'length', 'max'=>100),
			array('id', 'safe')
		);
	}
}