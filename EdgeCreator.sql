-- MySQL dump 10.13  Distrib 5.5.24, for Win32 (x86)
--
-- Host: localhost    Database: db301759616
-- ------------------------------------------------------
-- Server version	5.5.24-log

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
-- Table structure for table `edgecreator_droits`
--

DROP TABLE IF EXISTS `edgecreator_droits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edgecreator_droits` (
  `username` varchar(25) COLLATE latin1_german2_ci NOT NULL,
  `privilege` enum('Admin','Edition','Affichage') COLLATE latin1_german2_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edgecreator_intervalles`
--

DROP TABLE IF EXISTS `edgecreator_intervalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edgecreator_intervalles` (
  `ID_Valeur` int(10) NOT NULL,
  `Numero_debut` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `Numero_fin` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `username` varchar(25) COLLATE latin1_german2_ci NOT NULL,
  KEY `Index 1` (`ID_Valeur`,`Numero_debut`,`Numero_fin`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edgecreator_modeles2`
--

DROP TABLE IF EXISTS `edgecreator_modeles2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edgecreator_modeles2` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Ordre` float NOT NULL,
  `Nom_fonction` varchar(30) COLLATE latin1_german2_ci NOT NULL,
  `Option_nom` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=36233 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `edgecreator_modeles_vue`
--

DROP TABLE IF EXISTS `edgecreator_modeles_vue`;
/*!50001 DROP VIEW IF EXISTS `edgecreator_modeles_vue`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `edgecreator_modeles_vue` (
  `Pays` varchar(3),
  `Magazine` varchar(6),
  `Ordre` float,
  `Nom_fonction` varchar(30),
  `Option_nom` varchar(20),
  `ID` int(11),
  `ID_Valeur` int(10),
  `Option_valeur` varchar(200),
  `Numero_debut` varchar(10),
  `Numero_fin` varchar(10),
  `username` varchar(25)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `edgecreator_valeurs`
--

DROP TABLE IF EXISTS `edgecreator_valeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edgecreator_valeurs` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `ID_Option` int(10) DEFAULT NULL,
  `Option_valeur` varchar(200) COLLATE latin1_german2_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=32682 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images_myfonts`
--

DROP TABLE IF EXISTS `images_myfonts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images_myfonts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Font` varchar(150) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Color` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `ColorBG` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Width` varchar(7) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Texte` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `Precision_` varchar(5) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1758 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tranches_en_cours_modeles`
--

DROP TABLE IF EXISTS `tranches_en_cours_modeles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranches_en_cours_modeles` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `username` varchar(25) COLLATE latin1_german2_ci DEFAULT NULL,
  `NomPhotoPrincipale` varchar(60) COLLATE latin1_german2_ci DEFAULT NULL,
  `photographes` text COLLATE latin1_german2_ci,
  `createurs` text COLLATE latin1_german2_ci,
  `Active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `PretePourPublication` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `Index 2` (`Pays`,`Magazine`,`Numero`,`username`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `tranches_en_cours_modeles_vue`
--

DROP TABLE IF EXISTS `tranches_en_cours_modeles_vue`;
/*!50001 DROP VIEW IF EXISTS `tranches_en_cours_modeles_vue`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `tranches_en_cours_modeles_vue` (
  `username` varchar(25),
  `Pays` varchar(3),
  `Magazine` varchar(6),
  `Active` tinyint(3) unsigned,
  `Numero` varchar(10),
  `Ordre` float,
  `Nom_fonction` varchar(30),
  `Option_nom` varchar(30),
  `Option_valeur` varchar(200),
  `ID_Modele` int(10) unsigned,
  `ID_Valeur` int(10) unsigned
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tranches_en_cours_valeurs`
--

DROP TABLE IF EXISTS `tranches_en_cours_valeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranches_en_cours_valeurs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Ordre` float NOT NULL,
  `Nom_fonction` varchar(30) COLLATE latin1_german2_ci NOT NULL,
  `Option_nom` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Option_valeur` varchar(200) COLLATE latin1_german2_ci DEFAULT NULL,
  `ID_Modele` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_Modele` (`ID_Modele`),
  CONSTRAINT `ID_Modele` FOREIGN KEY (`ID_Modele`) REFERENCES `tranches_en_cours_modeles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2084 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `edgecreator_modeles_vue`
--

/*!50001 DROP TABLE IF EXISTS `edgecreator_modeles_vue`*/;
/*!50001 DROP VIEW IF EXISTS `edgecreator_modeles_vue`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `edgecreator_modeles_vue` AS select `ducksmanager`.`edgecreator_modeles2`.`Pays` AS `Pays`,`ducksmanager`.`edgecreator_modeles2`.`Magazine` AS `Magazine`,`ducksmanager`.`edgecreator_modeles2`.`Ordre` AS `Ordre`,`ducksmanager`.`edgecreator_modeles2`.`Nom_fonction` AS `Nom_fonction`,`ducksmanager`.`edgecreator_modeles2`.`Option_nom` AS `Option_nom`,`ducksmanager`.`edgecreator_modeles2`.`ID` AS `ID`,`ducksmanager`.`edgecreator_valeurs`.`ID` AS `ID_Valeur`,`ducksmanager`.`edgecreator_valeurs`.`Option_valeur` AS `Option_valeur`,`ducksmanager`.`edgecreator_intervalles`.`Numero_debut` AS `Numero_debut`,`ducksmanager`.`edgecreator_intervalles`.`Numero_fin` AS `Numero_fin`,`ducksmanager`.`edgecreator_intervalles`.`username` AS `username` from ((`ducksmanager`.`edgecreator_modeles2` join `ducksmanager`.`edgecreator_valeurs` on((`ducksmanager`.`edgecreator_modeles2`.`ID` = `ducksmanager`.`edgecreator_valeurs`.`ID_Option`))) join `ducksmanager`.`edgecreator_intervalles` on((`ducksmanager`.`edgecreator_valeurs`.`ID` = `ducksmanager`.`edgecreator_intervalles`.`ID_Valeur`))) order by `ducksmanager`.`edgecreator_modeles2`.`Pays`,`ducksmanager`.`edgecreator_modeles2`.`Magazine`,`ducksmanager`.`edgecreator_modeles2`.`Ordre`,`ducksmanager`.`edgecreator_modeles2`.`Option_nom`,`ducksmanager`.`edgecreator_intervalles`.`Numero_debut` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tranches_en_cours_modeles_vue`
--

/*!50001 DROP TABLE IF EXISTS `tranches_en_cours_modeles_vue`*/;
/*!50001 DROP VIEW IF EXISTS `tranches_en_cours_modeles_vue`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tranches_en_cours_modeles_vue` AS select `tranches_en_cours_modeles`.`username` AS `username`,`tranches_en_cours_modeles`.`Pays` AS `Pays`,`tranches_en_cours_modeles`.`Magazine` AS `Magazine`,`tranches_en_cours_modeles`.`Active` AS `Active`,`tranches_en_cours_modeles`.`Numero` AS `Numero`,`tranches_en_cours_valeurs`.`Ordre` AS `Ordre`,`tranches_en_cours_valeurs`.`Nom_fonction` AS `Nom_fonction`,`tranches_en_cours_valeurs`.`Option_nom` AS `Option_nom`,`tranches_en_cours_valeurs`.`Option_valeur` AS `Option_valeur`,`tranches_en_cours_valeurs`.`ID_Modele` AS `ID_Modele`,`tranches_en_cours_valeurs`.`ID` AS `ID_Valeur` from (`tranches_en_cours_modeles` join `tranches_en_cours_valeurs` on((`tranches_en_cours_modeles`.`ID` = `tranches_en_cours_valeurs`.`ID_Modele`))) order by `tranches_en_cours_modeles`.`Pays`,`tranches_en_cours_modeles`.`Magazine`,`tranches_en_cours_valeurs`.`Ordre`,`tranches_en_cours_valeurs`.`Nom_fonction`,`tranches_en_cours_valeurs`.`Option_nom`,`tranches_en_cours_valeurs`.`Option_valeur` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-12-08 14:07:56
