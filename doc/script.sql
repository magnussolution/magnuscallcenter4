-- MySQL dump 10.14  Distrib 5.5.52-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: callcenter
-- ------------------------------------------------------
-- Server version 5.5.52-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pkg_billing`
--


DROP TABLE IF EXISTS `pkg_billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_online` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `total_price` varchar(15) DEFAULT '0',
  `turno` varchar(1) NOT NULL DEFAULT 'M',
  `efetivas` int(11) NOT NULL DEFAULT '0',
  `total_time` int(11) NOT NULL DEFAULT '0',
  `ratio` varchar(10) NOT NULL DEFAULT '0',
  `ratio_total` varchar(10) NOT NULL DEFAULT '0',
  `incremento` varchar(10) NOT NULL DEFAULT '0',
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_billing_pkg_user_online` (`id_user_online`),
  KEY `fk_pkg_billing_pkg_user` (`id_user`),
  KEY `fk_pkg_billing_pkg_campaign` (`id_campaign`),
  CONSTRAINT `fk_pkg_billing_pkg_campaign` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_billing_pkg_user` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`),
  CONSTRAINT `fk_pkg_billing_pkg_user_online` FOREIGN KEY (`id_user_online`) REFERENCES `pkg_user_online` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_billing`
--

LOCK TABLES `pkg_billing` WRITE;
/*!40000 ALTER TABLE `pkg_billing` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_billing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_breaks`
--

DROP TABLE IF EXISTS `pkg_breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_breaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL DEFAULT '00:00:00',
  `stop_time` time NOT NULL DEFAULT '00:00:00',
  `mandatory` int(11) NOT NULL DEFAULT '0',
  `maximum_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_breaks`
--

LOCK TABLES `pkg_breaks` WRITE;
/*!40000 ALTER TABLE `pkg_breaks` DISABLE KEYS */;
INSERT INTO `pkg_breaks` VALUES (1,'Almoço','14:40:41','00:00:00',1,60,1),(2,'Fumar','14:40:41','00:00:00',0,60,1),(3,'Banheiro','14:40:41','00:00:00',0,60,1);
/*!40000 ALTER TABLE `pkg_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_call_online`
--

DROP TABLE IF EXISTS `pkg_call_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_call_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `canal` varchar(50) DEFAULT NULL,
  `tronco` varchar(50) DEFAULT NULL,
  `ndiscado` varchar(16) DEFAULT '0',
  `codec` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` varchar(16) NOT NULL,
  `duration` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reinvite` varchar(5) NOT NULL,
  `time_start_cat` varchar(20) NOT NULL DEFAULT '0',
  `cant_cat` int(11) NOT NULL DEFAULT '0',
  `media_to_cat` int(11) NOT NULL DEFAULT '0',
  `categorizando` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_operador_online` (`id_user`),
  KEY `fk_pkg_campaign_pkg_operador_online` (`id_campaign`),
  KEY `starttime` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_call_online`
--

LOCK TABLES `pkg_call_online` WRITE;
/*!40000 ALTER TABLE `pkg_call_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_call_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign`
--

DROP TABLE IF EXISTS `pkg_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `status` int(11) NOT NULL DEFAULT '1',
  `open_url` varchar(200) NOT NULL DEFAULT '',
  `open_url_when_answer_call` varchar(200) DEFAULT NULL,
  `daily_start_time` time NOT NULL DEFAULT '10:00:00',
  `daily_stop_time` time NOT NULL DEFAULT '18:00:00',
  `monday` tinyint(4) NOT NULL DEFAULT '1',
  `tuesday` tinyint(4) NOT NULL DEFAULT '1',
  `wednesday` tinyint(4) NOT NULL DEFAULT '1',
  `thursday` tinyint(4) NOT NULL DEFAULT '1',
  `friday` tinyint(4) NOT NULL DEFAULT '1',
  `saturday` tinyint(4) NOT NULL DEFAULT '0',
  `sunday` tinyint(4) NOT NULL DEFAULT '0',
  `allow_email` tinyint(1) NOT NULL DEFAULT '0',
  `allow_city` tinyint(1) NOT NULL DEFAULT '0',
  `allow_neighborhood` int(11) NOT NULL DEFAULT '0',
  `allow_address` tinyint(1) NOT NULL DEFAULT '0',
  `allow_address_number` int(11) NOT NULL DEFAULT '0',
  `allow_state` tinyint(1) NOT NULL DEFAULT '0',
  `allow_country` tinyint(1) NOT NULL DEFAULT '0',
  `allow_dni` tinyint(1) NOT NULL DEFAULT '0',
  `allow_cpf` int(11) NOT NULL DEFAULT '0',
  `allow_mobile` tinyint(1) NOT NULL DEFAULT '0',
  `allow_number_home` tinyint(1) NOT NULL DEFAULT '0',
  `allow_number_office` tinyint(1) NOT NULL DEFAULT '0',
  `allow_zip_code` tinyint(1) NOT NULL DEFAULT '0',
  `allow_company` tinyint(1) NOT NULL DEFAULT '0',
  `allow_birth_date` tinyint(1) NOT NULL DEFAULT '0',
  `allow_type_user` tinyint(1) NOT NULL DEFAULT '0',
  `allow_sexo` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edad` tinyint(1) NOT NULL DEFAULT '0',
  `allow_profesion` tinyint(1) NOT NULL DEFAULT '0',
  `allow_mobile_2` tinyint(1) NOT NULL DEFAULT '0',
  `allow_option_1` varchar(100) NOT NULL,
  `allow_option_2` varchar(100) NOT NULL,
  `allow_option_3` varchar(100) NOT NULL,
  `allow_option_4` varchar(100) NOT NULL,
  `allow_option_5` varchar(100) NOT NULL,
  `allow_option_6` varchar(100) DEFAULT NULL,
  `allow_option_7` varchar(100) DEFAULT NULL,
  `allow_option_8` varchar(100) DEFAULT NULL,
  `allow_option_1_type` varchar(20) NOT NULL DEFAULT '',
  `allow_option_2_type` varchar(20) NOT NULL DEFAULT '',
  `allow_option_3_type` varchar(20) NOT NULL DEFAULT '',
  `allow_option_4_type` varchar(20) NOT NULL DEFAULT '',
  `allow_option_5_type` varchar(20) NOT NULL DEFAULT '',
  `allow_option_6_type` varchar(200) DEFAULT NULL,
  `allow_option_7_type` varchar(200) DEFAULT NULL,
  `allow_option_8_type` varchar(200) DEFAULT NULL,
  `allow_number` tinyint(1) NOT NULL DEFAULT '1',
  `allow_name` tinyint(1) NOT NULL DEFAULT '1',
  `allow_id_phonebook` tinyint(1) NOT NULL DEFAULT '1',
  `allow_status` tinyint(1) NOT NULL DEFAULT '1',
  `allow_sessiontime` tinyint(1) NOT NULL DEFAULT '0',
  `daily_morning_start_time` time NOT NULL DEFAULT '08:00:00',
  `daily_morning_stop_time` time NOT NULL DEFAULT '13:00:00',
  `daily_afternoon_start_time` time NOT NULL DEFAULT '13:00:00',
  `daily_afternoon_stop_time` time NOT NULL DEFAULT '19:00:00',
  `allow_beneficio_number` int(11) NOT NULL DEFAULT '0',
  `allow_quantidade_transacoes` int(11) NOT NULL DEFAULT '0',
  `allow_inicio_beneficio` int(11) NOT NULL DEFAULT '0',
  `allow_beneficio_valor` int(11) NOT NULL DEFAULT '0',
  `allow_banco` int(11) NOT NULL DEFAULT '0',
  `allow_conta_tipo` int(11) DEFAULT '0',
  `allow_agencia` int(11) NOT NULL DEFAULT '0',
  `allow_conta` int(11) NOT NULL DEFAULT '0',
  `allow_address_complement` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo1` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo2` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo3` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_celular1` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_celular2` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_celular3` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo_comercial1` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo_comercial2` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_fixo_comercial3` int(11) NOT NULL DEFAULT '0',
  `allow_parente1` int(11) NOT NULL DEFAULT '0',
  `allow_fone_parente1` int(11) NOT NULL DEFAULT '0',
  `allow_parente2` int(11) NOT NULL DEFAULT '0',
  `allow_fone_parente2` int(11) NOT NULL DEFAULT '0',
  `allow_parente3` int(11) NOT NULL DEFAULT '0',
  `allow_fone_parente3` int(11) NOT NULL DEFAULT '0',
  `allow_vizinho1` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_vizinho1` int(11) NOT NULL DEFAULT '0',
  `allow_vizinho2` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_vizinho2` int(11) NOT NULL DEFAULT '0',
  `allow_vizinho3` int(11) NOT NULL DEFAULT '0',
  `allow_telefone_vizinho3` int(11) NOT NULL DEFAULT '0',
  `allow_email2` int(11) NOT NULL DEFAULT '0',
  `allow_email3` int(11) NOT NULL DEFAULT '0',
  `musiconhold` varchar(128) DEFAULT NULL,
  `strategy` varchar(128) DEFAULT NULL,
  `ringinuse` varchar(3) DEFAULT NULL,
  `timeout` int(11) DEFAULT NULL,
  `retry` int(11) DEFAULT NULL,
  `wrapuptime` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `periodic-announce` varchar(200) DEFAULT NULL,
  `periodic-announce-frequency` int(11) DEFAULT NULL,
  `announce-holdtime` varchar(128) DEFAULT NULL,
  `announce-position` varchar(5) NOT NULL DEFAULT 'yes',
  `announce-frequency` int(11) DEFAULT NULL,
  `joinempty` varchar(128) DEFAULT NULL,
  `leavewhenempty` varchar(128) DEFAULT NULL,
  `eventmemberstatus` varchar(3) NOT NULL DEFAULT 'yes',
  `autopause` varchar(5) NOT NULL DEFAULT 'yes',
  `setqueuevar` varchar(3) NOT NULL DEFAULT 'yes',
  `setqueueentryvar` varchar(3) NOT NULL DEFAULT 'yes',
  `setinterfacevar` varchar(3) DEFAULT 'yes',
  `call_limit` int(11) NOT NULL DEFAULT '0',
  `call_next_try` int(11) NOT NULL DEFAULT '30',
  `predictive` int(11) NOT NULL DEFAULT '0',
  `allow_beneficio_especie` int(11) NOT NULL DEFAULT '0',
  `allow_valor_proposta` int(11) NOT NULL DEFAULT '0',
  `allow_valor_parcela` int(11) NOT NULL DEFAULT '0',
  `allow_credit_card_name` int(11) DEFAULT '0',
  `allow_credit_card_type` int(11) DEFAULT '0',
  `allow_credit_card_number` int(11) DEFAULT '0',
  `allow_credit_card_code` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign`
--

LOCK TABLES `pkg_campaign` WRITE;
/*!40000 ALTER TABLE `pkg_campaign` DISABLE KEYS */;
INSERT INTO `pkg_campaign` VALUES (-2,'Treinamento','2017-08-07 18:56:05','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,1,'',NULL,'10:00:00','18:00:00',1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'','','','','',NULL,NULL,NULL,'','','','','',NULL,NULL,NULL,1,1,1,1,0,'08:00:00','13:00:00','13:00:00','19:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'yes',NULL,NULL,NULL,'yes','yes','yes','yes','yes',0,30,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `pkg_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_phonebook`
--

DROP TABLE IF EXISTS `pkg_campaign_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_phonebook` (
  `id_campaign` int(11) NOT NULL,
  `id_phonebook` int(11) NOT NULL,
  PRIMARY KEY (`id_campaign`,`id_phonebook`),
  KEY `fk_pkg_phonenumber_pkg_campaign_phonebook` (`id_phonebook`),
  CONSTRAINT `fk_pkg_campaign_pkg_campaign_phonebook` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_phonebook_pkg_campaign_phonebook` FOREIGN KEY (`id_phonebook`) REFERENCES `pkg_phonebook` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_phonebook`
--

LOCK TABLES `pkg_campaign_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_category`
--

DROP TABLE IF EXISTS `pkg_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `status` int(1) NOT NULL DEFAULT '1',
  `use_in_efetiva` int(1) NOT NULL DEFAULT '0',
  `color` varchar(7) NOT NULL DEFAULT '#ffffff',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_category`
--

LOCK TABLES `pkg_category` WRITE;
/*!40000 ALTER TABLE `pkg_category` DISABLE KEYS */;
INSERT INTO `pkg_category` VALUES (-3,'LEAVE QUEUE',NULL,0,0,'#ffffff',1),(-2,'AMD',NULL,1,0,'#ffffff',1),(0,'Inativo','',0,0,'#FF0000',0),(1,'Ativo','',1,0,'#339966',1),(2,'Agendar','',1,0,'#ddb96d',1),(3,'Chamada incompleta','',1,0,'#FF99CC',1),(4,'Não responde','',1,0,'#ab6b40',1),(5,'Número invalido','',0,0,'#800080',1),(6,'Rechazo','',1,0,'#00FF00',1),(7,'Caixa postal','',1,0,'#d9d1a8',1),(8,'Base externa','',1,0,'#8d5ed5',1),(10,'Sem filtro','',1,0,'#FF0000',1),(11,'Efetiva','',1,1,'#99CCFF',1),(12,'Inativo','',1,0,'#ffffff',1),(17,'Erro de Base','',0,0,'#ffffff',1),(18,'Ocupado','Ocupado',1,0,'#ffffff',1),(21,'Numero Invalido','',0,0,'#ffffff',1),(23,'Entrevista Realizada','Entrevista ja respondida',1,0,'#ffffff',1),(25,'Incorreto','Incorreto',1,0,'#ffffff',1);
/*!40000 ALTER TABLE `pkg_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_cdr`
--

DROP TABLE IF EXISTS `pkg_cdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_cdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `id_phonebook` int(11) NOT NULL,
  `id_phonenumber` int(11) DEFAULT NULL,
  `id_trunk` int(11) DEFAULT NULL,
  `sessionid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessiontime` int(11) DEFAULT NULL,
  `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id_category` int(11) DEFAULT NULL,
  `real_sessiontime` int(11) DEFAULT NULL,
  `dnid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `terminatecauseid` int(1) DEFAULT '1',
  `pay` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `starttime` (`starttime`),
  KEY `calledstation` (`calledstation`),
  KEY `terminatecauseid` (`terminatecauseid`),
  KEY `uniqueid` (`uniqueid`),
  KEY `id_campaign` (`id_campaign`),
  KEY `id_trunk` (`id_trunk`),
  KEY `id_user` (`id_user`),
  KEY `id_phonebook` (`id_phonebook`),
  KEY `id_category` (`id_category`),
  CONSTRAINT `fk_pkg_campaign_pkg_cdr` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_category_pkg_cdr` FOREIGN KEY (`id_category`) REFERENCES `pkg_category` (`id`),
  CONSTRAINT `fk_pkg_phonebook_pkg_cdr` FOREIGN KEY (`id_phonebook`) REFERENCES `pkg_phonebook` (`id`),
  CONSTRAINT `fk_pkg_trunk_pkg_cdr` FOREIGN KEY (`id_trunk`) REFERENCES `pkg_trunk` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_cdr` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_cdr`
--

LOCK TABLES `pkg_cdr` WRITE;
/*!40000 ALTER TABLE `pkg_cdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_cdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_codigos`
--

DROP TABLE IF EXISTS `pkg_codigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_codigos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` int(20) NOT NULL,
  `company` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `favorito` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_codigos`
--

LOCK TABLES `pkg_codigos` WRITE;
/*!40000 ALTER TABLE `pkg_codigos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_codigos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_codigos_trunks`
--

DROP TABLE IF EXISTS `pkg_codigos_trunks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_codigos_trunks` (
  `id_codigo` int(11) NOT NULL,
  `id_trunk` int(11) NOT NULL,
  PRIMARY KEY (`id_codigo`,`id_trunk`),
  KEY `fk_pkg_codigos_pkg_trunk` (`id_trunk`),
  CONSTRAINT `fk_pkg_codigos_pkg_trunk` FOREIGN KEY (`id_codigo`) REFERENCES `pkg_codigos` (`id`),
  CONSTRAINT `fk_pkg_trunk_pkg_codigos` FOREIGN KEY (`id_trunk`) REFERENCES `pkg_trunk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_codigos_trunks`
--

LOCK TABLES `pkg_codigos_trunks` WRITE;
/*!40000 ALTER TABLE `pkg_codigos_trunks` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_codigos_trunks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_configuration`
--

DROP TABLE IF EXISTS `pkg_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_key` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_value` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_description` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_group_title` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_configuration`
--

LOCK TABLES `pkg_configuration` WRITE;
/*!40000 ALTER TABLE `pkg_configuration` DISABLE KEYS */;
INSERT INTO `pkg_configuration` VALUES (1,'Language','base_language','pt_BR','Allow \n en English \nes Espanhol \npt_BR Portugues','global',1),(2,'Version','version','4.0.0','MagnusCallcenter Version','global',1),(3,'Licence','licence','free','MagnusCallcenter Licence','global',0),(4,'Server IP','ip_servers','','Ip do servidor MagnusCallcenter','global',0),(5,'Template','template','green-neptune','Allowed values:\ngreen, gray, blue, yellow, red, orange, purple','global',0),(6,'Country','base_country','BRL','Allowed values\nUSA United States,\nBRL Brasil,\nARG Argentina,\nNLD Netherlands,\nESP Spanish','global',0),(7,'Desktop layout','layout','0','Active Desktop template, only to FULL version\n1 - Enable (Only to full version)\n0 - Disable','global',0),(8,'Wallpaper','wallpaper','Azul','Default Wallpaper, only FULL version.','global',0),(9,'Admin Email','admin_email','info@magnussolution.com','Email for receive notifications','global',1),(10,'Send email copy to admin','admin_received_email','1','Send copy for admin email','global',0),(11,'Archive cdr','archive_call_prior_x_month','4','Calls to file before 10 months.','global',0),(12,'Decimal precision','decimal_precision','0000','Decimal precision.','global',0),(13,'Portabilidade Usuário','portabilidadeUsername','0','Usuário da portabilidade para consulta via WebService','global',1),(14,'Portabilidade Senha','portabilidadePassword','0','Senha da portabilidade para consulta via WebService','global',1),(15,'Tempo para gerar chamada para o operador após a 1º tentativa','operator_next_try','30','Tempo para gerar chamada para o operador após a 1º tentativa','global',1),(16,'Botao atualizar em LOTE','updateAll','0','Ativar o botao Atualizar em Lote nos menus','global',1),(17,'Limite de chamadas','campaign_limit','2','Limite de chamadas realizadas por cada operador disponível','global',1),(18,'Desconto por atraso','tardanza','15','Valor a descontar do operador por atraso','global',1),(19,'Preco Onibus','valor_colectivo','10.2','Valor do Onibus','global',1),(20,'Preco por hora, ratio < 0,5','valor_hora_zero','11','Preco por hora em campanhas con ratio menor que zero','global',1),(21,'Preco por hora','valor_hora','16','Preco por hora','global',1),(22,'Preco por falta','valor_falta','50','Preco por falta','global',1),(23,'Url para notificar apos salvar numero','notify_url_after_save_number',NULL,'Url para notificar apos salvar numero','global',1),(24,'Categorias para notificar','notify_url_category',NULL,'Categorias para notificar','global',1),(25,'AGI 1 - Answer Call','answer_call','0','If enabled the MagnusCallcenter answers the call that starts.\nDefault: 0','agi-conf1',0),(26,'AGI 1 - User DNID','use_dnid','1','If the client does not need active schedule again the number he wish to call after entering the PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable','agi-conf1',0),(27,'AGI 1 - Recording calls','record_call','0','Enables recording of all customers.\nCAUTION, THIS OPTION REQUIRES A LOT OF SERVER PERFORMANCE. SO YOU CAN RECORD CUSTOMER SPECIFIC.\n\n0: Disable\n1: Enable','agi-conf1',1),(28,'AGI 1 - International prefixes','international_prefixes','00,09','List the prefixes you want stripped off if the call number','agi-conf1',0),(29,'AGI 1 - FailOver LCR/LCD','failover_lc_prefix','1','If anable and have two hidden tariff in de plan, MagnusCallcenter gonna get the cheaper','agi-conf1',0),(30,'AGI 1 - Dial Command Params','dialcommand_param',',60,L(%timeout%:61000:30000)','More info: http://voip-info.org/wiki-Asterisk+cmd+dial','agi-conf1',1),(31,'AGI 1 - Internal Call, Dial Command Params','dialcommand_param_sipiax_friend',',60,TtiL(3600000:61000:30000)','Dial paramater for call between users.\n\nby default (3600000  =  1HOUR MAX CALL).','agi-conf1',0),(32,'AGI 1 - Failover Retry Limit','failover_recursive_limit','5','Define how many time we want to authorize the research of the failover trunk when a call fails','agi-conf1',0),(33,'AGI 1 - Outbound Call','switchdialcommand','0','Define the order to make the outbound call<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Both should work exactly the same but i experimented one case when gateway was supporting number@trunk, So in case of trouble, try it out.','agi-conf1',0),(34,'AGI 1 - Say Balance After Call','say_balance_after_call','0','Play the balance to the user after the call\n\n0 - No\n1 - Yes','agi-conf1',0),(35,'SIP Account for spy call','channel_spy','0','SIP Account for spy call','global',0),(36,'Menu color','color_menu','White','Menu color, Black or White','global',0),(37,'Charge Sip Call','charge_sip_call','0','Charge sip call between clients','global',0),(38,'URL to extra module','module_extra','index.php/extra/read','Url to extra module, default: index.php/extra/read','global',0),(39,'intra/inter Billing','intra-inter','0','Enable Intra-Inter Billing. If you enable this option, and you have another plan with the same name + Intra on the name Mbilling use the new plan to intra call','global',0),(40,'Asterisk','asterisk_version','11','Set your Asterisk Version instaled. Default 1.8','global',0),(41,'Tts URL','tts_url','https://translate.google.com/translate_tts?ie=UTF-8&q=$name&tl=pt-BR&total=1&idx=0&textlen=25&client=t&tk=55786|34299.','Set here the URL to use in Massive Call. Use variable $name in the string field','global',0),(42,'MixMonitor Format','MixMonitor_format','gsm','see the availables extensions in http://www.voip-info.org/wiki/view/MixMonitor','global',1),(43,'AGI 1 - Use amd macro','amd','0','1 to active, 0 to inactive ','agi-conf1',1),(44,'Callback add Prefix','callback_add_prefix','','Add prefix in callerd in callback call','global',0),(45,'Callback remove Prefix','callback_remove_prefix','','Remove prefix in callerd in callback call','global',0),(46,'Answer Callback','answer_callback','0','Answer callback and play audio','global',0),(47,'Diretorio dos audios','record_patch','/var/spool/asterisk/monitor','Diretorio onde estão os audios','global',0),(48,'Tolerancia para mais e para menos para pausas obrigatorias','break_tolerance','3','Tolerancia para mais e para menos para pausas obrigatorias','global',0);
/*!40000 ALTER TABLE `pkg_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_did_destination`
--

DROP TABLE IF EXISTS `pkg_did_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_did_destination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ivr` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `did` varchar(16) NOT NULL,
  `destination` varchar(120) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activated` int(11) NOT NULL DEFAULT '1',
  `voip_call` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_did_destination`
--

LOCK TABLES `pkg_did_destination` WRITE;
/*!40000 ALTER TABLE `pkg_did_destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_did_destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_group_module`
--

DROP TABLE IF EXISTS `pkg_group_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_group_module` (
  `id_group` int(11) NOT NULL,
  `id_module` int(11) NOT NULL,
  `action` varchar(45) DEFAULT NULL,
  `show_menu` tinyint(1) NOT NULL,
  `createShortCut` tinyint(1) NOT NULL DEFAULT '0',
  `createQuickStart` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_group`,`id_module`),
  KEY `fk_pkg_module_pkg_group_module` (`id_module`),
  CONSTRAINT `fk_pkg_group_user_pkg_group_module` FOREIGN KEY (`id_group`) REFERENCES `pkg_group_user` (`id`),
  CONSTRAINT `fk_pkg_module_pkg_group_module` FOREIGN KEY (`id_module`) REFERENCES `pkg_module` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_group_module`
--

LOCK TABLES `pkg_group_module` WRITE;
/*!40000 ALTER TABLE `pkg_group_module` DISABLE KEYS */;
INSERT INTO `pkg_group_module` VALUES (1,1,'crud',1,0,0),(1,2,'rcud',1,0,0),(1,3,'crud',1,0,0),(1,4,'crud',1,0,0),(1,5,'crud',1,0,0),(1,6,'r',0,0,0),(1,7,'crud',1,1,1),(1,8,'crud',1,0,0),(1,9,'crud',0,0,0),(1,10,'crud',1,0,0),(1,11,'crud',1,0,0),(1,65,'cru',1,0,0),(1,66,'crud',1,1,0),(1,67,'crud',1,0,0),(1,69,'crud',1,1,0),(1,71,'crud',1,1,0),(1,72,'crud',1,1,0),(1,74,'crud',1,0,0),(1,75,'crud',1,0,0),(1,76,'crud',1,1,0),(1,77,'rd',1,1,0),(1,79,'crud',1,0,0),(1,80,'rd',1,1,1),(1,85,'r',1,1,1),(1,86,'r',1,0,0),(1,87,'crud',1,0,0),(1,88,'crud',1,0,0),(1,90,'crud',1,0,0),(1,91,'crud',1,0,0),(1,92,'crud',1,0,0),(1,93,'crud',1,0,0),(1,94,'crud',1,0,0),(1,95,'r',1,1,1),(2,4,'r',1,1,0),(2,69,'r',0,1,0),(2,71,'r',0,1,0),(2,72,'ruc',1,1,1),(2,79,'r',0,1,0),(2,87,'r',0,0,0),(3,5,'r',1,1,1),(3,7,'r',0,0,0),(3,74,'r',1,1,1);
/*!40000 ALTER TABLE `pkg_group_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_group_user`
--

DROP TABLE IF EXISTS `pkg_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_group_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_user_type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pkg_user_type_pkg_group_user` (`id_user_type`),
  CONSTRAINT `pkg_user_type_pkg_group_user` FOREIGN KEY (`id_user_type`) REFERENCES `pkg_user_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_group_user`
--

LOCK TABLES `pkg_group_user` WRITE;
/*!40000 ALTER TABLE `pkg_group_user` DISABLE KEYS */;
INSERT INTO `pkg_group_user` VALUES (1,'Administrator',1),(2,'Operador',2);
/*!40000 ALTER TABLE `pkg_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_ivr`
--

DROP TABLE IF EXISTS `pkg_ivr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_ivr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `TimeOfDay_monFri` varchar(100) NOT NULL DEFAULT '',
  `TimeOfDay_sat` varchar(100) NOT NULL DEFAULT '',
  `TimeOfDay_sun` varchar(100) NOT NULL DEFAULT '',
  `option_0` varchar(50) DEFAULT NULL,
  `option_1` varchar(50) DEFAULT NULL,
  `option_2` varchar(50) DEFAULT NULL,
  `option_3` varchar(50) DEFAULT NULL,
  `option_4` varchar(50) DEFAULT NULL,
  `option_5` varchar(50) DEFAULT NULL,
  `option_6` varchar(50) DEFAULT NULL,
  `option_7` varchar(50) DEFAULT NULL,
  `option_8` varchar(50) DEFAULT NULL,
  `option_9` varchar(50) DEFAULT NULL,
  `option_10` varchar(50) DEFAULT NULL,
  `workaudio` varchar(100) DEFAULT NULL,
  `noworkaudio` varchar(100) DEFAULT NULL,
  `option_out_0` varchar(50) DEFAULT NULL,
  `option_out_1` varchar(50) DEFAULT NULL,
  `option_out_2` varchar(50) DEFAULT NULL,
  `option_out_3` varchar(50) DEFAULT NULL,
  `option_out_4` varchar(50) DEFAULT NULL,
  `option_out_5` varchar(50) DEFAULT NULL,
  `option_out_6` varchar(50) DEFAULT NULL,
  `option_out_7` varchar(50) DEFAULT NULL,
  `option_out_8` varchar(50) DEFAULT NULL,
  `option_out_9` varchar(50) DEFAULT NULL,
  `option_out_10` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_ivr`
--

LOCK TABLES `pkg_ivr` WRITE;
/*!40000 ALTER TABLE `pkg_ivr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_ivr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_log`
--

DROP TABLE IF EXISTS `pkg_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_log_actions` int(11) DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `username` varchar(50) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_log_actions_pkg_log` (`id_log_actions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_log`
--

LOCK TABLES `pkg_log` WRITE;
/*!40000 ALTER TABLE `pkg_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_log_actions`
--

DROP TABLE IF EXISTS `pkg_log_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_log_actions` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_log_actions`
--

LOCK TABLES `pkg_log_actions` WRITE;
/*!40000 ALTER TABLE `pkg_log_actions` DISABLE KEYS */;
INSERT INTO `pkg_log_actions` VALUES (1,'Login'),(2,'Edit'),(3,'Delete'),(4,'New'),(5,'Import'),(6,'UpdateAll'),(7,'Export'),(8,'Logout');
/*!40000 ALTER TABLE `pkg_log_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_logins`
--

DROP TABLE IF EXISTS `pkg_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_time` int(11) DEFAULT NULL,
  `turno` varchar(1) NOT NULL DEFAULT 'M',
  `type` varchar(10) DEFAULT NULL,
  `pause_type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_user_online` (`id_user`),
  KEY `fk_pkg_campaign_pkg_user_online` (`id_campaign`),
  CONSTRAINT `fk_pkg_campaign_billing` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_campaign_pkg_logins` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_operador_billing` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`),
  CONSTRAINT `fk_pkg_operador_pkg_logins` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_logins`
--

LOCK TABLES `pkg_logins` WRITE;
/*!40000 ALTER TABLE `pkg_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_logins_campaign`
--

DROP TABLE IF EXISTS `pkg_logins_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_logins_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_breaks` int(11) DEFAULT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_time` int(11) NOT NULL DEFAULT '0',
  `turno` varchar(1) NOT NULL,
  `login_type` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_logins_campaig_pkg_breaks` (`id_breaks`),
  CONSTRAINT `fk_pkg_logins_campaig_pkg_breaks` FOREIGN KEY (`id_breaks`) REFERENCES `pkg_breaks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_logins_campaign`
--

LOCK TABLES `pkg_logins_campaign` WRITE;
/*!40000 ALTER TABLE `pkg_logins_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_logins_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_massive_call_campaign`
--

DROP TABLE IF EXISTS `pkg_massive_call_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_massive_call_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `daily_start_time` time NOT NULL DEFAULT '09:00:00',
  `daily_stop_time` time NOT NULL DEFAULT '18:00:00',
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `secondusedreal` int(11) DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `frequency` int(11) NOT NULL DEFAULT '0',
  `forward_number` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `audio` varchar(100) DEFAULT NULL,
  `audio_2` varchar(100) DEFAULT NULL,
  `restrict_phone` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_massive_call_campaign`
--

LOCK TABLES `pkg_massive_call_campaign` WRITE;
/*!40000 ALTER TABLE `pkg_massive_call_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_massive_call_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_massive_call_campaign_phonebook`
--

DROP TABLE IF EXISTS `pkg_massive_call_campaign_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_massive_call_campaign_phonebook` (
  `id_massive_call_campaign` int(11) NOT NULL,
  `id_massive_call_phonebook` int(11) NOT NULL,
  PRIMARY KEY (`id_massive_call_campaign`,`id_massive_call_phonebook`),
  KEY `fk_pkg_phonenumber_pkg_campaign_phonebook` (`id_massive_call_phonebook`),
  CONSTRAINT `fk_pkg_massive_callcampaign_pkg_campaign_phonebook` FOREIGN KEY (`id_massive_call_campaign`) REFERENCES `pkg_massive_call_campaign` (`id`),
  CONSTRAINT `fk_pkg_massive_callphonebook_pkg_campaign_phonebook` FOREIGN KEY (`id_massive_call_phonebook`) REFERENCES `pkg_massive_call_phonebook` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_massive_call_campaign_phonebook`
--

LOCK TABLES `pkg_massive_call_campaign_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_massive_call_campaign_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_massive_call_campaign_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_massive_call_phonebook`
--

DROP TABLE IF EXISTS `pkg_massive_call_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_massive_call_phonebook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trunk` int(11) NOT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `portabilidadeFixed` tinyint(1) NOT NULL DEFAULT '0',
  `portabilidadeMobile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_trunk_pkg_phonebook` (`id_trunk`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_massive_call_phonebook`
--

LOCK TABLES `pkg_massive_call_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_massive_call_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_massive_call_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_massive_call_phonenumber`
--

DROP TABLE IF EXISTS `pkg_massive_call_phonenumber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_massive_call_phonenumber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_massive_call_phonebook` int(11) NOT NULL,
  `number` bigint(30) NOT NULL,
  `status` int(11) DEFAULT '1',
  `try` tinyint(1) NOT NULL DEFAULT '0',
  `name` char(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(60) NOT NULL DEFAULT '',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dial_date` datetime DEFAULT NULL,
  `info` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `address` varchar(150) NOT NULL DEFAULT '',
  `state` varchar(40) NOT NULL DEFAULT '',
  `country` varchar(40) NOT NULL DEFAULT '',
  `dni` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `number_home` varchar(20) DEFAULT NULL,
  `number_office` varchar(20) DEFAULT NULL,
  `zip_code` varchar(20) NOT NULL DEFAULT '',
  `company` varchar(50) NOT NULL DEFAULT '',
  `birth_date` varchar(20) DEFAULT NULL,
  `type_user` varchar(50) NOT NULL DEFAULT '',
  `sexo` varchar(10) NOT NULL DEFAULT '',
  `edad` varchar(11) DEFAULT NULL,
  `profesion` varchar(50) NOT NULL DEFAULT '',
  `mobile_2` varchar(20) DEFAULT NULL,
  `beneficio_number` varchar(50) DEFAULT NULL,
  `quantidade_transacoes` varchar(50) DEFAULT NULL,
  `inicio_beneficio` varchar(50) DEFAULT NULL,
  `beneficio_valor` varchar(50) DEFAULT NULL,
  `banco` varchar(50) DEFAULT NULL,
  `agencia` varchar(50) DEFAULT NULL,
  `conta` varchar(50) DEFAULT NULL,
  `address_complement` varchar(50) DEFAULT NULL,
  `telefone_fixo1` varchar(50) DEFAULT NULL,
  `telefone_fixo2` varchar(50) DEFAULT NULL,
  `telefone_fixo3` varchar(50) DEFAULT NULL,
  `telefone_celular1` varchar(50) DEFAULT NULL,
  `telefone_celular2` varchar(50) DEFAULT NULL,
  `telefone_celular3` varchar(50) DEFAULT NULL,
  `telefone_fixo_comercial1` varchar(50) DEFAULT NULL,
  `telefone_fixo_comercial2` varchar(60) DEFAULT NULL,
  `telefone_fixo_comercial3` varchar(60) DEFAULT NULL,
  `parente1` varchar(100) DEFAULT NULL,
  `fone_parente1` varchar(60) DEFAULT NULL,
  `parente2` varchar(60) DEFAULT NULL,
  `fone_parente2` varchar(60) DEFAULT NULL,
  `parente3` varchar(60) DEFAULT NULL,
  `fone_parente3` varchar(60) DEFAULT NULL,
  `vizinho1` varchar(100) DEFAULT NULL,
  `telefone_vizinho1` varchar(60) DEFAULT NULL,
  `vizinho2` varchar(100) DEFAULT NULL,
  `telefone_vizinho2` varchar(60) DEFAULT NULL,
  `vizinho3` varchar(100) DEFAULT NULL,
  `telefone_vizinho3` varchar(60) DEFAULT NULL,
  `email2` varchar(100) DEFAULT NULL,
  `email3` varchar(100) DEFAULT NULL,
  `timeCall` int(5) NOT NULL DEFAULT '0',
  `res_dtmf` int(11) DEFAULT NULL,
  `queue_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_massive_call_phonebook_pkg_phonenumber` (`id_massive_call_phonebook`),
  KEY `number` (`number`),
  CONSTRAINT `fk_pkg_massive_call_phonebook_pkg_phonenumber` FOREIGN KEY (`id_massive_call_phonebook`) REFERENCES `pkg_massive_call_phonebook` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_massive_call_phonenumber`
--

LOCK TABLES `pkg_massive_call_phonenumber` WRITE;
/*!40000 ALTER TABLE `pkg_massive_call_phonenumber` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_massive_call_phonenumber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_module`
--

DROP TABLE IF EXISTS `pkg_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `icon_cls` varchar(100) DEFAULT NULL,
  `id_module` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_module_module` (`id_module`),
  CONSTRAINT `fk_pkg_module_pkg_module` FOREIGN KEY (`id_module`) REFERENCES `pkg_module` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_module`
--

LOCK TABLES `pkg_module` WRITE;
/*!40000 ALTER TABLE `pkg_module` DISABLE KEYS */;
INSERT INTO `pkg_module` VALUES (1,'t(\'Users\')',NULL,'users',NULL),(2,'t(\'Administration\')',NULL,'icon-settings',NULL),(3,'t(\'Trocales\')',NULL,'routes',NULL),(4,'t(\'Campaign\')',NULL,'campaignpollinfo',NULL),(5,'t(\'Informes\')',NULL,'report',NULL),(6,'t(\'Module\')','module','module',2),(7,'t(\'Operadores\')','user','callerid',1),(8,'t(\'GroupUser\')','groupuser','modules',2),(9,'t(\'GroupModule\')','groupmodule','groupmodule',2),(10,'t(\'DID\')',NULL,'did',NULL),(11,'t(\'MassiveCall\')',NULL,'refillprovider',NULL),(65,'t(\'Config\')','configuration','config',2),(66,'t(\'Trunk\')','trunk','trunk',3),(67,'t(\'Provider\')','provider','provider',3),(69,'t(\'Campañas\')','campaign','offeruse',4),(71,'t(\'Agendas\')','phonebook','offercdr',4),(72,'t(\'Numeros\')','phonenumber','numbers',4),(73,'t(\'Resumen por Usuario\')','cdrsummarybyuser','tariffs',5),(74,'t(\'Cdr\')','cdr','cdr',5),(75,'t(\'Resumen CDR\')','cdrsummary','callsummary',5),(76,'t(\'Operator Status\')','operatorstatus','callonline',5),(77,'t(\'Campaign Logins\')','loginscampaign','offer',5),(79,'t(\'Categorias\')','category','queues',4),(80,'t(\'CdrSumaryOperador\')','cdrsumaryoperador','callsummary',5),(84,'t(\'Pools\')','pools','cdr',1),(85,'t(\'Dashboard\')','dashboard','callback',2),(86,'t(\'Predictive Report\')','campaignpredictive','callback',5),(87,'t(\'Breaks\')','breaks','modules',1),(88,'t(\'Portabilidade Codigos\')','portabilidadecodigos','trunk',3),(90,'t(\'Destination\')','diddestination','diddestination',10),(91,'t(\'Ura\')','ivr','ivr',10),(92,'t(\'Massive Call Campaign\')','massivecallcampaign','tariffs',11),(93,'t(\'Massive Call PhoneBook\')','massivecallphonebook','prefixs',11),(94,'t(\'Massive Call PhoneNumber\')','massivecallphonenumber','callsummary',11),(95,'t(\'Massive Call Report\')','massivecallreport','prefixs',5);
/*!40000 ALTER TABLE `pkg_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_operator_status`
--

DROP TABLE IF EXISTS `pkg_operator_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_operator_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `in_call` int(1) NOT NULL DEFAULT '0',
  `peer_status` varchar(25) DEFAULT NULL,
  `queue_status` int(11) DEFAULT NULL,
  `queue_paused` int(11) DEFAULT NULL,
  `last_call` int(11) DEFAULT NULL,
  `last_call_channel` varchar(30) DEFAULT NULL,
  `calls_taken` int(11) DEFAULT NULL,
  `last_call_ringtime` time DEFAULT NULL,
  `categorizing` int(11) DEFAULT NULL,
  `time_start_cat` varchar(20) DEFAULT NULL,
  `media_to_cat` int(11) NOT NULL DEFAULT '0',
  `cant_cat` int(11) NOT NULL DEFAULT '0',
  `time_free` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_operator_status`
--

LOCK TABLES `pkg_operator_status` WRITE;
/*!40000 ALTER TABLE `pkg_operator_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_operator_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_phonebook`
--

DROP TABLE IF EXISTS `pkg_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_phonebook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trunk` int(11) NOT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `show_numbers_operator` int(11) NOT NULL DEFAULT '1',
  `reprocessar` int(2) NOT NULL DEFAULT '0',
  `find_location` int(1) NOT NULL DEFAULT '0',
  `portabilidadeFixed` tinyint(1) NOT NULL DEFAULT '0',
  `portabilidadeMobile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_trunk_pkg_phonebook` (`id_trunk`),
  KEY `fk_pkg_campaign_pkg_phonebook` (`id_campaign`),
  CONSTRAINT `fk_pkg_trunk_pkg_phonebook` FOREIGN KEY (`id_trunk`) REFERENCES `pkg_trunk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_phonebook`
--

LOCK TABLES `pkg_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_phonenumber`
--

DROP TABLE IF EXISTS `pkg_phonenumber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_phonenumber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_phonebook` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `number` bigint(30) NOT NULL,
  `status` int(11) DEFAULT '1',
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(60) NOT NULL DEFAULT '',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_category` int(11) DEFAULT NULL,
  `datebackcall` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cita_concreta` int(1) NOT NULL DEFAULT '0',
  `info` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `neighborhood` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(150) NOT NULL DEFAULT '',
  `state` varchar(40) NOT NULL DEFAULT '',
  `country` varchar(40) NOT NULL DEFAULT '',
  `dni` varchar(20) DEFAULT NULL,
  `cpf` varchar(15) NOT NULL DEFAULT '',
  `mobile` varchar(20) DEFAULT NULL,
  `number_home` varchar(20) DEFAULT NULL,
  `number_office` varchar(20) DEFAULT NULL,
  `zip_code` varchar(20) NOT NULL DEFAULT '',
  `company` varchar(50) NOT NULL DEFAULT '',
  `birth_date` varchar(20) DEFAULT NULL,
  `type_user` varchar(50) NOT NULL DEFAULT '',
  `sexo` varchar(10) NOT NULL DEFAULT '',
  `edad` varchar(11) DEFAULT NULL,
  `profesion` varchar(50) NOT NULL DEFAULT '',
  `mobile_2` varchar(20) DEFAULT NULL,
  `option_1` varchar(80) NOT NULL DEFAULT '',
  `option_2` varchar(80) NOT NULL DEFAULT '',
  `option_3` varchar(80) NOT NULL DEFAULT '',
  `option_4` varchar(80) NOT NULL DEFAULT '',
  `option_5` varchar(80) NOT NULL DEFAULT '',
  `option_6` varchar(80) DEFAULT NULL,
  `option_7` varchar(80) DEFAULT NULL,
  `option_8` varchar(80) DEFAULT NULL,
  `sessiontime` int(11) NOT NULL DEFAULT '0',
  `gps` varchar(30) NOT NULL DEFAULT '',
  `beneficio_number` varchar(50) DEFAULT NULL,
  `quantidade_transacoes` varchar(50) DEFAULT NULL,
  `inicio_beneficio` varchar(50) DEFAULT NULL,
  `beneficio_valor` varchar(50) DEFAULT NULL,
  `banco` varchar(50) DEFAULT NULL,
  `conta_tipo` varchar(20) DEFAULT NULL,
  `agencia` varchar(50) DEFAULT NULL,
  `conta` varchar(50) DEFAULT NULL,
  `address_complement` varchar(50) DEFAULT NULL,
  `address_number` varchar(10) DEFAULT NULL,
  `telefone_fixo1` varchar(50) DEFAULT NULL,
  `telefone_fixo2` varchar(50) DEFAULT NULL,
  `telefone_fixo3` varchar(50) DEFAULT NULL,
  `telefone_celular1` varchar(50) DEFAULT NULL,
  `telefone_celular2` varchar(50) DEFAULT NULL,
  `telefone_celular3` varchar(50) DEFAULT NULL,
  `telefone_fixo_comercial1` varchar(50) DEFAULT NULL,
  `telefone_fixo_comercial2` varchar(60) DEFAULT NULL,
  `telefone_fixo_comercial3` varchar(60) DEFAULT NULL,
  `parente1` varchar(100) DEFAULT NULL,
  `fone_parente1` varchar(60) DEFAULT NULL,
  `parente2` varchar(60) DEFAULT NULL,
  `fone_parente2` varchar(60) DEFAULT NULL,
  `parente3` varchar(60) DEFAULT NULL,
  `fone_parente3` varchar(60) DEFAULT NULL,
  `vizinho1` varchar(100) DEFAULT NULL,
  `telefone_vizinho1` varchar(60) DEFAULT NULL,
  `vizinho2` varchar(100) DEFAULT NULL,
  `telefone_vizinho2` varchar(60) DEFAULT NULL,
  `vizinho3` varchar(100) DEFAULT NULL,
  `telefone_vizinho3` varchar(60) DEFAULT NULL,
  `email2` varchar(100) DEFAULT NULL,
  `email3` varchar(100) DEFAULT NULL,
  `timeCall` int(5) NOT NULL DEFAULT '0',
  `try` int(1) NOT NULL DEFAULT '0',
  `beneficio_especie` varchar(60) DEFAULT NULL,
  `valor_proposta` int(10) DEFAULT NULL,
  `valor_parcela` int(10) DEFAULT NULL,
  `credit_card_name` varchar(50) DEFAULT NULL,
  `credit_card_type` varchar(30) DEFAULT NULL,
  `credit_card_number` varchar(50) DEFAULT NULL,
  `credit_card_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_phonebook_pkg_phonenumber` (`id_phonebook`),
  KEY `number` (`number`),
  KEY `id_user` (`id_user`),
  KEY `fk_pkg_category_pkg_phonenumber` (`id_category`),
  CONSTRAINT `fk_pkg_category_pkg_phonenumber` FOREIGN KEY (`id_category`) REFERENCES `pkg_category` (`id`),
  CONSTRAINT `fk_pkg_phonebook_pkg_phonenumber` FOREIGN KEY (`id_phonebook`) REFERENCES `pkg_phonebook` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_phonenumber` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_phonenumber`
--

LOCK TABLES `pkg_phonenumber` WRITE;
/*!40000 ALTER TABLE `pkg_phonenumber` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_phonenumber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_pools`
--

DROP TABLE IF EXISTS `pkg_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_pools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(200) NOT NULL,
  `type` varchar(20) NOT NULL,
  `answer_0` varchar(200) NOT NULL,
  `id_polls_0` int(11) DEFAULT NULL,
  `answer_1` varchar(200) DEFAULT NULL,
  `id_polls_1` int(11) DEFAULT NULL,
  `answer_2` varchar(200) DEFAULT NULL,
  `id_polls_2` int(11) DEFAULT NULL,
  `answer_3` varchar(200) DEFAULT NULL,
  `id_polls_3` int(11) DEFAULT NULL,
  `answer_4` varchar(200) DEFAULT NULL,
  `id_polls_4` int(11) DEFAULT NULL,
  `answer_5` varchar(200) DEFAULT NULL,
  `id_polls_5` int(11) DEFAULT NULL,
  `answer_6` varchar(200) DEFAULT NULL,
  `id_polls_6` int(11) DEFAULT NULL,
  `answer_7` varchar(200) DEFAULT NULL,
  `id_polls_7` int(11) DEFAULT NULL,
  `answer_8` varchar(200) DEFAULT NULL,
  `id_polls_8` int(11) DEFAULT NULL,
  `answer_9` varchar(200) DEFAULT NULL,
  `id_polls_9` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_pools`
--

LOCK TABLES `pkg_pools` WRITE;
/*!40000 ALTER TABLE `pkg_pools` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_pools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_predictive`
--

DROP TABLE IF EXISTS `pkg_predictive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_predictive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) DEFAULT NULL,
  `uniqueid` varchar(50) NOT NULL,
  `number` varchar(20) NOT NULL,
  `operador` varchar(20) DEFAULT NULL,
  `amd` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueid` (`uniqueid`),
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_predictive`
--

LOCK TABLES `pkg_predictive` WRITE;
/*!40000 ALTER TABLE `pkg_predictive` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_predictive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_predictive_gen`
--

DROP TABLE IF EXISTS `pkg_predictive_gen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_predictive_gen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(20) DEFAULT NULL,
  `uniqueID` varchar(30) DEFAULT NULL,
  `id_phonebook` int(11) DEFAULT NULL,
  `ringing_time` int(11) NOT NULL DEFAULT '0',
  `amd` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueID` (`uniqueID`),
  KEY `ringing_time` (`ringing_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_predictive_gen`
--

LOCK TABLES `pkg_predictive_gen` WRITE;
/*!40000 ALTER TABLE `pkg_predictive_gen` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_predictive_gen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_provider`
--

DROP TABLE IF EXISTS `pkg_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `credit` decimal(10,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cons_pkg_provider_provider_name` (`provider_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_provider`
--

LOCK TABLES `pkg_provider` WRITE;
/*!40000 ALTER TABLE `pkg_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue`
--

DROP TABLE IF EXISTS `pkg_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `musiconhold` varchar(128) DEFAULT NULL,
  `announce` varchar(128) DEFAULT NULL,
  `context` varchar(128) DEFAULT NULL,
  `timeout` int(11) DEFAULT NULL,
  `monitor_join` tinyint(1) DEFAULT NULL,
  `monitor_format` varchar(128) DEFAULT NULL,
  `queue_youarenext` varchar(128) DEFAULT NULL,
  `queue_thereare` varchar(128) DEFAULT NULL,
  `queue_callswaiting` varchar(128) DEFAULT NULL,
  `queue_holdtime` varchar(128) DEFAULT NULL,
  `queue_minutes` varchar(128) DEFAULT NULL,
  `queue_seconds` varchar(128) DEFAULT NULL,
  `queue_lessthan` varchar(128) DEFAULT NULL,
  `queue_thankyou` varchar(128) DEFAULT NULL,
  `queue_reporthold` varchar(128) DEFAULT NULL,
  `announce_frequency` int(11) DEFAULT NULL,
  `announce_round_seconds` int(11) DEFAULT NULL,
  `announce_holdtime` varchar(128) DEFAULT NULL,
  `retry` int(11) DEFAULT NULL,
  `wrapuptime` int(11) DEFAULT NULL,
  `maxlen` int(11) DEFAULT NULL,
  `servicelevel` int(11) DEFAULT NULL,
  `strategy` varchar(128) DEFAULT NULL,
  `joinempty` varchar(128) DEFAULT NULL,
  `leavewhenempty` varchar(128) DEFAULT NULL,
  `eventmemberstatus` tinyint(1) DEFAULT NULL,
  `eventwhencalled` tinyint(1) DEFAULT NULL,
  `reportholdtime` tinyint(1) DEFAULT NULL,
  `memberdelay` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `timeoutrestart` tinyint(1) DEFAULT NULL,
  `periodic_announce` varchar(50) DEFAULT NULL,
  `periodic_announce_frequency` int(11) DEFAULT NULL,
  `ringinuse` tinyint(1) DEFAULT NULL,
  `setinterfacevar` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_pkg_user_pkg_queue` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_queue` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue`
--

LOCK TABLES `pkg_queue` WRITE;
/*!40000 ALTER TABLE `pkg_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue_call_waiting`
--

DROP TABLE IF EXISTS `pkg_queue_call_waiting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue_call_waiting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue_call_waiting`
--

LOCK TABLES `pkg_queue_call_waiting` WRITE;
/*!40000 ALTER TABLE `pkg_queue_call_waiting` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue_call_waiting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue_member`
--

DROP TABLE IF EXISTS `pkg_queue_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue_member` (
  `uniqueid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `membername` varchar(40) DEFAULT NULL,
  `queue_name` varchar(128) DEFAULT NULL,
  `interface` varchar(128) DEFAULT NULL,
  `penalty` int(11) DEFAULT NULL,
  `paused` int(11) DEFAULT NULL,
  PRIMARY KEY (`uniqueid`),
  UNIQUE KEY `queue_interface` (`queue_name`,`interface`),
  KEY `fk_pkg_user_pkg_queue_member` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_queue_member` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue_member`
--

LOCK TABLES `pkg_queue_member` WRITE;
/*!40000 ALTER TABLE `pkg_queue_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_sip`
--

DROP TABLE IF EXISTS `pkg_sip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_sip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `accountcode` varchar(11) NOT NULL,
  `regexten` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `amaflags` char(7) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `callgroup` char(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `callerid` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `canreinvite` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `context` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `DEFAULTip` char(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `dtmfmode` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RFC2833',
  `fromuser` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fromdomain` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `host` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `insecure` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `language` char(2) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mailbox` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `md5secret` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `nat` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `deny` varchar(95) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `permit` varchar(95) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mask` varchar(95) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `pickupgroup` char(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `port` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `qualify` char(7) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `restrictcid` char(1) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rtptimeout` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `rtpholdtimeout` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `secret` varchar(20) DEFAULT NULL,
  `type` char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'friend',
  `username` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `disallow` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'all',
  `allow` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `musiconhold` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `regseconds` int(11) NOT NULL DEFAULT '0',
  `ipaddr` char(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `cancallforward` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `fullcontact` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `setvar` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `regserver` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `lastms` varchar(11) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `directrtpsetup` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `defaultuser` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `auth` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `subscribemwi` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `vmexten` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `cid_number` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `callingpres` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `usereqphone` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `incominglimit` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `subscribecontext` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `musicclass` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `mohsuggest` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `allowtransfer` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'no',
  `autoframing` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `maxcallbitrate` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `outboundproxy` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `rtpkeepalive` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '0',
  `compactheaders` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `relaxdtmf` varchar(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `useragent` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `calllimit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`),
  KEY `ipaddr` (`ipaddr`),
  KEY `port` (`port`),
  KEY `sip_friend_hp_index` (`host`,`port`),
  KEY `sip_friend_ip_index` (`ipaddr`,`port`),
  KEY `fk_pkg_user_pkg_sip` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_sip` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_sip`
--

LOCK TABLES `pkg_sip` WRITE;
/*!40000 ALTER TABLE `pkg_sip` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_sip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_trunk`
--

DROP TABLE IF EXISTS `pkg_trunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_trunk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_provider` int(11) NOT NULL,
  `failover_trunk` int(11) DEFAULT NULL,
  `trunkcode` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `host` varchar(100) NOT NULL,
  `trunkprefix` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `providertech` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `providerip` char(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `removeprefix` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `secondusedreal` int(11) DEFAULT '0',
  `secondusedcarrier` int(11) DEFAULT '0',
  `secondusedratecard` int(11) DEFAULT '0',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `addparameter` char(120) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `inuse` int(11) DEFAULT '0',
  `maxuse` int(11) DEFAULT '-1',
  `status` int(11) DEFAULT '1',
  `if_max_use` int(11) DEFAULT '0',
  `user` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `secret` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `allow` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `link_sms` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `directmedia` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `context` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'billing',
  `dtmfmode` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RFC2833',
  `insecure` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'port,invite',
  `nat` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'force_rport,comedia',
  `qualify` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `type` char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'peer',
  `disallow` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'all',
  `sms_res` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'all',
  `fromdomain` varchar(50) NOT NULL DEFAULT '',
  `fromuser` varchar(50) NOT NULL DEFAULT '',
  `register_string` varchar(150) NOT NULL DEFAULT '',
  `register` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_provider_pkg_trunk` (`id_provider`),
  CONSTRAINT `fk_pkg_provider_pkg_trunk` FOREIGN KEY (`id_provider`) REFERENCES `pkg_provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_trunk`
--

LOCK TABLES `pkg_trunk` WRITE;
/*!40000 ALTER TABLE `pkg_trunk` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_trunk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user`
--

DROP TABLE IF EXISTS `pkg_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) NOT NULL,
  `id_current_phonenumber` int(11) DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `force_logout` int(1) NOT NULL DEFAULT '0',
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `direction` char(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `zipcode` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `state` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `phone` char(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mobile` char(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `datecreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(70) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `company` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` text,
  `campaign_login` int(11) DEFAULT NULL,
  `usuario_tns` varchar(50) DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `training` tinyint(1) NOT NULL DEFAULT '0',
  `conta` varchar(15) NOT NULL DEFAULT '',
  `agencia` varchar(15) NOT NULL DEFAULT '',
  `banck` varchar(25) NOT NULL DEFAULT '',
  `salary` varchar(15) NOT NULL DEFAULT '',
  `cargo` varchar(50) NOT NULL DEFAULT '',
  `stoptcontract` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `startcontract` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `worktime` varchar(25) NOT NULL DEFAULT '',
  `estadocivil` varchar(15) NOT NULL DEFAULT '',
  `escolaridade` varchar(25) NOT NULL DEFAULT '',
  `fathername` varchar(50) NOT NULL DEFAULT '',
  `dni` varchar(25) NOT NULL DEFAULT '',
  `cpf` varchar(25) NOT NULL DEFAULT '',
  `birthday` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hometown` varchar(50) NOT NULL DEFAULT '',
  `mothername` varchar(25) NOT NULL DEFAULT '',
  `webphone` tinyint(1) NOT NULL DEFAULT '0',
  `break_mandatory` int(1) NOT NULL DEFAULT '0',
  `auto_load_phonenumber` tinyint(1) NOT NULL DEFAULT '0',
  `allow_direct_call_campaign` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_pkg_group_user_pkg_user` (`id_group`),
  CONSTRAINT `fk_pkg_user_pkg_group_user` FOREIGN KEY (`id_group`) REFERENCES `pkg_group_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user`
--

LOCK TABLES `pkg_user` WRITE;
/*!40000 ALTER TABLE `pkg_user` DISABLE KEYS */;
INSERT INTO `pkg_user` VALUES (1,1,NULL,NULL,0,'admin','magnus','Admin',NULL,NULL,NULL,NULL,NULL,1,'2014-02-12 19:41:42',NULL,NULL,NULL,'',NULL,NULL,NULL,'2017-08-08 21:04:48',0,'','','','','','0000-00-00 00:00:00','0000-00-00 00:00:00','','','','','','','0000-00-00 00:00:00','torres','',0,0,0,NULL);
/*!40000 ALTER TABLE `pkg_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_campaign`
--

DROP TABLE IF EXISTS `pkg_user_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_campaign` (
  `id_user` int(11) NOT NULL,
  `id_campaign` int(11) NOT NULL,
  PRIMARY KEY (`id_user`,`id_campaign`),
  KEY `fk_pkg_campaign_pkg_user_campaign` (`id_campaign`),
  CONSTRAINT `fk_pkg_campaign_pkg_user_campaign` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_user_campaign` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_campaign`
--

LOCK TABLES `pkg_user_campaign` WRITE;
/*!40000 ALTER TABLE `pkg_user_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_user_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_online`
--

DROP TABLE IF EXISTS `pkg_user_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_time` int(11) DEFAULT NULL,
  `pause` int(11) NOT NULL,
  `pausetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_pause` int(11) NOT NULL DEFAULT '0',
  `turno` varchar(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_user_online` (`id_user`),
  KEY `fk_pkg_campaign_pkg_user_online` (`id_campaign`),
  CONSTRAINT `fk_pkg_campaign_pkg_user_online` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`),
  CONSTRAINT `fk_pkg_operador_pkg_user_online` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_online`
--

LOCK TABLES `pkg_user_online` WRITE;
/*!40000 ALTER TABLE `pkg_user_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_user_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_type`
--

DROP TABLE IF EXISTS `pkg_user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_type`
--

LOCK TABLES `pkg_user_type` WRITE;
/*!40000 ALTER TABLE `pkg_user_type` DISABLE KEYS */;
INSERT INTO `pkg_user_type` VALUES (1,'Admin'),(2,'Operador'),(3,'Cliente');
/*!40000 ALTER TABLE `pkg_user_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_workshift`
--

DROP TABLE IF EXISTS `pkg_user_workshift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_workshift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_workshift` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_asistencia_pkg_user_asistencia` (`id_workshift`),
  KEY `fk_pkg_user_pkg_user_asistencia` (`id_user`),
  CONSTRAINT `fk_pkg_asistencia_pkg_user_asistencia` FOREIGN KEY (`id_workshift`) REFERENCES `pkg_work_shift` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_user_asistencia` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_workshift`
--

LOCK TABLES `pkg_user_workshift` WRITE;
/*!40000 ALTER TABLE `pkg_user_workshift` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_user_workshift` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_work_shift`
--

DROP TABLE IF EXISTS `pkg_work_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_work_shift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `day` date NOT NULL DEFAULT '0000-00-00',
  `turno` varchar(1) NOT NULL DEFAULT 'M',
  `start_time` time NOT NULL DEFAULT '08:00:00',
  `stop_time` time NOT NULL DEFAULT '13:00:00',
  `week_day` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_asistencia` (`id_user`),
  CONSTRAINT `fk_pkg_operador_asistencia` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_work_shift`
--

LOCK TABLES `pkg_work_shift` WRITE;
/*!40000 ALTER TABLE `pkg_work_shift` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_work_shift` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-19 19:07:00
