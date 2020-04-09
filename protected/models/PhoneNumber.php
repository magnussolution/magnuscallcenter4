<?php
/**
 * Modelo para a tabela "PhoneNumber".
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class PhoneNumber extends Model
{
    protected $_module = 'phonenumber';
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
        return 'pkg_phonenumber';
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
            array('id_team,id_phonebook, id_category, edad, id_user,  status, cita_concreta, last_trying_number', 'numerical', 'integerOnly' => true),
            array('city, dni,credit_card_type', 'length', 'max' => 30),
            array('name,beneficio_number,quantidade_transacoes,inicio_beneficio,beneficio_valor,
                    banco,conta_tipo,agencia,conta,address_complement,telefone_fixo1,
                    telefone_fixo2,telefone_fixo3,telefone_celular1,telefone_celular2,telefone_celular3,
                    telefone_fixo_comercial1,telefone_fixo_comercial2,telefone_fixo_comercial3,
                    parente1,fone_parente1,parente2,fone_parente2,parente3,fone_parente3,
                    vizinho1,telefone_vizinho1,vizinho2,telefone_vizinho2,
                    vizinho3,telefone_vizinho3,beneficio_especie,valor_proposta,valor_parcela', 'length', 'max' => 60),
            array('number, state, country, mobile, number_home, number_office, zip_code', 'length', 'max' => 30),
            array('profesion, datebackcall, email,email2,email3, creationdate,
                    neighborhood,credit_card_name, credit_card_number', 'length', 'max' => 50),
            array('sexo, sessiontime,address_number, credit_card_code', 'length', 'max' => 10),
            array('cpf', 'length', 'max' => 15),
            array('info, company, birth_date, type_user, mobile_2, option_1, option_2, option_3, option_4, option_5,
                 option_6, option_7, option_8', 'length', 'max' => 100),
            array('address', 'length', 'max' => 150),

        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idPhonebook' => array(self::BELONGS_TO, 'PhoneBook', 'id_phonebook'),
            'idCategory'  => array(self::BELONGS_TO, 'Category', 'id_category'),
            'idUser'      => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }

    public function beforeSave()
    {
        if ($this->getIsNewRecord() && $this->id_category == 1) {
            $this->status             = 1;
            $this->last_trying_number = 1;
        }
        return parent::beforeSave();

    }

}
