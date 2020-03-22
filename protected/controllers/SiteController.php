<?php
/**
 * Actions of module "Pedido".
 *
 * MagnusCallCenter <info@magnussolution.com>
 * 05/06/2013
 */

class SiteController extends BaseController
{
    public function actionIndex()
    {

        $base_language = $this->config['global']['base_language'];
        echo 'window.lang = ' . json_encode($base_language) . ';';
        Yii::app()->session['language'] = $base_language;
        Yii::app()->setLanguage(Yii::app()->session['language']);

        $template = $this->config['global']['template'];
        echo 'window.theme = ' . json_encode($template) . ';';
        Yii::app()->session['theme'] = $template;

        $layout = $this->config['global']['layout'];
        echo 'window.layout = ' . json_encode($layout) . ';';
        Yii::app()->session['layout'] = $layout;

        $wallpaper = $this->config['global']['wallpaper'];
        echo 'window.wallpaper = ' . json_encode($wallpaper) . ';';
        Yii::app()->session['wallpaper'] = $wallpaper;

        echo 'window.colorMenu = ' . json_encode($this->config['global']['color_menu']) . ';';
        echo 'window.moduleExtra = ' . json_encode($this->config['global']['module_extra']) . ';';
    }

}
