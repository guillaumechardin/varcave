-- MySQL dump 10.17  Distrib 10.3.22-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: varcaveExport2
-- ------------------------------------------------------
-- Server version	10.3.22-MariaDB-1ubuntu1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl`
--

DROP TABLE IF EXISTS `acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `guidv4` varchar(36) NOT NULL,
  `related_groups` tinytext NOT NULL,
  `related_webpage` tinytext NOT NULL,
  `read_only` tinyint(4) NOT NULL,
  `editdate` int(11) NOT NULL,
  PRIMARY KEY (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl`
--

LOCK TABLES `acl` WRITE;
/*!40000 ALTER TABLE `acl` DISABLE KEYS */;
INSERT INTO `acl` VALUES (1,'c47d51c4-62c4-5f40-9047-c466388cc52b','users','display.php',0,1589727955),(2,'91562650-629a-4461-aa38-e9e5c7cbd432','editors','display.php',0,1589727955),(3,'39e3c075-9d59-5c71-888c-c45527ad05b8','admin','aclmgmt.php',1,1588147492),(4,'7c19458c-5d4f-5f25-9af5-9b2ea7f7a79b','admin,editors','global',0,1589823264),(5,'98aef116-c96d-5163-9ed3-4cd8482b10a4','admin','global',0,1588147492),(6,'370f195f-0d8d-46bc-b3a6-7c9da3e88289','users','global',0,1589727955),(7,'b3c16122-c6cb-417f-a0a8-b981f09acb37','admin,editors','editcave.php',0,1588147492),(8,'3935e285-9367-4945-98c1-be528995c9d0','admin,editors','newcave.php',0,1589823269),(9,'ade8fdde-1e7c-4abd-9ead-99787a13f099','editors','ressources.php',0,1589823255),(10,'a45f34efc-536f-4a31-a5e6-e2a8b24cdda','admin','siteconfig.php',0,1588147492),(11,'150edca1-1783-45ae-a433-0a1b2ff332bc','admin,editors','techsupport.php',0,1589823302),(12,'1abc1ede-a115-4613-9f88-cbc0a86d6778','admin','newsmgmt.php',0,1588147492),(13,'8e9d7d52-f061-4021-8109-cc48fbfe0e61','users','ressources.php',0,1589727955),(14,'bda76758-f447-4f6a-90da-2c595a4adfb5','users','getpdf.php',0,1589727955),(15,'52f41225-92c9-4926-a24f-d62f1e824f8d','users','getgpxkml.php',0,1589727955);
/*!40000 ALTER TABLE `acl` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `acl_insEditdate` BEFORE INSERT ON `acl`
 FOR EACH ROW SET new.editdate = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `acl_updEditdate` BEFORE UPDATE ON `acl`
 FOR EACH ROW SET new.editdate = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `caves`
--

DROP TABLE IF EXISTS `caves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caves` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `guidv4` varchar(36) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `addendum` text DEFAULT NULL,
  `annex` text DEFAULT NULL,
  `editYear` text DEFAULT NULL,
  `bibliography` text DEFAULT NULL,
  `mapName` text DEFAULT NULL,
  `town` text DEFAULT NULL,
  `CO2` tinyint(1) DEFAULT 0,
  `accessSketchText` text DEFAULT NULL,
  `airflowDate` text DEFAULT NULL,
  `exploreDate` text DEFAULT NULL,
  `shortDescription` text DEFAULT NULL,
  `documentOfOrigin` text DEFAULT NULL,
  `length` decimal(7,2) DEFAULT NULL,
  `explorers` text DEFAULT NULL,
  `editDate` int(11) DEFAULT 0,
  `geology` text DEFAULT NULL,
  `hydrology` text DEFAULT NULL,
  `inventor` text DEFAULT NULL,
  `place` text DEFAULT NULL,
  `mountainRange` text DEFAULT NULL,
  `airflow` tinyint(1) DEFAULT 0,
  `numero_arrondissement` text DEFAULT NULL,
  `numero_commune` text DEFAULT NULL,
  `numero_departement` text DEFAULT NULL,
  `caveRef` text DEFAULT NULL,
  `depth` text DEFAULT NULL,
  `maxDepth` decimal(4,1) DEFAULT NULL,
  `area` text DEFAULT NULL,
  `topographer` text DEFAULT NULL,
  `random_coordinates` tinyint(1) DEFAULT 0,
  `json_coords` text NOT NULL DEFAULT '',
  `coords_GPS_checked` tinyint(1) DEFAULT 0,
  `zone_natura_2000` tinyint(1) NOT NULL DEFAULT 0,
  `anchors` tinyint(1) NOT NULL DEFAULT 0,
  `noAccess` tinyint(1) NOT NULL DEFAULT 0,
  `PNR_SB` tinyint(1) NOT NULL DEFAULT 0,
  `documents` text DEFAULT '',
  `biologyDocuments` text DEFAULT '',
  `files` text DEFAULT NULL,
  PRIMARY KEY (`indexid`),
  KEY `indexid` (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caves`
--

LOCK TABLES `caves` WRITE;
/*!40000 ALTER TABLE `caves` DISABLE KEYS */;
INSERT INTO `caves` VALUES (1,'f82105af-0b1a-4da4-a25d-4953df04a6e0','cave one','2019','This field permit to add more informations.',NULL,'p12  book of caves','25M WEST','NOWHERE',0,NULL,NULL,'20/05/2012',NULL,'Peter Parker',NULL,'Paul Smith',1589823571,NULL,NULL,'Peter Parker','cave one',NULL,0,NULL,NULL,NULL,'123456XYZ','-322/+65',322.0,'Cave one',NULL,0,'',0,0,0,0,0,'','',NULL);
/*!40000 ALTER TABLE `caves` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `editDateAuIns` BEFORE INSERT ON `caves`
 FOR EACH ROW SET new.editDate =  UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `editDateAuUpd` BEFORE UPDATE ON `caves`
 FOR EACH ROW SET new.editDate =  UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `changelog`
--

DROP TABLE IF EXISTS `changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changelog` (
  `indexid_caves` int(11) NOT NULL,
  `chgLogTxt` text DEFAULT NULL,
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isVisible` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`indexid`),
  KEY `indexid_caves` (`indexid_caves`),
  CONSTRAINT `indexidCaves` FOREIGN KEY (`indexid_caves`) REFERENCES `caves` (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changelog`
--

LOCK TABLES `changelog` WRITE;
/*!40000 ALTER TABLE `changelog` DISABLE KEYS */;
INSERT INTO `changelog` VALUES (1,'NEW CAVE',1,'2020-05-18 17:36:29',1);
/*!40000 ALTER TABLE `changelog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `configItem` varchar(40) COLLATE utf8_bin NOT NULL,
  `configItemValue` text COLLATE utf8_bin NOT NULL,
  `configItemType` text COLLATE utf8_bin NOT NULL,
  `configItemMtime` int(11) NOT NULL,
  `configItemGroup` text COLLATE utf8_bin NOT NULL,
  `configItemAdminOnly` tinyint(1) NOT NULL DEFAULT 0,
  `configIndexid` int(5) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`configIndexid`),
  UNIQUE KEY `configItem` (`configItem`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('version','3.0','dec',0,'system',1,1),('httpdomain','http://www.website.tld','text',0,'system',0,3),('httpwebroot','','text',0,'system',0,4),('websiteFullName','..:: DEFAULT NAME ::..','text',0,'configSite',0,5),('disclaimer','Disclaimer','text',0,'configSite',0,7),('sessionlifetime','86400','dec',0,'cookieUser',0,8),('maxSearchResults_default','50','dec',1,'cookieUser',0,72),('default_ascDesc','asc','text',0,'cookieUser',0,11),('stats','1','bool',0,'configSiteStats',0,12),('displayedStats','30','dec',0,'configSiteStats',0,13),('welcomePageShowLastUpdate','20','dec',0,'configSite',0,17),('adminIP','127.0.0.1','array',0,'configSiteStats',0,21),('anon_get_obfsuc_coords','1','bool',0,'configSite',0,70),('pdfCoordSystem','UTM','text',0,'PDF',0,71),('RWfolders','archive_cavites,img/cavites/*,pdfbook,ressources/*','array',0,'configSite',1,26),('noAccessDisclaimer','no access to this cave','text',0,'configSite',0,33),('geoportail_api_key','*key*','text',0,'geoAPI',0,37),('randCoordDisclaimer','Coordinates obfuscated','text',0,'configSite',0,38),('default_geo_api','googlemaps','text',0,'geoAPI',0,39),('footerMsg','footer message','text',0,'configSite',0,40),('fallbackLanguage','FR','text',0,'configSite',0,41),('googlemaps_api_key','*key*','text',0,'geoAPI',0,42),('caves_files_path','caves/','text',0,'configSite',0,43),('excludedcopyfields','indexid , guidv4, name,length,depth,maxDepth,json_coords,coords_GPS_checked,sketchAccessPath,files','text',0,'configSite',1,44),('sessiondir','sessions','text',0,'system',0,45),('ressources_stor_dir','ressources/','text',0,'configSite',0,46),('max_news_homepage','3','text',0,'configsite',0,48),('timezone','Europe/Paris','text',0,'configsite',0,49),('gApi_zoom_lvl','16','text',0,'configSite',0,50),('use_googleapi_img_pdf','1','bool',0,'configSite',0,51),('cache_dir','cache','text',0,'config site',1,52),('dynamic_rights','0','bool',0,'configSite',1,53),('use_anon_auth','0','bool',0,'configSite',1,54),('loglevel','1','dec',0,'ConfigSite',0,55),('mail_use_captcha','1','dec',0,'Captcha',0,56),('captcha_secret_key','*key*','text',0,'Captcha',0,57),('captcha_public_key','*key*','text',0,'Captcha',0,58),('smtp_server','ssl0.ovh.net','text',0,'configemail',0,59),('smtp_port','587','dec',0,'configemail',0,60),('smtp_user','username','text',0,'configemail',0,61),('smtp_userpwd','password','text',0,'configemail',0,62),('smtp_useauth','1','bool',0,'configemail',0,63),('smtp_sender','noreply@domain.com','text',0,'configemail',0,64),('smtp_max_attach_size','1500','dec',0,'configemail',0,65),('smtp_max_attach_global_size','5120','dec',0,'configemail',0,66),('smtp_cave_edit_recipients','recipient@domain.com','text',0,'configemail',0,67),('smtp_general_inquiry_recipient','recipient1@domain.com, recipient2@domain2.com','text',0,'configemail',0,68),('smtp_server_debuglbvl','0','dec',0,'configemail',0,69);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `end_user_fields`
--

DROP TABLE IF EXISTS `end_user_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `end_user_fields` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `field` text NOT NULL,
  `type` tinytext NOT NULL,
  `sort_order` int(11) NOT NULL,
  `show_on_display` tinyint(4) NOT NULL,
  `show_on_search` tinyint(4) NOT NULL,
  `show_on_edit` tinyint(4) NOT NULL,
  PRIMARY KEY (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `end_user_fields`
--

LOCK TABLES `end_user_fields` WRITE;
/*!40000 ALTER TABLE `end_user_fields` DISABLE KEYS */;
INSERT INTO `end_user_fields` VALUES (2,'name','text',1,1,1,1),(3,'addendum','text',9999,1,1,1),(4,'annex','text',2,0,1,1),(5,'editYear','text',9999,1,1,0),(6,'bibliography','text',9999,1,1,1),(7,'mapName','text',9999,1,1,1),(8,'town','text',3,1,1,1),(9,'CO2','bool',9999,1,1,1),(10,'accessSketchText','other',9999,0,1,1),(11,'airflowDate','text',9999,1,1,1),(12,'exploreDate','text',9999,1,1,1),(13,'shortDescription','text',9999,0,1,1),(14,'documentOfOrigin','text',9999,1,1,1),(15,'length','decimal',4,1,1,1),(16,'explorers','text',9999,1,1,1),(17,'editDate','text',9999,1,1,1),(18,'geology','text',9999,1,1,1),(19,'hydrology','text',9999,1,1,1),(20,'inventor','text',9999,1,1,1),(21,'place','text',5,1,1,1),(22,'mountainRange','text',9999,1,1,1),(23,'airflow','bool',9999,0,1,1),(24,'numero_arrondissement','text',9999,1,1,1),(25,'numero_commune','text',9999,1,1,1),(26,'numero_departement','text',9999,1,1,1),(27,'caveRef','text',2,1,1,1),(28,'depth','text',5,1,1,1),(29,'maxDepth','decimal',5,1,1,1),(30,'area','text',3,1,1,1),(31,'topographer','text',9999,1,1,1),(55,'zone_natura_2000','bool',9999,0,1,1),(56,'anchors','bool',9999,0,1,1),(57,'noAccess','bool',9999,0,1,1),(58,'PNR_SB','bool',9999,0,1,1),(59,'documents','json',9999,0,1,1),(61,'guidv4','text',9999,0,0,0),(62,'biologyDocuments','json',9999,1,1,1),(63,'json_coords','json',9999,0,1,1),(65,'cave_maps','json',9999,0,0,1),(67,'random_coordinates','bool',9999,1,1,1),(68,'coords_GPS_checked','bool',9999,1,0,1),(69,'photos','json',9999,0,0,1),(70,'sketch_access','json',115,1,0,1);
/*!40000 ALTER TABLE `end_user_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files_ressources`
--

DROP TABLE IF EXISTS `files_ressources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files_ressources` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(254) NOT NULL,
  `display_group` tinytext NOT NULL,
  `filepath` varchar(511) NOT NULL,
  `description` varchar(2047) NOT NULL,
  `creation_date` int(11) NOT NULL,
  `access_rights` varchar(2047) NOT NULL,
  `creator` varchar(50) NOT NULL,
  PRIMARY KEY (`indexid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files_ressources`
--

LOCK TABLES `files_ressources` WRITE;
/*!40000 ALTER TABLE `files_ressources` DISABLE KEYS */;
/*!40000 ALTER TABLE `files_ressources` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `files_ressources_insCreation_date` BEFORE INSERT ON `files_ressources`
 FOR EACH ROW SET new.creation_date = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `groupName` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL,
  PRIMARY KEY (`indexid`),
  UNIQUE KEY `groupName` (`groupName`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'admin','Website Administrators'),(5,'news','Members can edit new on welcome page'),(2,'anonymous','Anonymous users (unauthenticated users)'),(3,'users','Authenticated users'),(4,'editors','Members can edit caves data, create new caves');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `list_coordinates_systems`
--

DROP TABLE IF EXISTS `list_coordinates_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_coordinates_systems` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `js_lib_filename` varchar(64) NOT NULL,
  `php_lib_filename` varchar(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `list_coordinates_systems`
--

LOCK TABLES `list_coordinates_systems` WRITE;
/*!40000 ALTER TABLE `list_coordinates_systems` DISABLE KEYS */;
INSERT INTO `list_coordinates_systems` VALUES (1,'GEOGRAPHIC','geoconv_longlatdef.js','geoconv_longlatdef.php'),(2,'UTM','geoconv_longlat2utm.js','geoconv_longlat2utm.php'),(3,'LAMBERT3','geoconv_lambert3.js','geoconv_lambert3.php');
/*!40000 ALTER TABLE `list_coordinates_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `list_geo_api`
--

DROP TABLE IF EXISTS `list_geo_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_geo_api` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `list_geo_api`
--

LOCK TABLES `list_geo_api` WRITE;
/*!40000 ALTER TABLE `list_geo_api` DISABLE KEYS */;
INSERT INTO `list_geo_api` VALUES (1,'googlemaps'),(2,'geoportail');
/*!40000 ALTER TABLE `list_geo_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `indexid` int(8) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `last_editor` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `title` text NOT NULL,
  `creation_date` int(11) NOT NULL,
  `edit_date` int(11) NOT NULL,
  PRIMARY KEY (`indexid`),
  KEY `fk_creator` (`creator`),
  KEY `fk_lastEditor` (`last_editor`),
  CONSTRAINT `fk_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`indexid`),
  CONSTRAINT `fk_lastEditor` FOREIGN KEY (`last_editor`) REFERENCES `users` (`indexid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,1,NULL,0,'Your fresh installation of varcave is working. Don\'t forget to change default admin password.','Welcome to Varcave',1589725681,0);
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `news_insCreationDate` BEFORE INSERT ON `news`
 FOR EACH ROW SET new.creation_date = UNIX_TIMESTAMP(), new.edit_date = 0 */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `news_updEditdate` BEFORE UPDATE ON `news`
 FOR EACH ROW SET new.edit_date  = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `cave_id` int(11) NOT NULL,
  `view_count` int(11) NOT NULL,
  `lastupdate` int(11) NOT NULL,
  PRIMARY KEY (`indexid`),
  UNIQUE KEY `cave_id` (`cave_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats`
--

LOCK TABLES `stats` WRITE;
/*!40000 ALTER TABLE `stats` DISABLE KEYS */;
INSERT INTO `stats` VALUES (1,1,0,1590051639);
/*!40000 ALTER TABLE `stats` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `stats_insLastupdate` BEFORE INSERT ON `stats`
 FOR EACH ROW SET new.lastupdate = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `stats_updLastupdate` BEFORE UPDATE ON `stats`
 FOR EACH ROW SET new.lastupdate = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `indexid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `groups` text NOT NULL,
  `password` text NOT NULL,
  `expire` int(11) NOT NULL,
  `created` int(11) DEFAULT NULL,
  `lastUpdate` int(11) NOT NULL,
  `firstname` tinytext NOT NULL,
  `lastname` tinytext NOT NULL,
  `theme` varchar(25) NOT NULL DEFAULT 'default',
  `geo_api` varchar(30) NOT NULL DEFAULT '',
  `last_php_session` tinytext NOT NULL,
  `datatablesMaxItems` int(11) NOT NULL,
  `pref_coord_system` varchar(15) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `emailaddr` tinytext NOT NULL,
  `streetNum` varchar(10) DEFAULT NULL,
  `address1` tinytext NOT NULL,
  `address2` tinytext NOT NULL,
  `postCode` varchar(6) DEFAULT NULL,
  `town` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `licenceNumber` tinytext NOT NULL,
  `phoneNum` tinytext NOT NULL,
  `cavingGroup` text NOT NULL,
  `notes` text NOT NULL,
  `uiLanguage` tinytext NOT NULL,
  PRIMARY KEY (`indexid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin,users','5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',1767225599,1330000522,1589725755,'admin','admin','','','',0,NULL,0,'','0','','','0','','','','','','','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `users_insLastUpdate` BEFORE INSERT ON `users`
 FOR EACH ROW SET
               new.lastUpdate = UNIX_TIMESTAMP(),
               new.created = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `users_updLastUpdate` BEFORE UPDATE ON `users`
 FOR EACH ROW SET new.lastUpdate = UNIX_TIMESTAMP() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-05-25 23:04:56
