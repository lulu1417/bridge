-- MySQL dump 10.13  Distrib 5.7.27, for Linux (x86_64)
--
-- Host: localhost    Database: bridge
-- ------------------------------------------------------
-- Server version	5.7.27-0ubuntu0.18.04.1

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
-- Table structure for table `bids`
--

DROP TABLE IF EXISTS `bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bids` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trump` int(11) NOT NULL,
  `line` int(11) NOT NULL,
  `isPass` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bids`
--

LOCK TABLES `bids` WRITE;
/*!40000 ALTER TABLE `bids` DISABLE KEYS */;
INSERT INTO `bids` VALUES (1,'lulu2',300,2,0,'2019-12-04 21:40:51','2019-12-04 21:40:51'),(2,'lulu',400,2,0,'2019-12-04 21:41:03','2019-12-04 21:41:03'),(3,'lulu2',300,3,0,'2019-12-04 21:48:32','2019-12-04 21:48:32'),(4,'lulu',300,3,1,'2019-12-04 21:50:12','2019-12-04 21:50:12');
/*!40000 ALTER TABLE `bids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards`
--

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` VALUES (1,'lulu','200','9','2019-12-04 21:34:42','2019-12-04 21:34:42'),(2,'lulu','400','8','2019-12-04 21:34:42','2019-12-04 21:34:42'),(3,'lulu','200','11','2019-12-04 21:34:42','2019-12-04 21:34:42'),(4,'discard','100','13','2019-12-04 21:34:42','2019-12-04 22:02:20'),(5,'lulu','100','3','2019-12-04 21:34:42','2019-12-04 21:34:42'),(6,'lulu','400','11','2019-12-04 21:34:42','2019-12-04 21:34:42'),(7,'lulu','100','14','2019-12-04 21:34:42','2019-12-04 21:34:42'),(8,'lulu','400','3','2019-12-04 21:34:42','2019-12-04 21:34:42'),(9,'lulu','200','10','2019-12-04 21:34:42','2019-12-04 21:34:42'),(10,'lulu','400','5','2019-12-04 21:34:42','2019-12-04 21:34:42'),(11,'discard','100','11','2019-12-04 21:34:42','2019-12-04 21:56:43'),(12,'lulu','300','9','2019-12-04 21:34:42','2019-12-04 21:34:42'),(13,'lulu','200','14','2019-12-04 21:34:42','2019-12-04 21:34:42'),(14,'discard','100','2','2019-12-04 21:34:42','2019-12-04 22:03:07'),(15,'discard','100','10','2019-12-04 21:34:42','2019-12-04 21:50:53'),(16,'lulu2','300','5','2019-12-04 21:34:42','2019-12-04 21:34:42'),(17,'lulu2','300','10','2019-12-04 21:34:42','2019-12-04 21:34:42'),(18,'lulu2','300','14','2019-12-04 21:34:42','2019-12-04 21:34:42'),(19,'lulu2','100','6','2019-12-04 21:34:42','2019-12-04 21:34:42'),(20,'lulu2','200','13','2019-12-04 21:34:42','2019-12-04 21:34:42'),(21,'lulu2','200','3','2019-12-04 21:34:42','2019-12-04 21:34:42'),(22,'lulu2','300','12','2019-12-04 21:34:42','2019-12-04 21:34:42'),(23,'lulu2','100','9','2019-12-04 21:34:42','2019-12-04 21:34:42'),(24,'lulu2','400','2','2019-12-04 21:34:42','2019-12-04 21:34:42'),(25,'lulu2','200','7','2019-12-04 21:34:42','2019-12-04 21:34:42'),(26,'lulu2','400','12','2019-12-04 21:34:42','2019-12-04 21:34:42'),(27,'lulu','400','6','2019-12-04 21:34:42','2019-12-04 21:56:43'),(28,'lulu2','300','8','2019-12-04 21:34:42','2019-12-04 21:56:43'),(29,'lulu2','300','13','2019-12-04 21:34:42','2019-12-04 21:56:43'),(30,'lulu','200','12','2019-12-04 21:34:43','2019-12-04 22:03:07'),(31,'lulu2','100','12','2019-12-04 21:34:43','2019-12-04 22:03:07'),(32,'pile','200','4','2019-12-04 21:34:43','2019-12-04 21:34:43'),(33,'pile','100','8','2019-12-04 21:34:43','2019-12-04 21:34:43'),(34,'pile','400','13','2019-12-04 21:34:43','2019-12-04 21:34:43'),(35,'pile','300','3','2019-12-04 21:34:43','2019-12-04 21:34:43'),(36,'pile','400','9','2019-12-04 21:34:43','2019-12-04 21:34:43'),(37,'pile','400','10','2019-12-04 21:34:43','2019-12-04 21:34:43'),(38,'pile','300','11','2019-12-04 21:34:43','2019-12-04 21:34:43'),(39,'pile','300','2','2019-12-04 21:34:43','2019-12-04 21:34:43'),(40,'pile','300','4','2019-12-04 21:34:43','2019-12-04 21:34:43'),(41,'pile','400','4','2019-12-04 21:34:43','2019-12-04 21:34:43'),(42,'pile','100','4','2019-12-04 21:34:43','2019-12-04 21:34:43'),(43,'pile','200','6','2019-12-04 21:34:43','2019-12-04 21:34:43'),(44,'pile','200','8','2019-12-04 21:34:43','2019-12-04 21:34:43'),(45,'pile','300','7','2019-12-04 21:34:43','2019-12-04 21:34:43'),(46,'pile','300','6','2019-12-04 21:34:43','2019-12-04 21:34:43'),(47,'pile','200','5','2019-12-04 21:34:43','2019-12-04 21:34:43'),(48,'pile','400','14','2019-12-04 21:34:43','2019-12-04 21:34:43'),(49,'pile','400','7','2019-12-04 21:34:43','2019-12-04 21:34:43'),(50,'pile','100','5','2019-12-04 21:34:43','2019-12-04 21:34:43'),(51,'pile','100','7','2019-12-04 21:34:43','2019-12-04 21:34:43'),(52,'pile','200','2','2019-12-04 21:34:43','2019-12-04 21:34:43');
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compares`
--

DROP TABLE IF EXISTS `compares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compares` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `round` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compares`
--

LOCK TABLES `compares` WRITE;
/*!40000 ALTER TABLE `compares` DISABLE KEYS */;
INSERT INTO `compares` VALUES (1,1,'lulu2','100','10','0','2019-12-04 21:50:53','2019-12-04 21:56:44'),(2,1,'lulu','100','11','1','2019-12-04 21:56:43','2019-12-04 21:56:44'),(3,2,'lulu','100','13','1','2019-12-04 22:02:20','2019-12-04 22:03:07'),(4,2,'lulu2','100','2','0','2019-12-04 22:03:07','2019-12-04 22:03:07');
/*!40000 ALTER TABLE `compares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (46,'2019_11_21_070046_create_players_table',1),(47,'2019_11_21_073033_create_bids_table',1),(48,'2019_11_21_083233_create_compares_table',1),(49,'2019_11_22_021409_create_cards_table',1),(50,'2019_11_29_021928_create_rooms_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal` int(11) DEFAULT NULL,
  `trick` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES (1,'lulu','123',5,0,'2019-12-04 21:34:30','2019-12-04 21:50:12'),(2,'lulu2','123',9,0,'2019-12-04 21:34:41','2019-12-04 21:50:12');
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-05 14:10:34
