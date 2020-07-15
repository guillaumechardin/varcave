-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 15 juil. 2020 à 22:01
-- Version du serveur :  10.3.22-MariaDB-1ubuntu1
-- Version de PHP : 7.2.31-1+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `varcaveExport`
--

-- --------------------------------------------------------

--
-- Structure de la table `acl`
--

CREATE TABLE `acl` (
  `indexid` int(11) NOT NULL,
  `guidv4` varchar(36) NOT NULL,
  `related_groups` tinytext NOT NULL,
  `related_webpage` tinytext NOT NULL,
  `read_only` tinyint(4) NOT NULL,
  `editdate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl`
--

INSERT INTO `acl` (`indexid`, `guidv4`, `related_groups`, `related_webpage`, `read_only`, `editdate`) VALUES
(1, 'c47d51c4-62c4-5f40-9047-c466388cc52b', 'anonymous,users', 'display.php', 0, 1594728332),
(2, '91562650-629a-4461-aa38-e9e5c7cbd432', 'admin,editors', 'display.php', 0, 1594728363),
(3, '39e3c075-9d59-5c71-888c-c45527ad05b8', 'admin', 'aclmgmt.php', 1, 1594726806),
(4, '7c19458c-5d4f-5f25-9af5-9b2ea7f7a79b', 'admin,editors', 'global', 0, 1594728368),
(5, '98aef116-c96d-5163-9ed3-4cd8482b10a4', 'admin', 'global', 0, 1594726806),
(6, '370f195f-0d8d-46bc-b3a6-7c9da3e88289', 'anonymous,users', 'global', 0, 1594728351),
(7, 'b3c16122-c6cb-417f-a0a8-b981f09acb37', 'admin,editors', 'editcave.php', 0, 1594726806),
(8, '3935e285-9367-4945-98c1-be528995c9d0', 'admin,editors', 'newcave.php', 0, 1594728316),
(9, 'ade8fdde-1e7c-4abd-9ead-99787a13f099', 'admin,editors', 'ressources.php', 0, 1594728341),
(10, 'a45f34efc-536f-4a31-a5e6-e2a8b24cdda', 'admin', 'siteconfig.php', 0, 1594726806),
(11, '150edca1-1783-45ae-a433-0a1b2ff332bc', 'admin,editors,news', 'techsupport.php', 0, 1594728303),
(12, '1abc1ede-a115-4613-9f88-cbc0a86d6778', 'admin,news', 'newsmgmt.php', 0, 1594728287),
(13, '8e9d7d52-f061-4021-8109-cc48fbfe0e61', 'users', 'ressources.php', 0, 1594726806),
(14, 'bda76758-f447-4f6a-90da-2c595a4adfb5', 'users', 'getpdf.php', 0, 1594726806),
(15, '52f41225-92c9-4926-a24f-d62f1e824f8d', 'users', 'getgpxkml.php', 0, 1594726806),
(16, '939ea0d9-f23c-4451-89ae-da31c498414c', 'admin', 'fieldsettings.php', 0, 1594726806);

--
-- Déclencheurs `acl`
--
DELIMITER $$
CREATE TRIGGER `acl_insEditdate` BEFORE INSERT ON `acl` FOR EACH ROW SET new.editdate = UNIX_TIMESTAMP()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `acl_updEditdate` BEFORE UPDATE ON `acl` FOR EACH ROW SET new.editdate = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `caves`
--

CREATE TABLE `caves` (
  `indexid` int(11) NOT NULL,
  `guidv4` varchar(36) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `creation_date` text DEFAULT NULL,
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
  `caveRef` text DEFAULT NULL,
  `depth` text DEFAULT NULL,
  `maxDepth` decimal(4,1) DEFAULT NULL,
  `area` text DEFAULT NULL,
  `topographer` text DEFAULT NULL,
  `random_coordinates` tinyint(1) DEFAULT 0,
  `coords_GPS_checked` tinyint(1) DEFAULT 0,
  `sketchAccessPath` mediumtext DEFAULT NULL,
  `zone_natura_2000` tinyint(1) NOT NULL DEFAULT 0,
  `anchors` tinyint(1) NOT NULL DEFAULT 0,
  `noAccess` tinyint(1) NOT NULL DEFAULT 0,
  `PNR_SB` tinyint(1) NOT NULL DEFAULT 0,
  `documents` text DEFAULT NULL,
  `biologyDocuments` text DEFAULT NULL,
  `files` text DEFAULT NULL,
  `json_coords` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `caves`
--

INSERT INTO `caves` (`indexid`, `guidv4`, `name`, `creation_date`, `annex`, `editYear`, `bibliography`, `mapName`, `town`, `CO2`, `accessSketchText`, `airflowDate`, `exploreDate`, `shortDescription`, `documentOfOrigin`, `length`, `explorers`, `editDate`, `geology`, `hydrology`, `inventor`, `place`, `mountainRange`, `airflow`, `caveRef`, `depth`, `maxDepth`, `area`, `topographer`, `random_coordinates`, `coords_GPS_checked`, `sketchAccessPath`, `zone_natura_2000`, `anchors`, `noAccess`, `PNR_SB`, `documents`, `biologyDocuments`, `files`, `json_coords`) VALUES
(1, 'fe99ef56-1eff-4891-9bd8-516665401965', 'Demo cave', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Turn left just after the big tree or the big rock...', '01/01/2000', '01/1959 by Peter Mc Allaway', 'This cave is located in the forest. Is very deep and must be visited by experienced cavers only. Note that some animals can be seen bellow !', NULL, '500.00', 'Mr Smith', 1594843275, 'Portlandian', 'Very wet', 'Jack Rabbit', NULL, 'Cloudy  Mountains', 1, 'CAV001', '-241/+86', '-241.0', 'Somewhere over the rainbow', 'Mr Ruler', 0, 1, NULL, 0, 1, 1, 0, NULL, NULL, '{\n    \"cave_maps\": {\n        \"1\": \"caves//fe99ef56-1eff-4891-9bd8-516665401965/maps/topo.jpg\",\n        \"2\": \"caves//fe99ef56-1eff-4891-9bd8-516665401965/maps/topoA4bis.jpg\"\n    },\n    \"photos\": {\n        \"1\": [\n            \"caves//fe99ef56-1eff-4891-9bd8-516665401965/photos/photo1.jpg\",\n            \"A nice picture\"\n        ],\n        \"2\": [\n            \"caves//fe99ef56-1eff-4891-9bd8-516665401965/photos/photo2.jpg\",\n            \"Another beautifull pic\"\n        ]\n    }\n}', '{\n    \"features\": [\n        {\n            \"type\": \"Feature\",\n            \"properties\": {\n                \"prop0\": \"\"\n            },\n            \"geometry\": {\n                \"coordinates\": [\n                    [\n                        5.8936117,\n                        43.1637726,\n                        365\n                    ]\n                ]\n            }\n        }\n    ]\n}');

--
-- Déclencheurs `caves`
--
DELIMITER $$
CREATE TRIGGER `editDateAuIns` BEFORE INSERT ON `caves` FOR EACH ROW SET new.editDate =  UNIX_TIMESTAMP()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `editDateAuUpd` BEFORE UPDATE ON `caves` FOR EACH ROW SET new.editDate =  UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `changelog`
--

CREATE TABLE `changelog` (
  `indexid_caves` int(11) NOT NULL,
  `chgLogTxt` text DEFAULT NULL,
  `indexid` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isVisible` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `changelog`
--

INSERT INTO `changelog` (`indexid_caves`, `chgLogTxt`, `indexid`, `date`, `isVisible`) VALUES
(1, 'This is a new cave !', 1, '2020-07-15 19:40:46', 1);

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE `config` (
  `configItem` varchar(40) COLLATE utf8_bin NOT NULL,
  `configItemValue` text COLLATE utf8_bin NOT NULL,
  `configItemType` text COLLATE utf8_bin NOT NULL,
  `configItemMtime` int(11) NOT NULL,
  `configItemGroup` text COLLATE utf8_bin NOT NULL,
  `configItemAdminOnly` tinyint(1) NOT NULL DEFAULT 0,
  `configIndexid` int(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `config`
--

INSERT INTO `config` (`configItem`, `configItemValue`, `configItemType`, `configItemMtime`, `configItemGroup`, `configItemAdminOnly`, `configIndexid`) VALUES
('httpdomain', 'http://www.website.com', 'text', 0, 'system', 0, 3),
('httpwebroot', '', 'text', 0, 'system', 0, 4),
('websiteFullName', '..:: WEBSITE NAME ::..', 'text', 0, 'configSite', 0, 5),
('disclaimer', '** Your disclaimer shown in all pdf ', 'text', 0, 'configSite', 0, 7),
('sessionlifetime', '86400', 'dec', 0, 'cookieUser', 0, 8),
('maxSearchResults_default', '50', 'dec', 0, 'cookieUser', 0, 9),
('default_search_field', 'name', 'text', 0, 'cookieUser', 0, 10),
('default_ascDesc', 'asc', 'text', 0, 'cookieUser', 0, 11),
('stats', '1', 'bool', 0, 'configSiteStats', 0, 12),
('displayedStats', '30', 'dec', 0, 'configSiteStats', 0, 13),
('welcomePageShowLastUpdate', '20', 'dec', 0, 'configSite', 0, 17),
('adminIP', '127.0.0.1,192.168.100.1', 'array', 0, 'configSiteStats', 0, 21),
('RWfolders', 'caves,lang,langcache,cache,logs,ressources', 'array', 0, 'configSite', 1, 26),
('noAccessDisclaimer', '** your disclaimer for no accessible caves **', 'text', 0, 'configSite', 0, 33),
('geoportail_api_key', 'choisirgeoportail', 'text', 0, 'geoAPI', 0, 37),
('randCoordDisclaimer', '** disclaimer for cave with obfucated coords **', 'text', 0, 'configSite', 0, 38),
('default_geo_api', 'googlemaps', 'text', 0, 'geoAPI', 0, 39),
('footerMsg', '** footer message **', 'text', 0, 'configSite', 0, 40),
('fallbackLanguage', 'FR', 'text', 0, 'configSite', 0, 41),
('googlemaps_api_key', '**yourKey**', 'text', 0, 'geoAPI', 0, 42),
('caves_files_path', 'caves/', 'text', 0, 'configSite', 0, 43),
('excludedcopyfields', 'indexid,guidv4,name,length,depth,maxDepth,json_coords,coords_GPS_checked,sketchAccessPath,files', 'text', 0, 'configSite', 1, 44),
('ressources_stor_dir', 'ressources/', 'text', 0, 'configSite', 0, 45),
('max_news_homepage', '3', 'text', 0, 'configsite', 0, 47),
('timezone', 'Europe/Paris', 'list', 0, 'configsite', 0, 48),
('gApi_zoom_lvl', '16', 'text', 0, 'configSite', 0, 49),
('returnSearchFields', 'name,guidv4,topographer,maxDepth,length,mountainRange', 'array', 0, 'configSite', 1, 50),
('useSearchAutocomplete', '1', 'bool', 0, 'configSite', 0, 51),
('use_googleapi_img_pdf', '1', 'bool', 0, 'configSite', 0, 52),
('cache_dir', 'cache', 'text', 0, 'config site', 1, 53),
('dynamic_rights', '1', 'bool', 0, 'configSite', 1, 54),
('use_anon_auth', '1', 'bool', 0, 'configSite', 1, 55),
('loglevel', '1', 'dec', 0, 'ConfigSite', 0, 56),
('mail_use_captcha', '1', 'dec', 0, 'Captcha', 0, 57),
('captcha_secret_key', '**secret captcha key**', 'text', 0, 'Captcha', 0, 58),
('captcha_public_key', '**secret captcha key**', 'text', 0, 'Captcha', 0, 59),
('smtp_server', 'ssl0.ovh.net', 'text', 0, 'configemail', 0, 60),
('smtp_port', '587', 'dec', 0, 'configemail', 0, 61),
('smtp_user', '** username **', 'text', 0, 'configemail', 0, 62),
('smtp_userpwd', '** password **', 'text', 0, 'configemail', 0, 63),
('smtp_useauth', '1', 'bool', 0, 'configemail', 0, 64),
('smtp_sender', 'noreply@domain.com', 'text', 0, 'configemail', 0, 65),
('smtp_max_attach_size', '1500', 'dec', 0, 'configemail', 0, 66),
('smtp_max_attach_global_size', '5120', 'dec', 0, 'configemail', 0, 67),
('smtp_cave_edit_recipients', 'recipient@domain.com', 'text', 0, 'configemail', 0, 68),
('smtp_general_inquiry_recipient', 'recipient1@domain.local, more-recipient@domain2.com', 'text', 0, 'configemail', 0, 69),
('smtp_server_debuglbvl', '0', 'dec', 0, 'configemail', 0, 70),
('anon_get_obfsuc_coords', '1', 'bool', 0, 'configSite', 0, 71),
('pdf_coords_system', 'UTM', 'text', 0, 'PDF', 0, 72);

-- --------------------------------------------------------

--
-- Structure de la table `dbversion`
--

CREATE TABLE `dbversion` (
  `id` int(11) NOT NULL,
  `major_version` smallint(6) NOT NULL,
  `minor_version` smallint(6) NOT NULL,
  `patch_version` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `dbversion`
--

INSERT INTO `dbversion` (`id`, `major_version`, `minor_version`, `patch_version`) VALUES
(1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `end_user_fields`
--

CREATE TABLE `end_user_fields` (
  `indexid` int(11) NOT NULL,
  `field` text NOT NULL,
  `type` tinytext NOT NULL,
  `sort_order` int(11) NOT NULL,
  `show_on_display` tinyint(4) NOT NULL,
  `show_on_search` tinyint(4) NOT NULL,
  `show_on_edit` tinyint(4) NOT NULL,
  `field_group` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `end_user_fields`
--

INSERT INTO `end_user_fields` (`indexid`, `field`, `type`, `sort_order`, `show_on_display`, `show_on_search`, `show_on_edit`, `field_group`) VALUES
(2, 'name', 'text', 1, 1, 1, 1, 'main'),
(4, 'annex', 'text', 10000, 0, 1, 1, 'description'),
(5, 'editYear', 'text', 35, 1, 1, 1, 'main'),
(6, 'bibliography', 'text', 38, 1, 1, 1, 'main'),
(7, 'mapName', 'text', 25, 1, 1, 1, 'main'),
(8, 'town', 'text', 3, 1, 1, 1, 'main'),
(9, 'CO2', 'bool', 31, 1, 1, 1, 'main'),
(10, 'accessSketchText', 'text', 9999, 0, 1, 1, 'sketchaccess'),
(11, 'airflowDate', 'text', 9, 1, 1, 1, 'main'),
(12, 'exploreDate', 'text', 20, 1, 1, 1, 'main'),
(13, 'shortDescription', 'text', 9999, 0, 1, 1, 'description'),
(14, 'documentOfOrigin', 'text', 37, 1, 1, 1, 'main'),
(15, 'length', 'decimal', 4, 1, 1, 1, 'main'),
(16, 'explorers', 'text', 21, 1, 1, 1, 'main'),
(17, 'editDate', 'text', 36, 1, 1, 1, 'main'),
(18, 'geology', 'text', 22, 1, 1, 1, 'main'),
(19, 'hydrology', 'text', 23, 1, 1, 1, 'main'),
(20, 'inventor', 'text', 24, 1, 1, 1, 'main'),
(21, 'place', 'text', 7, 1, 1, 1, 'main'),
(22, 'mountainRange', 'text', 18, 1, 1, 1, 'main'),
(23, 'airflow', 'bool', 8, 1, 1, 1, 'main'),
(27, 'caveRef', 'text', 2, 1, 1, 1, 'main'),
(28, 'depth', 'text', 5, 1, 1, 1, 'main'),
(29, 'maxDepth', 'decimal', 6, 1, 1, 1, 'main'),
(30, 'area', 'text', 3, 1, 1, 1, 'main'),
(31, 'topographer', 'text', 31, 1, 1, 1, 'main'),
(55, 'zone_natura_2000', 'bool', 36, 1, 1, 1, 'main'),
(56, 'anchors', 'bool', 30, 1, 1, 1, 'main'),
(57, 'noAccess', 'bool', 33, 1, 1, 1, 'main'),
(58, 'PNR_SB', 'bool', 34, 1, 1, 1, 'main'),
(59, 'documents', 'json', 9999, 1, 0, 1, 'files'),
(61, 'guidv4', 'text', 9999, 0, 0, 0, 'main'),
(62, 'biologyDocuments', 'json', 9999, 1, 0, 1, 'files'),
(63, 'json_coords', 'json', 9999, 0, 0, 1, 'coords'),
(65, 'cave_maps', 'json', 1, 0, 0, 1, 'files'),
(67, 'random_coordinates', 'bool', 35, 1, 1, 1, 'main'),
(68, 'coords_GPS_checked', 'bool', 32, 1, 0, 1, 'main'),
(69, 'photos', 'json', 9999, 0, 0, 1, 'files'),
(70, 'sketch_access', 'json', 20, 0, 0, 1, 'files');

-- --------------------------------------------------------

--
-- Structure de la table `files_ressources`
--

CREATE TABLE `files_ressources` (
  `indexid` int(11) NOT NULL,
  `display_name` varchar(254) NOT NULL,
  `display_group` tinytext NOT NULL,
  `filepath` varchar(511) NOT NULL,
  `description` varchar(2047) NOT NULL,
  `creation_date` int(11) NOT NULL,
  `access_rights` varchar(2047) NOT NULL,
  `creator` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déclencheurs `files_ressources`
--
DELIMITER $$
CREATE TRIGGER `files_ressources_insCreation_date` BEFORE INSERT ON `files_ressources` FOR EACH ROW SET new.creation_date = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

CREATE TABLE `groups` (
  `indexid` int(11) NOT NULL,
  `groupName` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `groups`
--

INSERT INTO `groups` (`indexid`, `groupName`, `description`) VALUES
(1, 'admin', 'Administrateurs du site'),
(3, 'anonymous', 'Groupe anonyme (utilisateurs non autentifiés)'),
(4, 'users', 'Utilisateurs authentifiés du site'),
(5, 'editors', 'Ce groupe permet d\'éditer les fiches de cavités'),
(6, 'news', 'Ce groupe permet d\'éditer les informations diffusées en page d\'accueil');

-- --------------------------------------------------------

--
-- Structure de la table `lists`
--

CREATE TABLE `lists` (
  `indexid` int(11) NOT NULL,
  `list_item` varchar(64) NOT NULL,
  `list_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `lists`
--

INSERT INTO `lists` (`indexid`, `list_item`, `list_name`) VALUES
(1, 'GEOGRAPHIC', 'list_coordinates_systems'),
(2, 'UTM', 'list_coordinates_systems'),
(3, 'googlemaps', 'default_geo_api'),
(4, 'geoportail', 'default_geo_api'),
(5, 'LAMBERT3', 'list_coordinates_systems'),
(6, 'Europe/Amsterdam', 'timezone'),
(7, 'Europe/Andorra', 'timezone'),
(8, 'Europe/Astrakhan', 'timezone'),
(9, 'Europe/Athens', 'timezone'),
(10, 'Europe/Belgrade', 'timezone'),
(11, 'Europe/Berlin', 'timezone'),
(12, 'Europe/Bratislava', 'timezone'),
(13, 'Europe/Brussels', 'timezone'),
(14, 'Europe/Bucharest', 'timezone'),
(15, 'Europe/Budapest', 'timezone'),
(16, 'Europe/Busingen', 'timezone'),
(17, 'Europe/Chisinau', 'timezone'),
(18, 'Europe/Copenhagen', 'timezone'),
(19, 'Europe/Dublin', 'timezone'),
(20, 'Europe/Gibraltar', 'timezone'),
(21, 'Europe/Guernsey', 'timezone'),
(22, 'Europe/Helsinki', 'timezone'),
(23, 'Europe/Isle_of_Man', 'timezone'),
(24, 'Europe/Istanbul', 'timezone'),
(25, 'Europe/Jersey', 'timezone'),
(26, 'Europe/Kaliningrad', 'timezone'),
(27, 'Europe/Kiev', 'timezone'),
(28, 'Europe/Kirov', 'timezone'),
(29, 'Europe/Lisbon', 'timezone'),
(30, 'Europe/Ljubljana', 'timezone'),
(31, 'Europe/London', 'timezone'),
(32, 'Europe/Luxembourg', 'timezone'),
(33, 'Europe/Madrid', 'timezone'),
(34, 'Europe/Malta', 'timezone'),
(35, 'Europe/Mariehamn', 'timezone'),
(36, 'Europe/Minsk', 'timezone'),
(37, 'Europe/Monaco', 'timezone'),
(38, 'Europe/Moscow', 'timezone'),
(39, 'Europe/Oslo', 'timezone'),
(40, 'Europe/Paris', 'timezone'),
(41, 'Europe/Podgorica', 'timezone'),
(42, 'Europe/Prague', 'timezone'),
(43, 'Europe/Riga', 'timezone'),
(44, 'Europe/Rome', 'timezone'),
(45, 'Europe/Samara', 'timezone'),
(46, 'Europe/San_Marino', 'timezone'),
(47, 'Europe/Sarajevo', 'timezone'),
(48, 'Europe/Saratov', 'timezone'),
(49, 'Europe/Simferopol', 'timezone'),
(50, 'Europe/Skopje', 'timezone'),
(51, 'Europe/Sofia', 'timezone'),
(52, 'Europe/Stockholm', 'timezone'),
(53, 'Europe/Tallinn', 'timezone'),
(54, 'Europe/Tirane', 'timezone'),
(55, 'Europe/Ulyanovsk', 'timezone'),
(56, 'Europe/Uzhgorod', 'timezone'),
(57, 'Europe/Vaduz', 'timezone'),
(58, 'Europe/Vatican', 'timezone'),
(59, 'Europe/Vienna', 'timezone'),
(60, 'Europe/Vilnius', 'timezone'),
(61, 'Europe/Volgograd', 'timezone'),
(62, 'Europe/Warsaw', 'timezone'),
(63, 'Europe/Zagreb', 'timezone'),
(64, 'Europe/Zaporozhye', 'timezone'),
(65, 'Europe/Zurich', 'timezone');

-- --------------------------------------------------------

--
-- Structure de la table `list_coordinates_systems`
--

CREATE TABLE `list_coordinates_systems` (
  `indexid` int(11) NOT NULL,
  `indexid_lists` int(11) NOT NULL,
  `php_lib_filename` varchar(64) NOT NULL DEFAULT '0',
  `js_lib_filename` varchar(64) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `list_coordinates_systems`
--

INSERT INTO `list_coordinates_systems` (`indexid`, `indexid_lists`, `php_lib_filename`, `js_lib_filename`) VALUES
(1, 1, 'geoconv_longlatdef.php', 'geoconv_longlatdef.js'),
(2, 2, 'geoconv_longlat2utm.php', 'geoconv_longlat2utm.js'),
(3, 5, 'geoconv_lambert3.php', 'geoconv_lambert3.js');

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `indexid` int(8) NOT NULL,
  `creator` int(11) NOT NULL,
  `last_editor` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `title` text NOT NULL,
  `creation_date` int(11) NOT NULL,
  `edit_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`indexid`, `creator`, `last_editor`, `deleted`, `content`, `title`, `creation_date`, `edit_date`) VALUES
(19, 1, NULL, 0, '<p>Your Varcave installation is now ready, you can add an ACL and do not forget to adjust the ACLs !</p>', 'Welcome !', 1594841782, 0);

--
-- Déclencheurs `news`
--
DELIMITER $$
CREATE TRIGGER `news_insCreationDate` BEFORE INSERT ON `news` FOR EACH ROW SET new.creation_date = UNIX_TIMESTAMP(), new.edit_date = 0
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `news_updEditdate` BEFORE UPDATE ON `news` FOR EACH ROW SET new.edit_date  = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `stats`
--

CREATE TABLE `stats` (
  `indexid` int(11) NOT NULL,
  `cave_id` int(11) NOT NULL,
  `view_count` int(11) NOT NULL,
  `lastupdate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `stats`
--

INSERT INTO `stats` (`indexid`, `cave_id`, `view_count`, `lastupdate`) VALUES
(1, 1, 0, 1594842900);

--
-- Déclencheurs `stats`
--
DELIMITER $$
CREATE TRIGGER `stats_insLastupdate` BEFORE INSERT ON `stats` FOR EACH ROW SET new.lastupdate = UNIX_TIMESTAMP()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stats_updLastupdate` BEFORE UPDATE ON `stats` FOR EACH ROW SET new.lastupdate = UNIX_TIMESTAMP()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `indexid` int(11) NOT NULL,
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
  `uiLanguage` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`indexid`, `username`, `groups`, `password`, `expire`, `created`, `lastUpdate`, `firstname`, `lastname`, `theme`, `geo_api`, `last_php_session`, `datatablesMaxItems`, `pref_coord_system`, `disabled`, `emailaddr`, `streetNum`, `address1`, `address2`, `postCode`, `town`, `country`, `licenceNumber`, `phoneNum`, `cavingGroup`, `notes`, `uiLanguage`) VALUES
(1, 'admin', 'admin,users', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 1767225599, 1330000522, 1594842955, 'Administrator', '', '', 'googlemaps', '8ekbfdegejj6h5ejiq37k23p5l', 0, 'UTM', 0, '', '0', '', '', '0', '', '', '', '', '', '', '');

--
-- Déclencheurs `users`
--
DELIMITER $$
CREATE TRIGGER `users_insLastUpdate` BEFORE INSERT ON `users` FOR EACH ROW SET
               new.lastUpdate = UNIX_TIMESTAMP(),
               new.created = UNIX_TIMESTAMP()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `users_updLastUpdate` BEFORE UPDATE ON `users` FOR EACH ROW SET new.lastUpdate = UNIX_TIMESTAMP()
$$
DELIMITER ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`indexid`);

--
-- Index pour la table `caves`
--
ALTER TABLE `caves`
  ADD PRIMARY KEY (`indexid`),
  ADD KEY `indexid` (`indexid`);

--
-- Index pour la table `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`indexid`),
  ADD KEY `indexid_caves` (`indexid_caves`);

--
-- Index pour la table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`configIndexid`),
  ADD UNIQUE KEY `configItem` (`configItem`);

--
-- Index pour la table `dbversion`
--
ALTER TABLE `dbversion`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `end_user_fields`
--
ALTER TABLE `end_user_fields`
  ADD PRIMARY KEY (`indexid`);

--
-- Index pour la table `files_ressources`
--
ALTER TABLE `files_ressources`
  ADD PRIMARY KEY (`indexid`);

--
-- Index pour la table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`indexid`),
  ADD UNIQUE KEY `groupName` (`groupName`);

--
-- Index pour la table `lists`
--
ALTER TABLE `lists`
  ADD PRIMARY KEY (`indexid`);

--
-- Index pour la table `list_coordinates_systems`
--
ALTER TABLE `list_coordinates_systems`
  ADD PRIMARY KEY (`indexid`),
  ADD KEY `fk_lists_coordsys` (`indexid_lists`);

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`indexid`),
  ADD KEY `fk_creator` (`creator`),
  ADD KEY `fk_lastEditor` (`last_editor`);

--
-- Index pour la table `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`indexid`),
  ADD UNIQUE KEY `cave_id` (`cave_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`indexid`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `acl`
--
ALTER TABLE `acl`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `caves`
--
ALTER TABLE `caves`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `changelog`
--
ALTER TABLE `changelog`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `config`
--
ALTER TABLE `config`
  MODIFY `configIndexid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `dbversion`
--
ALTER TABLE `dbversion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `end_user_fields`
--
ALTER TABLE `end_user_fields`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `files_ressources`
--
ALTER TABLE `files_ressources`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `groups`
--
ALTER TABLE `groups`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `lists`
--
ALTER TABLE `lists`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `list_coordinates_systems`
--
ALTER TABLE `list_coordinates_systems`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `indexid` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `stats`
--
ALTER TABLE `stats`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `indexid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `changelog`
--
ALTER TABLE `changelog`
  ADD CONSTRAINT `indexidCaves` FOREIGN KEY (`indexid_caves`) REFERENCES `caves` (`indexid`);

--
-- Contraintes pour la table `list_coordinates_systems`
--
ALTER TABLE `list_coordinates_systems`
  ADD CONSTRAINT `fk_lists_coordsys` FOREIGN KEY (`indexid_lists`) REFERENCES `lists` (`indexid`);

--
-- Contraintes pour la table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`indexid`),
  ADD CONSTRAINT `fk_lastEditor` FOREIGN KEY (`last_editor`) REFERENCES `users` (`indexid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
