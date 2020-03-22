<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2017
 */
class DashboardController extends BaseController
{

    public function actionIndex()
    {
        $modelUser = User::model()->find('id > 0');
        $this->render('index', array('result' => $modelUser));
    }
}
