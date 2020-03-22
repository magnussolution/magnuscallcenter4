<?php
/**
 * Actions of module "UserType".
 *
 * CallCenter <info@CallCenter.com>
 * 15/04/2013
 */

class UserTypeController extends BaseController
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new UserType;
        $this->abstractModel = UserType::model();
        parent::init();
    }
}
