<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class CampaignPoll extends Model
{
	protected $_module = 'campaignpoll';
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
		return 'pkg_campaign_poll';
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
			array('name, id_campaign', 'required'),
            array('ordem_exibicao, id_campaign', 'numerical', 'integerOnly'=>true),
            array('name, description, arq_audio', 'length', 'max'=>100),
            array('option0, option1, option2, option3, option4, option5, option6, option7, option8, option9', 'length', 'max'=>30),

            

		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idCampaign' => array(self::BELONGS_TO, 'CcCampaign', 'id_campaign')
		);
	}
}