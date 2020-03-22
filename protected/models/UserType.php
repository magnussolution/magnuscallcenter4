<?php
/**
 * Model to table "group_user".
 *
 * Columns of table 'group_user':
 * @property integer $id.
 * @property string $name.
 *
 * Relations of model:
 * @property Module[] $modules.
 * @property GroupModule[] $groupModules.
 * @property User[] $users.
 *
 * CallCenter <info@CallCenter.com>
 * 15/04/2013
 */

class UserType extends Model
{
    protected $_module = 'module';
    /**
     * Return the static class of model.
     * @return GroupUser classe estatica da model.
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
        return 'pkg_user_type';
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
            array('name', 'required'),
            array('name', 'length', 'max' => 100),
        );
    }
}
