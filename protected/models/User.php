<?php
/**
 * Model to table "user".
 *
 * Columns of table 'user':
 * @property integer $id.
 * @property string $name.
 * @property string $password.
 * @property integer $id_group.
 * @property integer $active.
 * @property double $double1.
 *
 * Relations of model:
 * @property GroupUser $idGroup.
 *
 * CallCenter <info@CallCenter.com>
 * 15/04/2013
 */

class User extends Model
{
    protected $_module = 'user';

    /**
     * Return the static class of model.
     * @return User classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return name of table.
     */
    public function tableName()
    {
        return 'pkg_user';
    }

    /**
     * @return name of primary key(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @return array validation of fields of model.
     */
    public function rules()
    {
        return array(
            array('username, password', 'required'),
            array('status, id_campaign, webphone, id_team, id_group, training,allow_direct_call_campaign, force_logout', 'numerical', 'integerOnly' => true),
            array('username, password, name, usuario_tns, cargo, fathername,mothername, hometown,birthday', 'length', 'max' => 50),
            array('direction', 'length', 'max' => 80),
            array('zipcode, state, company', 'length', 'max' => 20),
            array('phone, mobile', 'length', 'max' => 30),
            array('email,startcontract,stoptcontract', 'length', 'max' => 70),
            array('country, city', 'length', 'max' => 40),
            array('dni,cpf,escolaridade,worktime,banck', 'length', 'max' => 25),
            array('estadocivil,salary,agencia,conta', 'length', 'max' => 15),
            array('username', 'unique', 'caseSensitive' => 'false'),
        );
    }

    /**
     * @return array roles of relationship.
     */
    public function relations()
    {
        return array(
            'idTeam'         => array(self::BELONGS_TO, 'Team', 'id_team'),
            'idGroup'        => array(self::BELONGS_TO, 'GroupUser', 'id_group'),
            'idUserCampaign' => array(self::MANY_MANY, 'Campaign', 'pkg_user_campaign(id_user,id_campaign)'),
        );
    }
}
