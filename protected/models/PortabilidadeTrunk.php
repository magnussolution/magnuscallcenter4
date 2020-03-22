<?php
/**
 * Modelo para a tabela "CampaignPhonebook".
 * MagnusSolution.com <info@magnussolution.com>  
 * 29/10/2012
 */

class PortabilidadeTrunk extends Model
{
	protected $_module = 'portabilidadetrunk';
	/**
	 * Retorna a classe estatica da model.
	 * @return SubModule classe estatica da model.
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
		return 'pkg_codigos_trunks';
	}

	/**
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey()
	{
		return array('id_codigo','id_trunk');
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{
		return array(
			array('id_codigo, id_trunk', 'required'),
			array('id_codigo, id_trunk', 'numerical', 'integerOnly'=>true),
			array('id_codigo, id_trunk', 'safe')
		);
	}
}