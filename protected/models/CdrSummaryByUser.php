<?php
/**
 * Modelo para a tabela "CcCall".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class CdrSummaryByUser extends Model
{
	protected $_module = 'cdrsumarybyuser';
	public $total_time;
	public $sunsessiontime;
	public $nbcall;
	public $success_calls;
	public $aloc_success_calls;
	public $aloc_all_calls;
	public $sumsessiontime;
	public $sumsuccess_calls;
	public $sumaloc_success_calls;
	public $sumaloc_all_calls;
	public $sumnbcall;
	public $idUserusername;
	public $categoriacion_completa;
	public $sumcategoriacion_completa;


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
		return 'pkg_cdr';
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
            array('sessiontime, nbcall, success_calls, categoriacion_completa, sumcategoriacion_completa, aloc_success_call, aloc_all_calls, sumaloc_all_calls, sumaloc_success_calls', 'length', 'max'=>50),
     	);
	}

}