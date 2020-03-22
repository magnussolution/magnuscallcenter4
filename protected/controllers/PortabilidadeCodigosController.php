<?php
/**
 * Acoes do modulo "Pausas".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class PortabilidadeCodigosController extends BaseController
{
    public $attributeOrder     = 't.favorito DESC, t.prefix ASC, t.company ASC';
    public $nameModelRelated   = 'PortabilidadeTrunk';
    public $nameFkRelated      = 'id_codigo';
    public $nameOtherFkRelated = 'id_trunk';
    public $group              = 'company';

    public function init()
    {
        $this->instanceModel = new PortabilidadeCodigos;
        $this->abstractModel = PortabilidadeCodigos::model();

        $this->abstractModelRelated = PortabilidadeTrunk::model();
        parent::init();
    }
}
