<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class MassiveCallCampaign extends Model
{
	protected $_module = 'massivecallcampaign';


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
		return 'pkg_massive_call_campaign';
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
			array('secondusedreal, id_campaign,status, frequency, restrict_phone', 'numerical', 'integerOnly'=>true),
			array('audio, audio_2', 'length', 'max'=>100),
			array('status', 'length', 'max'=>1),
			array('name,forward_number, daily_start_time, daily_stop_time', 'length', 'max'=>50),
			array('description', 'safe')
			
		);
	}
	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign')
		);
	}	
	
}