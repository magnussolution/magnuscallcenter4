<?php
/**
 * Modelo para a tabela "PhoneNumber".
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class MassiveCallPhoneNumber extends Model
{
    protected $_module = 'massivecallphonenumber';
    public $hours;
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
        return 'pkg_massive_call_phonenumber';
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
            array('number', 'required'),
            array('id_massive_call_phonebook, edad, status, res_dtmf, id_user', 'numerical', 'integerOnly' => true),
            array('city, dni', 'length', 'max' => 30),
            array('name,beneficio_number,quantidade_transacoes,inicio_beneficio,beneficio_valor,
                    banco,agencia,conta,address_complement,telefone_fixo1,
                    telefone_fixo2,telefone_fixo3,telefone_celular1,telefone_celular2,telefone_celular3,
                    telefone_fixo_comercial1,telefone_fixo_comercial2,telefone_fixo_comercial3,
                    parente1,fone_parente1,parente2,fone_parente2,parente3,fone_parente3,
                    vizinho1,telefone_vizinho1,vizinho2,telefone_vizinho2,
                    vizinho3,telefone_vizinho3', 'length', 'max' => 60),
            array('number, state, country, mobile, number_home, number_office, zip_code', 'length', 'max' => 30),
            array('profesion, email,email2,email3,address, creationdate,queue_status, dial_date', 'length', 'max' => 50),
            array('sexo', 'length', 'max' => 10),
            array('info, company, birth_date, type_user, mobile_2', 'length', 'max' => 100),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idUser'                 => array(self::BELONGS_TO, 'User', 'id_user'),
            'idMassiveCallPhonebook' => array(self::BELONGS_TO, 'MassiveCallPhoneBook', 'id_massive_call_phonebook'),
        );
    }

    public function getPhoneNumbertoSend($id_campaign, $limit)
    {

        $columns = 't.id, t.name, t.number, t.id_massive_call_phonebook, id_trunk, t.try';
        $join    = 'JOIN pkg_massive_call_campaign_phonebook c
                            ON c.id_massive_call_phonebook =    t.id_massive_call_phonebook ';
        $join .= 'JOIN pkg_massive_call_phonebook d
                            ON c.id_massive_call_phonebook = d.id';
        $condition = 't.status = 1 AND c.id_massive_call_campaign = ' . $id_campaign;

        $sql = "SELECT $columns FROM " . MassiveCallPhoneNumber::tableName() . " t $join WHERE $condition LIMIT $limit";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }
}
