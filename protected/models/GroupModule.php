<?php
/**
 * Model to table "group_module".
 *
 * Columns of table 'group_module':
 * @property integer $id_group.
 * @property integer $id_module.
 *
 * Relations of model:
 * @property GroupUser $idGroup.
 * @property Module $idModule.
 *
 * CallCenter <info@magnussolution.com>
 * 15/04/2013
 */

class GroupModule extends Model
{
    protected $_module = 'groupmodule';

    /**
     * Return the static class of model.
     * @return GroupModule classe estatica da model.
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
        return 'pkg_group_module';
    }

    /**
     * @return name of primary key(s).
     */
    public function primaryKey()
    {
        return array('id_group', 'id_module');
    }

    /**
     * @return array validation of fields of model.
     */
    public function rules()
    {
        return array(
            array('id_group, id_module', 'required'),
            array('id_group, id_module, show_menu', 'numerical', 'integerOnly' => true),
            array('action', 'length', 'max' => 5),
        );
    }

    /**
     * @return array roles of relationship.
     */
    public function relations()
    {
        return array(
            'idGroup'  => array(self::BELONGS_TO, 'GroupUser', 'id_group'),
            'idModule' => array(self::BELONGS_TO, 'Module', 'id_module'),
        );
    }
    public function getGroupModule($id_group, $id_campaign, $isOperator = false)
    {

        $filter = $isOperator && $id_campaign < 1
        ? "(m.id = 4 OR m.module = 'workshift' OR m.module = 'campaign') AND id_group = :id_group"
        : "id_group = :id_group";

        $sql = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module, createShortCut, createQuickStart
				FROM pkg_group_module gm
				INNER JOIN pkg_module m ON gm.id_module = m.id
				WHERE $filter";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":id_group", $id_group, PDO::PARAM_STR);
        return $command->queryAll();
    }
}
