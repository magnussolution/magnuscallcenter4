<?php 


/**
 * verificar se ja tem um numero para o operador
 * verificar agendamento
 	Se tem algum numero agendado para o operador mostart
	Se tem algum numero agendado para qualquer operador mostrar.
* pegar novo numero		
*/
class NumberDispatcher
{
	private $modelUser;
	private $id_campaign;
	function __construct($id_user,$id_campaign)
	{
		$this->modelUser = User::model()->findByPk($id_user);
		$this->id_campaign = $id_campaign;
	}

	public function getNumber()
	{
		if(($id_phonenumber = $this->modelUser->id_current_phonenumber)!= NULL)
			return [PhoneNumber::model()->findByPk($id_phonenumber)];
		else{
			$modelPhoneNumber = (new ScheduledNumberDispatcher())->getPhonenumber($this->modelUser->id);
	
			if (count($modelPhoneNumber) == 0) {

				$modelPhoneNumber = PhoneNumberView::model()->findAll(array(
					'condition' => 'id_campaign = '.$this->id_campaign .' AND t.status = 1 AND (t.id_category = 1 OR t.id_category = 8)',
					'order' => 'id DESC , RAND( )',
					'limit' => 1
				));
			}
		}
		return $modelPhoneNumber;
	}
}
?>