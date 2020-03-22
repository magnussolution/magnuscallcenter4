<?php
/**
 * Actions of module "GroupUser".
 *
 * CallCenter <info@magnussolution.com>
 * 15/04/2013
 */

class GroupUserController extends BaseController
{
    public $attributeOrder          = 't.id';
    public $titleReport             = 'GroupUser';
    public $subTitleReport          = 'GroupUser';
    public $nameModelRelated        = 'GroupModule';
    public $extraFieldsRelated      = array('show_menu', 'action', 'id_module', 'createShortCut', 'createQuickStart');
    public $extraValuesOtherRelated = array('idModule' => 'text');
    public $nameFkRelated           = 'id_group';
    public $nameOtherFkRelated      = 'id_module';
    public $extraValues             = array('idUserType' => 'name');

    public $filterByUser = false;

    public function init()
    {
        $this->instanceModel        = new GroupUser;
        $this->abstractModel        = GroupUser::model();
        $this->abstractModelRelated = GroupModule::model();
        parent::init();
    }
}
