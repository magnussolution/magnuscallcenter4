<?php
/**
 * Acoes do modulo "Trunk".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/06/2012
 */

class TeamController extends BaseController
{
    public $attributeOrder = 't.id';

    public $nameModelRelated   = 'TeamTrunk';
    public $nameFkRelated      = 'id_team';
    public $nameOtherFkRelated = 'id_trunk';

    public function init()
    {
        $this->instanceModel        = new Team;
        $this->abstractModel        = Team::model();
        $this->abstractModelRelated = TeamTrunk::model();
        $this->titleReport          = Yii::t('yii', 'Team');
        parent::init();
    }

}
