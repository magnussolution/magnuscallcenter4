<?php
class ClearSystemCommand extends ConsoleCommand
{

    public function run($args)
    {
        $sql = "TRUNCATE pkg_preditive_gen";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "TRUNCATE pkg_call_online;";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "TRUNCATE TABLE pkg_preditive_refresh_number;";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "OPTIMIZE TABLE pkg_cdr";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "OPTIMIZE TABLE pkg_phonenumber;";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "REPAIR pkg_preditive_gen";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "REPAIR TABLE pkg_preditive_refresh_number;";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "OPTIMIZE TABLE pkg_preditive_gen;";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "OPTIMIZE TABLE pkg_preditive_refresh_number;";
        Yii::app()->db->createCommand($sql)->execute();

    }
}
