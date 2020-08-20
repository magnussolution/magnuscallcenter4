<?php
class UpdateMysqlCommand extends ConsoleCommand
{
    public $config;
    public $success;

    public function run($args)
    {

        $version = $this->config['global']['version'];

        echo $version;

        if ($version == '3.0.0') {

            $sql = "ALTER TABLE  `pkg_campaign` ADD  `call_limit` INT( 11 ) NOT NULL DEFAULT  '0',
                            ADD  `call_next_try` INT( 11 ) NOT NULL DEFAULT  '30',
                            ADD  `predictive` INT( 11 ) NOT NULL DEFAULT  '0';
                    ALTER TABLE `pkg_breaks` CHANGE `start_time` `start_time` TIME NOT NULL DEFAULT '00:00:00';
                    ALTER TABLE `pkg_breaks` CHANGE `stop_time` `stop_time` TIME NOT NULL DEFAULT '00:00:00';
                    ALTER TABLE  `pkg_phonenumber` ADD  `cpf` VARCHAR( 15 ) NOT NULL DEFAULT  '' AFTER  `dni`;
            ";
            $this->executeDB($sql);

            $version = '3.0.1';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.1') {

            $sql = "ALTER TABLE  `pkg_campaign` ADD  `allow_neighborhood` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `allow_city`;
            ALTER TABLE  `pkg_phonenumber` ADD  `neighborhood` VARCHAR( 50 ) NOT NULL DEFAULT  '' AFTER  `city`;
            ALTER TABLE  `pkg_phonenumber` ADD  `try` INT( 1 ) NOT NULL DEFAULT  '0';
            ";
            $this->executeDB($sql);

            $version = '3.0.2';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }
        if ($version == '3.0.2') {

            $sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Tolerancia para mais e para menos para pausas obrigatorias', 'break_tolerance', '3', 'Tolerancia para mais e para menos para pausas obrigatorias', 'global', '1');;
            ";
            $this->executeDB($sql);

            $version = '3.0.3';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.3') {

            $sql = "ALTER TABLE `pkg_logins_campaign` ADD CONSTRAINT `fk_pkg_logins_campaig_pkg_breaks` FOREIGN KEY (`id_breaks`) REFERENCES `pkg_breaks` (`id`);
            ALTER TABLE  `pkg_breaks` ADD  `status` TINYINT( 1 ) NOT NULL DEFAULT  '1'
            ";
            $this->executeDB($sql);

            $version = '3.0.4';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.4') {

            $sql = "UPDATE `pkg_configuration` SET `config_description` = '1 to active, 0 to inactive ' WHERE config_key = 'amd';

            UPDATE `pkg_configuration` SET `status` = '0';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'base_language';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'version';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'admin_email';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'portabilidadeUsername';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'portabilidadePassword';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'operator_next_try';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'updateAll';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'campaign_limit';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'tardanza';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'valor_colectivo';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'valor_hora_zero';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'valor_hora';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'valor_falta';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'notify_url_after_save_number';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'notify_url_category';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'record_call';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'dialcommand_param';
            UPDATE `pkg_configuration` SET `status` = '1' WHERE config_key = 'MixMonitor_format';
            ALTER TABLE `pkg_category` ADD `color` VARCHAR(7) NOT NULL DEFAULT '#ffffff' AFTER `use_in_efetiva`;



            UPDATE `pkg_category` SET `color` = '#FF0000' WHERE id = 0;
            UPDATE `pkg_category` SET `color` = '#339966' WHERE id = 1;
            UPDATE `pkg_category` SET `color` = '#ddb96d' WHERE id = 2;
            UPDATE `pkg_category` SET `color` = '#FF99CC' WHERE id = 3;
            UPDATE `pkg_category` SET `color` = '#ab6b40' WHERE id = 4;
            UPDATE `pkg_category` SET `color` = '#800080' WHERE id = 5;
            UPDATE `pkg_category` SET `color` = '#00FF00' WHERE id = 6;
            UPDATE `pkg_category` SET `color` = '#d9d1a8' WHERE id = 7;
            UPDATE `pkg_category` SET `color` = '#8d5ed5' WHERE id = 8;
            UPDATE `pkg_category` SET `color` = '#993366' WHERE id = 9;
            UPDATE `pkg_category` SET `color` = '#FF0000' WHERE id = 10;
            UPDATE `pkg_category` SET `color` = '#99CCFF' WHERE id = 11;


            ";
            $this->executeDB($sql);

            $sql = "DELETE FROM pkg_category WHERE name = 'Inactivo' AND status = 0;";
            $this->executeDB($sql);

            $sql = "INSERT INTO `pkg_category` VALUES (99,'Inativo','',0,0);
            UPDATE `pkg_category` SET `id` = '0' WHERE `id` = 99;";
            $this->executeDB($sql);

            $sql = "ALTER TABLE  `pkg_category` ADD  `type` TINYINT( 1 ) NOT NULL DEFAULT  '1';";
            $this->executeDB($sql);

            $sql = "UPDATE  `pkg_category` SET  `type` =  '0' WHERE  `pkg_category`.`id` =0;";
            $this->executeDB($sql);

            $version = '3.0.5';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.5') {

            $sql = "ALTER TABLE `pkg_campaign` ADD `open_url` VARCHAR(200) NOT NULL DEFAULT '' AFTER `status`;
            ";
            $this->executeDB($sql);

            $version = '3.0.6';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.6') {

            $sql = "
                ALTER TABLE `pkg_phonenumber` CHANGE `endereco_complementar` `address_complement` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
                ALTER TABLE `pkg_massive_call_phonenumber` CHANGE `endereco_complementar` `address_complement` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
                ALTER TABLE `pkg_campaign` CHANGE `allow_endereco_complementar` `allow_address_complement` INT(11) NOT NULL DEFAULT '0';

                ALTER TABLE `pkg_phonenumber`
                ADD `address_number` INT(10) NULL DEFAULT NULL AFTER `address_complement`,
                ADD `beneficio_especie` VARCHAR(60) NULL DEFAULT NULL,
                ADD `valor_proposta` INT(10) NULL DEFAULT NULL,
                ADD `valor_parcela` INT(10) NULL DEFAULT NULL;

                ALTER TABLE  `pkg_campaign`
                ADD `allow_cpf` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `allow_dni`,
                ADD `allow_address_number` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `allow_address`,
                ADD `allow_beneficio_especie` INT( 11 ) NOT NULL DEFAULT  '0',
                ADD `allow_valor_proposta` INT( 11 ) NOT NULL DEFAULT  '0',
                ADD `allow_valor_parcela` INT( 11 ) NOT NULL DEFAULT  '0';


            ";
            $this->executeDB($sql);

            $version = '3.0.7';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.7') {

            $sql = "
            ALTER TABLE `pkg_campaign` ADD `allow_option_6` VARCHAR(100) NULL DEFAULT NULL AFTER `allow_option_5`;
            ALTER TABLE `pkg_campaign` ADD `allow_option_6_type` VARCHAR(200) NULL DEFAULT NULL AFTER `allow_option_5_type`;
            ALTER TABLE `pkg_phonenumber` ADD `option_6` VARCHAR(80) NULL DEFAULT NULL AFTER `option_5`;

            ALTER TABLE `pkg_campaign` ADD `allow_option_7` VARCHAR(100) NULL DEFAULT NULL AFTER `allow_option_6`;
            ALTER TABLE `pkg_campaign` ADD `allow_option_7_type` VARCHAR(200) NULL DEFAULT NULL AFTER `allow_option_6_type`;
            ALTER TABLE `pkg_phonenumber` ADD `option_7` VARCHAR(80) NULL DEFAULT NULL AFTER `option_6`;

            ALTER TABLE `pkg_campaign` ADD `allow_option_8` VARCHAR(100) NULL DEFAULT NULL AFTER `allow_option_7`;
            ALTER TABLE `pkg_campaign` ADD `allow_option_8_type` VARCHAR(200) NULL DEFAULT NULL AFTER `allow_option_7_type`;
            ALTER TABLE `pkg_phonenumber` ADD `option_8` VARCHAR(80) NULL DEFAULT NULL AFTER `option_7`;


            #add new field
            ALTER TABLE `pkg_phonenumber` ADD `conta_tipo` VARCHAR(20) NULL DEFAULT NULL AFTER `banco`;
            ALTER TABLE `pkg_campaign` ADD `allow_conta_tipo` INT(11) DEFAULT '0' AFTER `allow_banco`;

            ALTER TABLE `pkg_phonenumber` ADD `credit_card_name` VARCHAR(50) NULL DEFAULT NULL;
            ALTER TABLE `pkg_campaign` ADD `allow_credit_card_name` INT(11) DEFAULT '0';

            ALTER TABLE `pkg_phonenumber` ADD `credit_card_type` VARCHAR(30) NULL DEFAULT NULL;
            ALTER TABLE `pkg_campaign` ADD `allow_credit_card_type` INT(11) DEFAULT '0' ;

            ALTER TABLE `pkg_phonenumber` ADD `credit_card_number` VARCHAR(50) NULL DEFAULT NULL ;
            ALTER TABLE `pkg_campaign` ADD `allow_credit_card_number` INT(11) DEFAULT '0' ;

            ALTER TABLE `pkg_phonenumber` ADD `credit_card_code` VARCHAR(10) NULL DEFAULT NULL;
            ALTER TABLE `pkg_campaign` ADD `allow_credit_card_code` INT(11) DEFAULT '0' ;
            ";
            $this->executeDB($sql);

            $version = '3.0.8';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.8') {

            $sql = "ALTER TABLE `pkg_phonenumber` CHANGE `address_number` `address_number` VARCHAR(10) NULL DEFAULT NULL;
                    ALTER TABLE `pkg_phonenumber` CHANGE `name` `name` CHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;
            ";
            $this->executeDB($sql);

            $version = '3.0.9';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.0.9') {

            $sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Abrir URL quando operador receber a chamada', 'notify_url_when_receive_number', '', 'Abrir URL quando operador receber a chamada', 'global', '1');
            ";
            $this->executeDB($sql);

            $version = '3.1.0';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.0') {

            $sql = "ALTER TABLE `pkg_user` ADD `allow_direct_call_campaign` INT(11) NULL DEFAULT NULL AFTER `auto_load_phonenumber`;";
            $this->executeDB($sql);

            $version = '3.1.1';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.1') {

            $sql = "ALTER TABLE `pkg_campaign` ADD `open_url_when_answer_call` VARCHAR(200) NULL DEFAULT NULL AFTER `open_url`;";
            $this->executeDB($sql);

            $sql = "DELETE FROM `pkg_configuration` WHERE config_key = 'notify_url_when_receive_number'";
            $this->executeDB($sql);

            $version = '3.1.2';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.2') {

            $sql = "ALTER TABLE `pkg_massive_call_phonenumber` ADD `res_dtmf` INT(11) NULL DEFAULT NULL AFTER `timeCall`;";
            $this->executeDB($sql);

            $sql = "ALTER TABLE `pkg_massive_call_phonenumber` ADD `queue_status` VARCHAR(50) NULL DEFAULT NULL AFTER `res_dtmf`;";
            $this->executeDB($sql);

            $sql = "ALTER TABLE `pkg_massive_call_phonenumber` ADD `id_user` INT(11) NULL DEFAULT NULL AFTER `id`;";
            $this->executeDB($sql);

            $sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Massive Call Report'')', 'massivecallreport', 'prefixs', 5)";
            $this->executeDB($sql);
            $idServiceModule = Yii::app()->db->lastInsertID;

            $sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '" . $idServiceModule . "', 'r', '1', '1', '1');";
            $this->executeDB($sql);

            $version = '3.1.3';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.3') {

            $sql = "ALTER TABLE `pkg_predictive_gen` ADD `amd` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ringing_time`;";
            $this->executeDB($sql);

            $sql = "INSERT INTO `callcenter`.`pkg_category` (`id`, `name`, `description`, `status`, `use_in_efetiva`, `color`, `type`) VALUES ('-2', 'AMD', NULL, '1', '0', '#ffffff', '1');";
            $this->executeDB($sql);

            $version = '3.1.4';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);

            exec('echo "" >> /etc/asterisk/extensions.ael');
            exec('echo "context magnuscallcenterpredictive {" >> /etc/asterisk/extensions.ael');
            exec('echo "    _X. => {" >> /etc/asterisk/extensions.ael');
            exec('echo "        if (\"\${AMD}\"==\"1\")" >> /etc/asterisk/extensions.ael');
            exec('echo "        {" >> /etc/asterisk/extensions.ael');
            exec('echo "            Answer();" >> /etc/asterisk/extensions.ael');
            exec('echo "            Background(silence/1);" >> /etc/asterisk/extensions.ael');
            exec('echo "            AMD();" >> /etc/asterisk/extensions.ael');
            exec('echo "            Verbose(\${AMDSTATUS});" >> /etc/asterisk/extensions.ael');
            exec('echo "        }" >> /etc/asterisk/extensions.ael');
            exec('echo "        AGI(/var/www/html/callcenter/agi.php);" >> /etc/asterisk/extensions.ael');
            exec('echo "        Hangup();" >> /etc/asterisk/extensions.ael');
            exec('echo "    }" >> /etc/asterisk/extensions.ael');
            exec('echo "}" >> /etc/asterisk/extensions.ael');
            exec('echo "" >> /etc/asterisk/extensions.ael');
        }

        if ($version == '3.1.4') {

            $sql = "ALTER TABLE `pkg_massive_call_phonenumber` ADD `dial_date` DATETIME NULL DEFAULT NULL AFTER `creationdate`;";
            $this->executeDB($sql);

            $version = '3.1.5';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.5') {

            $sql = "ALTER TABLE `pkg_predictive` ADD `id_campaign` INT(11) NULL DEFAULT NULL AFTER `id`;
                    ALTER TABLE `pkg_predictive` ADD `amd` INT(11) NOT NULL DEFAULT '0';";
            $this->executeDB($sql);

            $version = '3.1.6';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.6') {

            $sql = "ALTER TABLE `pkg_user` ADD `force_logout` INT(1) NOT NULL DEFAULT '0' AFTER `id_campaign`;";
            $this->executeDB($sql);

            $version = '3.1.7';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }
        if ($version == '3.1.7') {

            $sql = "UPDATE `pkg_configuration` SET `status` = 1 WHERE config_key = 'amd'";
            $this->executeDB($sql);

            $version = '3.1.8';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '3.1.8') {

            $sql = "INSERT INTO `callcenter`.`pkg_category` (`id`, `name`, `description`, `status`, `use_in_efetiva`, `color`, `type`) VALUES ('-3', 'LEAVE QUEUE', NULL, '0', '0', '#ffffff', '1');";
            $this->executeDB($sql);

            $version = '3.2.0';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        ///////////////////////*****************VERSION 4******************///////////////////

        /*
        crear novo menu chamado equipe.

        cada operador tem que obrigatoriamente fazer parte de 1 equipe.

        Criar grupo de usuario chamado supervisor

        supervisor tambem tem que ter uma equipe.
        quando supervisor logar, so ver dados da equipe dele.

        Campanha, agenda e tronco tambem ficar vinculado a uma equipe

        verificar quando o salvar se os dados sao das mesma equipe

         */
        if ($version == '4.0.0') {

            $sql = "CREATE TABLE `pkg_team` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(20) NOT NULL,
              `description` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->executeDB($sql);

            $sql = "
            INSERT INTO `pkg_team` (`id`, `name`, `description`) VALUES (1, 'Principal', 'Equipe padrão');
            ALTER TABLE `pkg_user` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_user` SET `id_team` = '1';
            ALTER TABLE `pkg_user`  ADD CONSTRAINT `fk_pkg_user_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);
            ";
            $this->executeDB($sql);

            $sql = "INSERT INTO `pkg_group_user` (`id`, `name`, `id_user_type`) VALUES ('3', 'Team', '3')";
            $this->executeDB($sql);

            $sql = "UPDATE `pkg_user_type` SET `name` = 'Team' WHERE `pkg_user_type`.`id` = 3;
            UPDATE `pkg_group_user` SET `name` = 'Team' WHERE `pkg_group_user`.`id` = 3;
            UPDATE `pkg_group_user` SET `id_user_type` = '3' WHERE `id` = 3;

            DELETE FROM pkg_group_module WHERE id_group = 3;
            INSERT INTO pkg_group_module SELECT 3 id_group, `id_module`, `action`, `show_menu`, `createShortCut`, `createQuickStart` FROM `pkg_group_module` WHERE `id_group` = 1;

            UPDATE pkg_module SET text = 't(''Users'')' WHERE id = 7;
            ";
            $this->executeDB($sql);

            $sql = "
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module IN (SELECT id FROM `pkg_module` WHERE id_module = 82);
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE icon_cls = 'paymentmethods');

             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module IN (SELECT id FROM `pkg_module` WHERE id_module = 11);
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = 11;

             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module IN (SELECT id FROM `pkg_module` WHERE id_module = 10);
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = 10;

            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE module = 'team');
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module IN (SELECT id FROM `pkg_module` WHERE id_module = 2);
            UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE icon_cls = 'icon-settings');
             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE module = 'breaks');
             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE module = 'workshift');
             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE module = 'portabilidadecodigos');
             UPDATE pkg_group_module SET action = 'r', show_menu = 0 WHERE id_group = 3 AND id_module = (SELECT id FROM `pkg_module` WHERE module = 'category');
            ";
            $this->executeDB($sql);

            $sql = "
            ALTER TABLE `pkg_provider` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_provider` SET `id_team` = '1';
            ALTER TABLE `pkg_provider`  ADD CONSTRAINT `fk_pkg_provider_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);


            ALTER TABLE `pkg_trunk` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_trunk` SET `id_team` = '1';
            ALTER TABLE `pkg_trunk`  ADD CONSTRAINT `fk_pkg_trunk_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);


            ALTER TABLE `pkg_campaign` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_campaign` SET `id_team` = '1';
            ALTER TABLE `pkg_campaign`  ADD CONSTRAINT `fk_pkg_campaign_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);

            ALTER TABLE `pkg_phonebook` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_phonebook` SET `id_team` = '1';
            ALTER TABLE `pkg_phonebook`  ADD CONSTRAINT `fk_pkg_phonebook_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);

            ALTER TABLE `pkg_phonenumber` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id_phonebook`;
            UPDATE `pkg_phonenumber` SET `id_team` = '1';
            ALTER TABLE `pkg_phonenumber`  ADD CONSTRAINT `fk_pkg_phonenumber_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);


            ALTER TABLE `pkg_breaks` ADD `id_team` INT(11) NULL DEFAULT NULL AFTER `id`;
            UPDATE `pkg_breaks` SET `id_team` = '1';
            ALTER TABLE `pkg_breaks`  ADD CONSTRAINT `fk_pkg_breaks_team` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);


            ";
            $this->executeDB($sql);

            $sql = "
                 ALTER TABLE `pkg_trunk` ADD `AMD_active` INT(11) NOT NULL DEFAULT '0' ;
                 ALTER TABLE `pkg_trunk` ADD `AMD_totalAnalysisTime` INT(11) NOT NULL DEFAULT '5' ;
                 ALTER TABLE `pkg_trunk` ADD `AMD_maximumNumberOfWords` INT(11) NOT NULL DEFAULT '3' ;";
            $this->executeDB($sql);

            $sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Team'')', 'team', 'callback', 1)";
            $this->executeDB($sql);
            $idServiceModule = Yii::app()->db->lastInsertID;

            $sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '" . $idServiceModule . "', 'crud', '1', '1', '1');";
            $this->executeDB($sql);

            $sql = "UPDATE `callcenter`.`pkg_configuration` SET `status` = '1' WHERE config_key = 'channel_spy';";
            $this->executeDB($sql);

            $version = '4.0.1';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.1') {

            $sql = "DELETE FROM pkg_configuration WHERE config_key IN('operator_next_try', 'campaign_limit')";
            $this->executeDB($sql);

            $sql = "UPDATE `pkg_configuration` SET `config_title` = 'Recording calls' WHERE config_key = 'record_call';";
            $this->executeDB($sql);

            $sql = "UPDATE `pkg_configuration` SET `config_title` = 'Dial Command Params' WHERE config_key = 'dialcommand_param';";
            $this->executeDB($sql);

            $sql = "CREATE TABLE IF NOT EXISTS`pkg_team_trunk`(
                    `id_team` int(11) NOT NULL,
                    `id_trunk` int(11 )NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                ALTER TABLE `pkg_team_trunk`
                ADD PRIMARY KEY (`id_team`,`id_trunk`), ADD KEY `fk_pkg_team_pkg_trunk` (`id_trunk`);

                ALTER TABLE `pkg_team_trunk`
                ADD CONSTRAINT `fk_pkg_trunk_pkg_team_trunk` FOREIGN KEY (`id_trunk`) REFERENCES `pkg_trunk` (`id`),
                ADD CONSTRAINT `fk_pkg_team_pkg_team_trunk` FOREIGN KEY (`id_team`) REFERENCES `pkg_team` (`id`);
            ";
            $this->executeDB($sql);

            $version = '4.0.2';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.2') {

            $sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Categoria Automatica', 'automatic_caterogy', '0', 'ID da categoria para ser usada caso o operador não categoriza no tempo estipulado. \n Colocar o tempo na descriçao da propria categoria', 'global', '1');
            ";
            $this->executeDB($sql);

            $version = '4.0.3';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.3') {

            $sql = "ALTER TABLE `pkg_phonenumber` ADD `last_trying_number` VARCHAR(15)  NULL AFTER `credit_card_code`;";
            $this->executeDB($sql);

            $version = '4.0.4';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.4') {

            $sql = " INSERT INTO `pkg_category` VALUES ('50', 'Ligar para o proximo', NULL, '1', '0', '#00FFFF', '1');";
            $this->executeDB($sql);

            $sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Categoria para continuar ligando', 'continue_next_number', '50', 'ID da categoria para ser usada caso o operador queira que o sistema ligue para o proximo número do contato.', 'global', '1');";
            $this->executeDB($sql);

            $version = '4.0.5';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.5') {

            $sql = " ALTER TABLE `pkg_phonenumber` CHANGE `last_trying_number` `last_trying_number` INT(11) NOT NULL DEFAULT '1';";
            $this->executeDB($sql);

            $version = '4.0.6';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.6' || $version == '4.0.7') {

            exec('echo "[magnuscallcenter]
exten => _X.,1,Set(CHANNEL(accountcode)=\${CHANNEL(endpoint)})
    same => n,AGI(/var/www/html/callcenter/agi.php)

exten => _*22X.,1,Goto(spycall,\${EXTEN},1)

" > /etc/asterisk/extensions_magnus.conf');

            exec('echo $\'context magnuscallcenterpredictive {
    _X. => {
        if ("${AMD}" == "1")
        {
            Answer();
            Background(silence/1);
            AMD();
            if("${AMDSTATUS}"=="MACHINE"){
                Verbose("DESLIGAR CHAMADA ${EXTEN} PORQUE AMD DETECTOU ${AMDSTATUS}");
                Hangup();
            }
        }
        AGI(/var/www/html/callcenter/agi.php);
        Hangup();
    }
}

//Somente Espiar  221
//Falar com o espiado discar 222
//Falar com o espiado mas sem escutar a chamada 223
context spycall {
    _*22X. => {
        NoOp(Escuta remota);
        Set(CHANNEL(accountcode)=${CHANNEL(endpoint)});
        Answer();
        Authenticate(3003);
        Wait(1);
        if ("${EXTEN:3:1}"="1")
        {
            ChanSpy(PJSIP/${EXTEN:4},qb);
        }
        else if ("${EXTEN:3:1}"="2")
        {
            ChanSpy(PJSIP/${EXTEN:4},qw);
        }
        else if ("${EXTEN:3:1}"="3")
        {
            ChanSpy(PJSIP/${EXTEN:4},qW);
        }
        Hangup();
    }
}
\' > /etc/asterisk/extensions.ael');

            $version = '4.0.8';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.0.8' || $version == '4.0.9') {

            $sql = "

            ALTER TABLE `pkg_campaign` CHANGE `open_url` `open_url` VARCHAR(500) NOT NULL DEFAULT '';
            ALTER TABLE `pkg_campaign` CHANGE `open_url_when_answer_call` `open_url_when_answer_call` VARCHAR(500)  NOT NULL DEFAULT '';
            ";
            $this->executeDB($sql);

            $version = '4.1.0';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.1.0') {

            exec('echo $\'context magnuscallcenterpredictive {
    _X. => {
        if ("${AMD}" == "1")
        {
            Answer();
            Background(silence/1);
            AMD();
            if("${AMDSTATUS}"=="MACHINE"){
                Verbose("DESLIGAR CHAMADA ${EXTEN} PORQUE AMD DETECTOU ${AMDSTATUS}");
            }
        }
        AGI(/var/www/html/callcenter/agi.php);
        Hangup();
    }
}

//Somente Espiar  221
//Falar com o espiado discar 222
//Falar com o espiado mas sem escutar a chamada 223
context spycall {
    _*22X. => {
        NoOp(Escuta remota);
        Set(CHANNEL(accountcode)=${CHANNEL(endpoint)});
        Answer();
        Authenticate(3003);
        Wait(1);
        if ("${EXTEN:3:1}"="1")
        {
            ChanSpy(PJSIP/${EXTEN:4},qb);
        }
        else if ("${EXTEN:3:1}"="2")
        {
            ChanSpy(PJSIP/${EXTEN:4},qw);
        }
        else if ("${EXTEN:3:1}"="3")
        {
            ChanSpy(PJSIP/${EXTEN:4},qW);
        }
        Hangup();
    }
}
\' > /etc/asterisk/extensions.ael');

            $version = '4.1.1';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.1.1') {

            $sql = "

            ALTER TABLE `pkg_phonenumber` ADD INDEX(`last_trying_number`);
            ALTER TABLE `pkg_phonenumber` ADD INDEX(`status`);
            ALTER TABLE `pkg_phonenumber` ADD INDEX(`id_category`);
            ALTER TABLE `pkg_phonenumber` ADD INDEX(`datebackcall`);

            ";
            $this->executeDB($sql);

            $version = '4.1.2';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.1.2') {

            exec("echo '\n* * * * * root /var/www/html/callcenter/protected/commands/check_service.sh' >> /etc/crontab");

            $version = '4.1.3';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.1.3') {

            $sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Categoria para bloquear números recebidos', 'category_to_block', '', 'ID da categoria para nao aceitar chamadas entrantes. Padrao vazio', 'global', '1');
            ";
            $this->executeDB($sql);

            $version = '4.1.4';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

        if ($version == '4.1.4') {
            $sql = "ALTER TABLE `pkg_campaign` ADD `max_wait_time` INT(11) NOT NULL DEFAULT '30' ;
            ALTER TABLE `pkg_campaign` ADD `max_wait_time_action` VARCHAR(100) NOT NULL DEFAULT '' ;";
            $this->executeDB($sql);

            $version = '4.1.5';
            $sql     = "UPDATE pkg_configuration SET config_value = '" . $version . "' WHERE config_key = 'version' ";
            $this->executeDB($sql);
        }

    }
    //sudo php /Users/macbookpro/Documents/html/CallCenter_4/cron.php  updatemysql
    private function executeDB($sql)
    {
        try {
            Yii::app()->db->createCommand($sql)->execute();
        } catch (Exception $e) {

        }
    }
}
