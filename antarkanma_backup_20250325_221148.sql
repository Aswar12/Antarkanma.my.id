-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: antarkanma
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('shipping_calculation_1','a:4:{s:4:\"data\";a:5:{s:20:\"total_shipping_price\";i:10000;s:19:\"merchant_deliveries\";a:1:{i:0;a:8:{s:11:\"merchant_id\";i:11;s:13:\"merchant_name\";s:14:\"Warung Hardini\";s:8:\"distance\";d:8.5;s:8:\"duration\";d:6;s:4:\"cost\";i:10000;s:10:\"route_type\";s:13:\"base_merchant\";s:10:\"route_info\";a:3:{s:5:\"angle\";d:282.3606156287746;s:8:\"group_id\";s:7:\"group_0\";s:7:\"is_base\";b:1;}s:14:\"cost_breakdown\";a:1:{s:9:\"base_cost\";i:10000;}}}s:13:\"route_summary\";a:2:{s:15:\"total_merchants\";i:1;s:16:\"direction_groups\";a:1:{i:0;a:5:{s:8:\"group_id\";s:7:\"group_0\";s:10:\"base_angle\";d:282.3606156287746;s:9:\"merchants\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;s:14:\"Warung Hardini\";}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"total_cost\";i:10000;s:14:\"cost_breakdown\";a:2:{s:13:\"base_merchant\";a:3:{s:4:\"name\";s:14:\"Warung Hardini\";s:8:\"distance\";d:8.5;s:4:\"cost\";i:10000;}s:10:\"on_the_way\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}}}}s:15:\"cost_comparison\";a:3:{s:15:\"if_single_order\";a:2:{s:5:\"total\";i:10000;s:9:\"breakdown\";s:22:\"Warung Hardini (10000)\";}s:18:\"if_separate_orders\";a:2:{s:5:\"total\";i:15000;s:9:\"breakdown\";s:22:\"Warung Hardini (15000)\";}s:7:\"savings\";a:2:{s:6:\"amount\";i:5000;s:11:\"explanation\";s:35:\"Hemat Rp 5,000 dengan optimasi rute\";}}s:15:\"recommendations\";N;}s:10:\"expires_at\";s:19:\"2025-03-21 17:42:18\";s:5:\"items\";a:1:{i:0;a:2:{s:10:\"product_id\";i:7;s:8:\"quantity\";i:1;}}s:10:\"created_at\";s:19:\"2025-03-21 17:12:18\";}',1742550138),('shipping_calculation_14','a:4:{s:4:\"data\";a:5:{s:20:\"total_shipping_price\";i:24000;s:19:\"merchant_deliveries\";a:1:{i:0;a:8:{s:11:\"merchant_id\";i:11;s:13:\"merchant_name\";s:14:\"Warung Hardini\";s:8:\"distance\";d:13287.45;s:8:\"duration\";d:39862;s:4:\"cost\";i:24000;s:10:\"route_type\";s:13:\"base_merchant\";s:10:\"route_info\";a:3:{s:5:\"angle\";d:2.1910900320582436;s:8:\"group_id\";s:7:\"group_0\";s:7:\"is_base\";b:1;}s:14:\"cost_breakdown\";a:1:{s:9:\"base_cost\";i:24000;}}}s:13:\"route_summary\";a:2:{s:15:\"total_merchants\";i:1;s:16:\"direction_groups\";a:1:{i:0;a:5:{s:8:\"group_id\";s:7:\"group_0\";s:10:\"base_angle\";d:2.1910900320582436;s:9:\"merchants\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;s:14:\"Warung Hardini\";}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"total_cost\";i:24000;s:14:\"cost_breakdown\";a:2:{s:13:\"base_merchant\";a:3:{s:4:\"name\";s:14:\"Warung Hardini\";s:8:\"distance\";d:13287.45;s:4:\"cost\";i:24000;}s:10:\"on_the_way\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}}}}s:15:\"cost_comparison\";a:3:{s:15:\"if_single_order\";a:2:{s:5:\"total\";i:24000;s:9:\"breakdown\";s:22:\"Warung Hardini (24000)\";}s:18:\"if_separate_orders\";a:2:{s:5:\"total\";i:25000;s:9:\"breakdown\";s:22:\"Warung Hardini (25000)\";}s:7:\"savings\";a:2:{s:6:\"amount\";i:1000;s:11:\"explanation\";s:35:\"Hemat Rp 1,000 dengan optimasi rute\";}}s:15:\"recommendations\";N;}s:10:\"expires_at\";s:19:\"2025-03-21 17:37:13\";s:5:\"items\";a:1:{i:0;a:2:{s:10:\"product_id\";i:7;s:8:\"quantity\";i:1;}}s:10:\"created_at\";s:19:\"2025-03-21 17:07:13\";}',1742549833),('shipping_calculation_4','a:4:{s:4:\"data\";a:5:{s:20:\"total_shipping_price\";i:5000;s:19:\"merchant_deliveries\";a:1:{i:0;a:8:{s:11:\"merchant_id\";i:12;s:13:\"merchant_name\";s:23:\"NasGor Jakarta Mas Rudy\";s:8:\"distance\";d:0.36;s:8:\"duration\";d:0;s:4:\"cost\";i:5000;s:10:\"route_type\";s:13:\"base_merchant\";s:10:\"route_info\";a:3:{s:5:\"angle\";d:290.6615410996778;s:8:\"group_id\";s:7:\"group_0\";s:7:\"is_base\";b:1;}s:14:\"cost_breakdown\";a:1:{s:9:\"base_cost\";i:5000;}}}s:13:\"route_summary\";a:2:{s:15:\"total_merchants\";i:1;s:16:\"direction_groups\";a:1:{i:0;a:5:{s:8:\"group_id\";s:7:\"group_0\";s:10:\"base_angle\";d:290.6615410996778;s:9:\"merchants\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;s:23:\"NasGor Jakarta Mas Rudy\";}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"total_cost\";i:5000;s:14:\"cost_breakdown\";a:2:{s:13:\"base_merchant\";a:3:{s:4:\"name\";s:23:\"NasGor Jakarta Mas Rudy\";s:8:\"distance\";d:0.36;s:4:\"cost\";i:5000;}s:10:\"on_the_way\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}}}}s:15:\"cost_comparison\";a:3:{s:15:\"if_single_order\";a:2:{s:5:\"total\";i:5000;s:9:\"breakdown\";s:30:\"NasGor Jakarta Mas Rudy (5000)\";}s:18:\"if_separate_orders\";a:2:{s:5:\"total\";i:7000;s:9:\"breakdown\";s:30:\"NasGor Jakarta Mas Rudy (7000)\";}s:7:\"savings\";a:2:{s:6:\"amount\";i:2000;s:11:\"explanation\";s:35:\"Hemat Rp 2,000 dengan optimasi rute\";}}s:15:\"recommendations\";N;}s:10:\"expires_at\";s:19:\"2025-03-22 21:35:40\";s:5:\"items\";a:1:{i:0;a:2:{s:10:\"product_id\";i:13;s:8:\"quantity\";i:1;}}s:10:\"created_at\";s:19:\"2025-03-22 21:05:40\";}',1742650540);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courier_batches`
--

DROP TABLE IF EXISTS `courier_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courier_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `courier_id` bigint unsigned NOT NULL,
  `status` enum('PREPARING','IN_PROGRESS','COMPLETED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courier_batches_courier_id_foreign` (`courier_id`),
  CONSTRAINT `courier_batches_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courier_batches`
--

LOCK TABLES `courier_batches` WRITE;
/*!40000 ALTER TABLE `courier_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `courier_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `couriers`
--

DROP TABLE IF EXISTS `couriers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `couriers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_plate` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `wallet_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee_per_order` decimal(10,2) NOT NULL DEFAULT '2000.00',
  `is_wallet_active` tinyint(1) NOT NULL DEFAULT '1',
  `minimum_balance` decimal(10,2) NOT NULL DEFAULT '10000.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `couriers_user_id_unique` (`user_id`),
  CONSTRAINT `couriers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `couriers`
--

LOCK TABLES `couriers` WRITE;
/*!40000 ALTER TABLE `couriers` DISABLE KEYS */;
INSERT INTO `couriers` VALUES (1,20,'Motor','DD 1234 XX','2025-03-22 12:46:02','2025-03-22 12:46:02',100000.00,2000.00,1,10000.00);
/*!40000 ALTER TABLE `couriers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `courier_id` bigint unsigned NOT NULL,
  `delivery_status` enum('PENDING','IN_PROGRESS','DELIVERED','CANCELED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_delivery_time` datetime NOT NULL,
  `actual_delivery_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveries_transaction_id_foreign` (`transaction_id`),
  KEY `deliveries_courier_id_foreign` (`courier_id`),
  CONSTRAINT `deliveries_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`),
  CONSTRAINT `deliveries_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_items`
--

DROP TABLE IF EXISTS `delivery_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `pickup_status` enum('PENDING','PICKED_UP') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_items_delivery_id_foreign` (`delivery_id`),
  KEY `delivery_items_order_item_id_foreign` (`order_item_id`),
  CONSTRAINT `delivery_items_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`),
  CONSTRAINT `delivery_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_items`
--

LOCK TABLES `delivery_items` WRITE;
/*!40000 ALTER TABLE `delivery_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fcm_tokens`
--

DROP TABLE IF EXISTS `fcm_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcm_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'android',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fcm_tokens_token_unique` (`token`),
  KEY `fcm_tokens_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `fcm_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fcm_tokens`
--

LOCK TABLES `fcm_tokens` WRITE;
/*!40000 ALTER TABLE `fcm_tokens` DISABLE KEYS */;
INSERT INTO `fcm_tokens` VALUES (1,1,'fomoiVzSQfWYNmKjHaV_zT:APA91bEKQ_ITBffEVZPT6yOj6NbZ7zywNDIrK7KDuPPqz-W6WNbHZIr9Yk94TEHBmTo8RSDxaea7nsnbPUqHUn3gvi-l7QQoaFqjYFVW5y3AZeWA5L0eH2U','android',1,'2025-03-18 04:08:32','2025-03-18 04:08:32'),(2,2,'fsSwo-rMSKmK7CTaPy8qop:APA91bFiP0-pivN5bw39BRcTZt87-gscAl9TSVOtW6TYB0PTA8heiCa1sqPsXQgu66pUvn9Ibp9VVPpNJo0Hqhp03Xrd4VsK5hSQnVud3Yl2dE1Elq9XoqA','android',1,'2025-03-18 04:16:44','2025-03-18 04:16:44'),(3,4,'eWsebL2wT6KUiTUAe5M_uX:APA91bH_ghGVttG5Vgk_cDojci2oNqQjfQ3jiXDiLBqFqu8sguoxq4rXuAdpcujI9LbMqPM4_dMCjeB9LxeI7sG5ctSAEZg3aCB_NmscGMwAz9wUlhvrHrY','android',1,'2025-03-18 09:22:43','2025-03-18 09:22:43'),(4,5,'dS1z0HdUSfmgYLVOZRhd-f:APA91bG_NJxoYeDA_fIEYjmpajj2KZFIVmQ7qZtSsD36_GpAPTgQwQ0cCxVQ7924vJSK0EIF9l6AFE_KhG9h0RI00tv2OjgjMaUfbqPBgHWBIZxLCvyigzo','android',1,'2025-03-19 01:43:22','2025-03-19 01:43:22'),(12,12,'fSAQJ_7NTF-vGiRLIm8TxG:APA91bFfFiZezBatjMtVLT8-gWvswqgui5d7HFhUKGKhZdpaV-0heY7bt9iIHMvndM8goqOg29vg81bUwJfHo9GvTXYc7Db9oA2hwCZEnL9hGbaN7S11wcM','android',1,'2025-03-19 10:52:43','2025-03-19 10:52:43'),(21,16,'eOAV2PR9TBSDNxumvWK92f:APA91bGg3qKtdlsEbPASxcx8WGmOO7wgUPyNxwf9NvFLoE2nghCzKMkF7ULlYm9G6SfrNj4vt-b4P3-FVTg1-YQafsfR50IIB0tvieEPv4k7-tcrqzMrYNs','android',1,'2025-03-19 16:17:53','2025-03-19 16:17:53'),(28,2,'dvy3KhAOSp2D-sbhfJajaC:APA91bH2OTuwP1Mqr5MhNoVjsz5im34j6Kx37QcBZ-4C_tINCl1vsNJEy2uFkp1gAHKn5ao4rmWncdtwePUSgiVy_ADRI99hKzBGiURt_ADTNWyphkrSPbQ','android',1,'2025-03-21 08:41:58','2025-03-21 08:41:58'),(51,19,'fG3xeLfzQLCJw15CF3N5Iu:APA91bF-3JJzquh6U13htdNrq4Vtm1H2H80k8QP_fnPPdupRWv044cV29URW_RMlyuAPDV8_2087jmuMKIQdkDuN3v38PlB4RtK8JC6_8Nm1dh2dyrs8iLU','android',1,'2025-03-21 19:00:48','2025-03-21 19:00:48'),(64,20,'dWG8d_7HSfaTNIc_Uu7zBf:APA91bFgM183rNydp5n8HGarZYksKzP8ZwCEaGjOW0MX3UBdHgq6HfLQqC3ts-GLo9YR0aHSdyvg_0YgJZ0JMpjtBAv2HaIId9ZdK1R2A5AZLQxgU8GeD1s','android',1,'2025-03-22 13:08:18','2025-03-22 13:08:18');
/*!40000 ALTER TABLE `fcm_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `points` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_points_user_id_foreign` (`user_id`),
  CONSTRAINT `loyalty_points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_points`
--

LOCK TABLES `loyalty_points` WRITE;
/*!40000 ALTER TABLE `loyalty_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchants`
--

DROP TABLE IF EXISTS `merchants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_open_24_hours` tinyint(1) NOT NULL DEFAULT '0',
  `operating_days` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `merchants_owner_id_foreign` (`owner_id`),
  CONSTRAINT `merchants_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchants`
--

LOCK TABLES `merchants` WRITE;
/*!40000 ALTER TABLE `merchants` DISABLE KEYS */;
INSERT INTO `merchants` VALUES (1,'Koneksi Rasa',2,'segeri',-4.64718391,119.58496723,'087812379186','active','asdasasassd','merchants/logos/merchant-1-1742271423.png',NULL,'2025-03-18 04:15:57','2025-03-18 04:26:06','21:15:00','12:24:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(2,'R.M Empat Lawang',6,'9H4M+5XH, Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.64736225,119.58504691,'082184703675','active',NULL,'merchants/logos/merchant-2-1742381583.png',NULL,'2025-03-19 09:45:28','2025-03-19 10:56:32','08:00:00','12:44:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(4,'Nasi Kuning Banjir',11,'Jl. Kemakmuran No.38, Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan 90655',0.00000000,0.00000000,'081355200321','active','Nasi kuning banjir','merchants/logos/merchant-4-1742381262.png',NULL,'2025-03-19 10:16:47','2025-03-19 10:47:43','00:00:00','12:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(5,'Frozen Food Segeri',12,'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.64691344,119.58539432,'0812345678','active','Jual Aneka Makanan Beku dan Bahan Baku Minuman Kekinian','merchants/logos/merchant-5-1742382295.png',NULL,'2025-03-19 10:59:38','2025-03-19 11:05:01','09:00:00','18:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(6,'Tahu Crispy Segeri',14,'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.64678488,119.58511438,'081234567891','active','Jual Aneka Gorengan -4.646784882053787, 119.58511437504957','merchants/logos/merchant-6-1742383359.png',NULL,'2025-03-19 11:07:30','2025-03-19 11:22:39','14:00:00','22:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(7,'Gorengan Perempatan Segeri',13,'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.64697363,119.58531961,'08123456789','active','-4.646973627704215, 119.5853196063539','merchants/logos/merchant-7-1742383409.png',NULL,'2025-03-19 11:17:44','2025-03-19 11:23:29','10:00:00','22:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(8,'Toko Lima Satu',15,'9H3P+4C7, Jl. Kemakmuran, Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan 90655',-4.64714792,119.58597372,'081243432404','active','Toko 51 adalah toko kelontong dan grosir yang menyediakan berbagai kebutuhan pokok seperti bahan makanan, sembako, serta camilan dengan harga terjangkau. ','merchants/logos/merchant-8-1742401712.png',NULL,'2025-03-19 14:48:35','2025-03-19 16:28:33','08:00:00','22:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(9,'Dapur Marisha',16,'Galla Raya desa Coppo Tompong Kec. Mandalle',-4.58612002,119.61163916,'082195325749','active','-4.586120021349711, 119.61163915662856','merchants/logos/merchant-9-1742401093.png',NULL,'2025-03-19 16:17:04','2025-03-19 16:18:13','08:00:00','22:00:00',0,'[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(10,'OMI BOBA',17,'CJC2+662, Tamarupa, Kec. Mandalle, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.57940282,119.60051297,'085299722242','active','-4.579402819155608, 119.60051297116455',NULL,NULL,'2025-03-20 20:54:24','2025-03-20 20:54:24','09:00:00','12:00:00',0,'[\"monday\", \"wednesday\", \"tuesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(11,'Warung Hardini',18,'Mandalle, Kec. Mandalle, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.57596538,119.60060007,'081355010678','active','-4.575965375861917, 119.60060006715237','merchants/logos/merchant-11-1742538131.png',NULL,'2025-03-21 06:18:47','2025-03-21 06:22:12','08:00:00','22:00:00',0,'[\"tuesday\", \"monday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]',1),(12,'NasGor Jakarta Mas Rudy',19,'Segeri, Kec. Segeri, Kabupaten Pangkajene Dan Kepulauan, Sulawesi Selatan',-4.64633629,119.58533952,'081808670849','active','Jualan Nasi Goreng depan masjid al- Multazam segeri','merchants/logos/merchant-12-1742706459.png',NULL,'2025-03-21 18:46:52','2025-03-23 05:07:40','17:00:00','03:30:00',0,'\"minggu,senin\"',1);
/*!40000 ALTER TABLE `merchants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_02_14_000000_create_fcm_tokens_table',1),(5,'2024_10_20_094403_add_two_factor_columns_to_users_table',1),(6,'2024_10_20_094426_create_personal_access_tokens_table',1),(7,'2024_10_20_101535_add_fields_to_users_table',1),(8,'2024_10_20_101729_create_merchants_table',1),(9,'2024_10_20_101730_add_operating_hours_to_merchants_table',1),(10,'2024_10_20_102058_create_products_table',1),(11,'2024_10_20_102548_create_product_categories_table',1),(12,'2024_10_20_102848_create_product_galleries_table',1),(13,'2024_10_20_103008_create_orders_table',1),(14,'2024_10_20_103009_add_ready_for_pickup_status_to_orders',1),(15,'2024_10_20_111447_create_order_items_table',1),(16,'2024_10_20_111655_create_loyalty_points_table',1),(17,'2024_10_20_112939_create_couriers_table',1),(18,'2024_10_20_113547_create_transactions_table',1),(19,'2024_10_20_114851_create_deliveries_table',1),(20,'2024_10_20_121538_create_product_variants_table',1),(21,'2024_10_20_122931_create_user_locations_table',1),(22,'2024_10_21_103723_create_product_reviews_table',1),(23,'2024_10_21_124857_create_delivery_items_table',1),(24,'2024_10_21_124910_create_courier_batches_table',1),(25,'2024_10_31_083932_update_user_locations_table',1),(26,'2024_10_31_085012_update_address_type_enum_to_indonesian',1),(27,'2024_11_01_000000_add_is_active_to_users_table',1),(28,'2025_01_17_220327_update_order_status_enum_add_waiting_approval_and_picked_up',1),(29,'2025_01_21_180653_add_admin_role_to_users_table',1),(30,'2025_01_22_052601_update_roles_column_to_enum',1),(31,'2025_01_23_122024_add_logo_url_to_merchants_table',1),(32,'2025_01_23_150506_add_courier_approval_and_timeout_to_transactions',1),(33,'2025_01_23_161355_fix_transactions_orders_relationship',1),(34,'2025_02_03_130142_add_coordinates_to_merchants_table',1),(35,'2025_02_18_163947_add_courier_id_to_transactions',1),(36,'2025_02_22_151323_add_base_merchant_id_to_transactions_table',1),(37,'2025_02_28_014115_add_rejection_reason_and_customer_note_to_orders_table',1),(38,'2025_02_28_115148_move_customer_note_to_order_items_table',1),(39,'2025_03_08_080527_add_wallet_fields_to_couriers_table',1),(40,'2025_03_18_152020_add_is_active_to_merchants_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `merchant_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `customer_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,7,NULL,11,1,35000.00,NULL,'2025-03-21 09:08:21','2025-03-21 09:08:21'),(2,2,13,NULL,12,1,15000.00,NULL,'2025-03-22 13:07:25','2025-03-22 13:07:25');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `merchant_id` bigint unsigned NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('PENDING','WAITING_APPROVAL','PROCESSING','READY_FOR_PICKUP','PICKED_UP','COMPLETED','CANCELED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `merchant_approval` enum('PENDING','APPROVED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_transaction_id_foreign` (`transaction_id`),
  KEY `orders_merchant_id_foreign` (`merchant_id`),
  CONSTRAINT `orders_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`),
  CONSTRAINT `orders_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,14,11,35000.00,'CANCELED',NULL,'PENDING','2025-03-21 09:08:21','2025-03-22 12:48:07'),(2,2,4,12,15000.00,'PROCESSING',NULL,'APPROVED','2025-03-22 13:07:25','2025-03-23 04:56:05');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (3,'App\\Models\\User',2,'authToken','dee4ae94dbb44a5a49f9e3af3322c93b1a08b9ef3dbee903a2e71c15b7c774b5','[\"*\"]','2025-03-18 09:57:29',NULL,'2025-03-18 04:16:43','2025-03-18 09:57:29'),(5,'App\\Models\\User',4,'authToken','ae6ac25029bbc89ec3052db12774a455786f51456ba141fca58b211f5ab94fbc','[\"*\"]','2025-03-18 09:22:43',NULL,'2025-03-18 09:22:41','2025-03-18 09:22:43'),(6,'App\\Models\\User',2,'authToken','ef7a08591fd59c9d593475d67b0d14db552bd3182d44cfdfd0f7f68b214358f3','[\"*\"]','2025-03-19 08:58:44',NULL,'2025-03-18 09:57:30','2025-03-19 08:58:44'),(7,'App\\Models\\User',5,'authToken','ad7981bdee8872b49579c4d5a5132a74f20996effab128c22613635c55a5221e','[\"*\"]','2025-03-22 10:55:25',NULL,'2025-03-19 01:43:21','2025-03-22 10:55:25'),(9,'App\\Models\\User',2,'authToken','dbf01df72abd00c447420782c57dfdb145ed7f28c28247ffcfa00b0bc104eed6','[\"*\"]','2025-03-19 08:58:47',NULL,'2025-03-19 08:58:45','2025-03-19 08:58:47'),(14,'App\\Models\\User',9,'authToken','973932dbca94e5a2136248cb7c0a65dbd809b275fce20ad4c904eb4e59786c43','[\"*\"]',NULL,NULL,'2025-03-19 10:06:14','2025-03-19 10:06:14'),(15,'App\\Models\\User',9,'authToken','b9c8f1c0baa53cf510f761b8ea6254f88cd19f2c33248b43c90ce262308a9f91','[\"*\"]',NULL,NULL,'2025-03-19 10:06:23','2025-03-19 10:06:23'),(16,'App\\Models\\User',9,'authToken','048ad529bb0698454693437d447bd3998d22ed93cb20be91022ec6aeb8a5c8b6','[\"*\"]',NULL,NULL,'2025-03-19 10:09:34','2025-03-19 10:09:34'),(17,'App\\Models\\User',9,'authToken','91919221dfe2c0e7be36e49188e100bdaddac691e5ecfca55097e6129a2f7ec3','[\"*\"]',NULL,NULL,'2025-03-19 10:09:44','2025-03-19 10:09:44'),(19,'App\\Models\\User',10,'authToken','83146e3371b1d3cd54972b0854d82d200c2640bbe39a72b78e9dc9d35451bc84','[\"*\"]',NULL,NULL,'2025-03-19 10:18:13','2025-03-19 10:18:13'),(20,'App\\Models\\User',11,'authToken','b47f7ddbb07b3dcd108b36df6ffd5272f368f5da475fc9f74594bee09fea8cbc','[\"*\"]','2025-03-19 11:41:55',NULL,'2025-03-19 10:20:23','2025-03-19 11:41:55'),(21,'App\\Models\\User',11,'authToken','181973e4afc02c0e5ac37cd18ecf504be2c58fcf4e88fe4aea5e066f93b5c34d','[\"*\"]','2025-03-19 10:42:45',NULL,'2025-03-19 10:22:30','2025-03-19 10:42:45'),(22,'App\\Models\\User',11,'authToken','3060a457ab3e37c7e387914393e8f4d9bfcd417ecf96d040d056f763480a80bf','[\"*\"]','2025-03-19 10:47:43',NULL,'2025-03-19 10:42:47','2025-03-19 10:47:43'),(23,'App\\Models\\User',6,'authToken','6cdfbf88b82da293ebb78e973178f213e668d70e33fb5ad06431b52e3b90db9d','[\"*\"]','2025-03-19 10:57:34',NULL,'2025-03-19 10:51:55','2025-03-19 10:57:34'),(25,'App\\Models\\User',13,'authToken','4b29784d8138ee762670a3ac78ab2d4720c532ddc2e8eadbabfda7c45b795977','[\"*\"]','2025-03-19 10:54:41',NULL,'2025-03-19 10:54:40','2025-03-19 10:54:41'),(27,'App\\Models\\User',12,'authToken','d69ebc46b321c35f124166d4cdb4e53a01010ef6f59e954dfd16fac770713908','[\"*\"]','2025-03-19 11:05:01',NULL,'2025-03-19 11:04:31','2025-03-19 11:05:01'),(28,'App\\Models\\User',13,'authToken','f8445d82f6e6a4d0739720e5d7b09f7bde9f8619c12912a1ad7837fff85ce9d4','[\"*\"]','2025-03-19 11:11:22',NULL,'2025-03-19 11:10:09','2025-03-19 11:11:22'),(29,'App\\Models\\User',14,'authToken','37b6fd7406cb868ec0965a42ed4ee54af7290e201001a0c91ebf8e31cf715271','[\"*\"]','2025-03-19 11:22:39',NULL,'2025-03-19 11:19:28','2025-03-19 11:22:39'),(30,'App\\Models\\User',13,'authToken','97c1218ceed1f661e295a69220858722741460fd1015872b8f45c76112f15b33','[\"*\"]','2025-03-19 16:18:21',NULL,'2025-03-19 11:23:19','2025-03-19 16:18:21'),(32,'App\\Models\\User',2,'authToken','c479789a47bc08097e831ff451727b24b8cd353c43cc595bbf3ba95d621265b5','[\"*\"]',NULL,NULL,'2025-03-19 15:04:36','2025-03-19 15:04:36'),(33,'App\\Models\\User',16,'authToken','e005e80f979c78df0c3ea743bfeb49ae782b9d569925d9fca993cc6afbb24930','[\"*\"]',NULL,NULL,'2025-03-19 16:14:30','2025-03-19 16:14:30'),(34,'App\\Models\\User',16,'authToken','ef0af939d0d5f66000646a28933ce8573503eb3c23a1eb841da845e545908b0a','[\"*\"]','2025-03-19 16:33:44',NULL,'2025-03-19 16:17:52','2025-03-19 16:33:44'),(35,'App\\Models\\User',13,'authToken','4a2c83dff0198509bddce996d6458e20401b8e9a323f6d8424b7ea8927abca91','[\"*\"]','2025-03-19 16:18:27',NULL,'2025-03-19 16:18:24','2025-03-19 16:18:27'),(36,'App\\Models\\User',15,'authToken','4d1a5bf50b2832f3e0022e955e14b689f573ac0200e27edbb28d42274411eb99','[\"*\"]','2025-03-19 16:29:11',NULL,'2025-03-19 16:22:40','2025-03-19 16:29:11'),(37,'App\\Models\\User',15,'authToken','da60c301d24e769fbe3dc8fee326fc903eb241e44e34dcfdbfb7ef91d9d4ac3c','[\"*\"]','2025-03-19 16:29:51',NULL,'2025-03-19 16:29:13','2025-03-19 16:29:51'),(38,'App\\Models\\User',16,'authToken','7b4c38ebd84c132e19c5a099cd68e0cba9c014e3311030dc35aaa80e2c583d46','[\"*\"]','2025-03-19 16:36:17',NULL,'2025-03-19 16:35:06','2025-03-19 16:36:17'),(39,'App\\Models\\User',16,'authToken','b6f2dbaf8d21a19be79d76c9f7b73ab6218059446ae1a837cbb832e0ab8c8a48','[\"*\"]','2025-03-19 16:38:24',NULL,'2025-03-19 16:37:49','2025-03-19 16:38:24'),(40,'App\\Models\\User',16,'authToken','526d70f4bbbf48acb45b68a4579300d0d71facd0fed61e18f6ae0d75d233cf9b','[\"*\"]','2025-03-19 16:39:56',NULL,'2025-03-19 16:38:39','2025-03-19 16:39:56'),(41,'App\\Models\\User',2,'authToken','832ddbcda25d4a977f89f8e67e2dd846c70406188970ba5ca208c396d59c6cae','[\"*\"]','2025-03-20 20:55:36',NULL,'2025-03-19 16:41:34','2025-03-20 20:55:36'),(42,'App\\Models\\User',2,'authToken','85b42a5ef07dca7b1173e9bb6842d29c8403226a967fe39a656cccc5f948f1fb','[\"*\"]','2025-03-21 06:13:54',NULL,'2025-03-20 20:55:37','2025-03-21 06:13:54'),(44,'App\\Models\\User',2,'authToken','7f099e0ed9e584a6bbe25d1210ec9e29b90562fe1383cc69c2cfe5f21d5dbaa2','[\"*\"]','2025-03-21 06:13:59',NULL,'2025-03-21 06:13:55','2025-03-21 06:13:59'),(46,'App\\Models\\User',18,'authToken','d51456f8d424215ccf845520ae734acec2be400d79e5a68a63050454d31c00ed','[\"*\"]','2025-03-21 09:07:08',NULL,'2025-03-21 06:19:38','2025-03-21 09:07:08'),(47,'App\\Models\\User',1,'authToken','ae4cb4daa633be46b2be40b1b3bd705fcf0093595211fabdcec4eb00112133b9','[\"*\"]','2025-03-22 15:31:14',NULL,'2025-03-21 06:21:35','2025-03-22 15:31:14'),(48,'App\\Models\\User',2,'authToken','4b338790693efbebe16debe89c90e602987cd3ea1a1c919a90d53e25237a814a','[\"*\"]',NULL,NULL,'2025-03-21 06:57:50','2025-03-21 06:57:50'),(49,'App\\Models\\User',2,'authToken','466c530cd0fb677b711a07bc3c7699dcb0f501a62e8a2865e99d971c2a789bb8','[\"*\"]','2025-03-21 08:42:14',NULL,'2025-03-21 08:41:58','2025-03-21 08:42:14'),(50,'App\\Models\\User',18,'authToken','bc6b58d4a9cdd047eadcd425a73b4dfedf41a508e6c6c7d3437aa0f9f93cc219','[\"*\"]','2025-03-21 15:39:13',NULL,'2025-03-21 09:07:10','2025-03-21 15:39:13'),(51,'App\\Models\\User',12,'authToken','2bcbd890c3ca0f1da1df0b5cd6d92c6bbe337b3d2b20436c851a84fd50c4b0f1','[\"*\"]',NULL,NULL,'2025-03-21 09:11:38','2025-03-21 09:11:38'),(53,'App\\Models\\User',18,'authToken','17ce2b8d97425b4bf362d87c08a1c0a8f7738d1b491f5dcc830492669e1304ca','[\"*\"]','2025-03-21 18:46:56',NULL,'2025-03-21 15:39:15','2025-03-21 18:46:56'),(54,'App\\Models\\User',19,'authToken','2864de200f27544f8aac764f4695193817f0a627235dcbeae9daa4a73db970bb','[\"*\"]','2025-03-22 12:35:57',NULL,'2025-03-21 18:42:50','2025-03-22 12:35:57'),(55,'App\\Models\\User',18,'authToken','ee9fd704c4540ff294426d878b669460b847d6b664ee6b0fe27935619e30be2d','[\"*\"]','2025-03-21 18:47:00',NULL,'2025-03-21 18:46:59','2025-03-21 18:47:00'),(56,'App\\Models\\User',19,'authToken','19117f0336567a2265c03ad104075c4c27bede4368426fe45e9fb42b315533f8','[\"*\"]','2025-03-22 12:55:27',NULL,'2025-03-21 18:49:00','2025-03-22 12:55:27'),(57,'App\\Models\\User',19,'authToken','c20acc105edb4ea0b64b2ad86bb0fb6f33b5baa312d17956045925764f425b7c','[\"*\"]','2025-03-21 19:02:27',NULL,'2025-03-21 18:54:20','2025-03-21 19:02:27'),(58,'App\\Models\\User',19,'authToken','904e782cba16b9744aa759097270adee9587624b4c9f8454039746f66e0bb21f','[\"*\"]','2025-03-22 01:12:31',NULL,'2025-03-21 19:00:45','2025-03-22 01:12:31'),(59,'App\\Models\\User',18,'authToken','074b35b399c89a8ef28362f2406696319272dff9328bf8e8179f9bb6bfedb877','[\"*\"]','2025-03-22 13:12:41',NULL,'2025-03-21 19:03:19','2025-03-22 13:12:41'),(60,'App\\Models\\User',19,'authToken','35460f11f3194201e8637396b0ff579f4445b4a4fd20fe365aba35e1bf4e3094','[\"*\"]','2025-03-22 01:20:26',NULL,'2025-03-22 01:12:33','2025-03-22 01:20:26'),(61,'App\\Models\\User',19,'authToken','896ba67d4d6074385869705ade3e21fd34e702b110bcc2f7c4fc5ab25ceae3f8','[\"*\"]','2025-03-22 01:36:04',NULL,'2025-03-22 01:20:30','2025-03-22 01:36:04'),(62,'App\\Models\\User',19,'authToken','1b2bf2f0ed0200a211b28aac71d88f4338bd4b0991b3f88fd6000507ae7c2d41','[\"*\"]','2025-03-22 01:39:13',NULL,'2025-03-22 01:36:06','2025-03-22 01:39:13'),(63,'App\\Models\\User',19,'authToken','781d59070e2c74e9bc3c5dd953fa7b4ad37af5be3be128c22d8fc6374513550e','[\"*\"]','2025-03-22 02:38:20',NULL,'2025-03-22 01:43:54','2025-03-22 02:38:20'),(64,'App\\Models\\User',19,'authToken','db0dec8b6caeff8ca7c89906eed42d3174b60db1af72288f79d55364cb9a0886','[\"*\"]','2025-03-22 04:16:49',NULL,'2025-03-22 02:38:22','2025-03-22 04:16:49'),(65,'App\\Models\\User',19,'authToken','d5708d7eea3f5bc7ae050f7af1cceb387cb8f5a847395215dcf7016c62a93e92','[\"*\"]','2025-03-22 04:23:17',NULL,'2025-03-22 04:16:50','2025-03-22 04:23:17'),(66,'App\\Models\\User',19,'authToken','c321b0a4808ac8f5faa58a8497db55c60cdab0db98bcf929dfbb4fe8b046b1ea','[\"*\"]','2025-03-22 11:26:03',NULL,'2025-03-22 04:23:19','2025-03-22 11:26:03'),(67,'App\\Models\\User',19,'authToken','499320f866e6eaa197c71fed18cf5e0544b72d5d49046604097360668880e3a6','[\"*\"]','2025-03-22 14:57:19',NULL,'2025-03-22 11:26:05','2025-03-22 14:57:19'),(69,'App\\Models\\User',20,'authToken','69164271f68bc9ab7447fbee382aca9059da8986293955bfedc8d0d37b00ec90','[\"*\"]','2025-03-22 13:48:50',NULL,'2025-03-22 12:47:56','2025-03-22 13:48:50'),(70,'App\\Models\\User',20,'authToken','3be90e08669e71726d8f0412c0ec4e8d9c84772f088239599cc9a103ed1188a8','[\"*\"]','2025-03-22 13:24:54',NULL,'2025-03-22 12:54:27','2025-03-22 13:24:54'),(71,'App\\Models\\User',19,'authToken','af230eb4dc25dacb59d59568542dccf9a754376fcd0972924ee4adda034a29da','[\"*\"]','2025-03-22 12:55:35',NULL,'2025-03-22 12:55:29','2025-03-22 12:55:35'),(72,'App\\Models\\User',18,'authToken','abad76d0121affaad6807c8ac5fb79dbd465c572e9186f288f614adcf93347a2','[\"*\"]','2025-03-22 15:30:49',NULL,'2025-03-22 13:12:43','2025-03-22 15:30:49'),(73,'App\\Models\\User',20,'authToken','70008e0e6bbfbed00cb1e608707362ad6ca310ab27c020ff82e099745906de24','[\"*\"]','2025-03-22 13:49:05',NULL,'2025-03-22 13:24:53','2025-03-22 13:49:05'),(74,'App\\Models\\User',19,'authToken','7e673a35ab5181308a252045c5f27b7ecef0bca19b1e79dd707811b1b9daec1a','[\"*\"]','2025-03-22 15:01:16',NULL,'2025-03-22 14:57:21','2025-03-22 15:01:16'),(75,'App\\Models\\User',19,'authToken','3b80919c35a2ed65c1122131aa4dd3ceaf4511ae3830ac7984d99002ecbc9aae','[\"*\"]','2025-03-23 00:06:09',NULL,'2025-03-22 15:01:18','2025-03-23 00:06:09'),(76,'App\\Models\\User',18,'authToken','0cd0763236d46db46a1b71714f0951f56c519854654a77beb3d919d0be26179b','[\"*\"]','2025-03-22 15:30:54',NULL,'2025-03-22 15:30:51','2025-03-22 15:30:54'),(77,'App\\Models\\User',19,'authToken','adf8d1a1166ecfae2f593f4e0c081d4b05798740d1e5cb962152756b617e275a','[\"*\"]','2025-03-23 04:54:53',NULL,'2025-03-23 00:06:11','2025-03-23 04:54:53'),(78,'App\\Models\\User',19,'authToken','77c66219e6e100cf203b632e0ba68093869ae7089f7f223e945fabcca0a4f110','[\"*\"]','2025-03-23 04:55:04',NULL,'2025-03-23 04:54:55','2025-03-23 04:55:04'),(79,'App\\Models\\User',19,'authToken','fc0feb39c181b2cbcda534bb6a3cc8090d12756d40873b627bc78dfecc55f056','[\"*\"]','2025-03-23 04:55:42',NULL,'2025-03-23 04:55:06','2025-03-23 04:55:42'),(80,'App\\Models\\User',19,'authToken','6c74a8ed1f4d18f4417a395043a2235967a0a472fa323df32eaebfb2f1c62301','[\"*\"]','2025-03-23 04:55:52',NULL,'2025-03-23 04:55:44','2025-03-23 04:55:52'),(81,'App\\Models\\User',19,'authToken','4a60c6c7df93c2cdfd445ec7b1f5738db22bb0f243eb70f0beec7a227386f079','[\"*\"]','2025-03-23 05:07:40',NULL,'2025-03-23 04:55:54','2025-03-23 05:07:40');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Makanan',NULL,'2025-03-18 04:21:11','2025-03-18 04:21:11'),(2,'Minuman',NULL,'2025-03-18 04:21:38','2025-03-18 04:21:38');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_galleries`
--

DROP TABLE IF EXISTS `product_galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_galleries_product_id_foreign` (`product_id`),
  CONSTRAINT `product_galleries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_galleries`
--

LOCK TABLES `product_galleries` WRITE;
/*!40000 ALTER TABLE `product_galleries` DISABLE KEYS */;
INSERT INTO `product_galleries` VALUES (4,6,'products/images/6-0-image_0.jpg',NULL,'2025-03-21 06:30:52','2025-03-21 06:30:52'),(5,6,'products/images/6-1-image_1.jpg',NULL,'2025-03-21 06:30:52','2025-03-21 06:30:52'),(6,7,'products/images/7-0-image_0.jpg',NULL,'2025-03-21 06:33:20','2025-03-21 06:33:20'),(7,8,'products/images/8-0-image_0.jpg',NULL,'2025-03-21 06:35:24','2025-03-21 06:35:24'),(8,9,'products/images/9-0-image_0.jpg',NULL,'2025-03-21 06:38:04','2025-03-21 06:38:04'),(9,8,'products/images/8-0-image_0.jpg',NULL,'2025-03-21 09:06:22','2025-03-21 09:06:22'),(10,10,'products/images/10-0-image_0.jpg',NULL,'2025-03-21 09:09:56','2025-03-21 09:09:56'),(12,12,'products/images/12-0-image_0.jpg',NULL,'2025-03-22 01:50:04','2025-03-22 01:50:04'),(13,12,'products/images/12-1-image_1.jpg',NULL,'2025-03-22 01:50:05','2025-03-22 01:50:05'),(14,13,'products/images/13-0-image_0.jpg',NULL,'2025-03-22 01:53:07','2025-03-22 01:53:07'),(15,14,'products/images/14-0-image_0.jpg',NULL,'2025-03-22 04:21:30','2025-03-22 04:21:30');
/*!40000 ALTER TABLE `product_galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_reviews_user_id_foreign` (`user_id`),
  KEY `product_reviews_product_id_foreign` (`product_id`),
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_adjustment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('ACTIVE','INACTIVE','OUT_OF_STOCK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,6,'coto porsi','20k',7000.00,'ACTIVE','2025-03-21 06:30:49','2025-03-21 06:30:49'),(2,6,'coto porsi','25k',12000.00,'ACTIVE','2025-03-21 06:30:49','2025-03-21 06:30:49'),(3,6,'coto porsi','15k',2000.00,'ACTIVE','2025-03-21 06:30:49','2025-03-21 06:30:49'),(4,6,'coto porsi','paruh 15k',2000.00,'ACTIVE','2025-03-21 06:30:49','2025-03-21 06:30:49'),(5,6,'coto porsi','daging paruh 15',2000.00,'ACTIVE','2025-03-21 06:30:49','2025-03-21 06:30:49');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ACTIVE','INACTIVE','OUT_OF_STOCK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (4,9,1,'Ayam Richesee Utuh Original','Ayam Richesee utuh Lokal by Dapur Marisha \n1 ekor utuh include 3 cocolan\nsaos keju, saos lava, sambal bawang','ACTIVE',87000.00,'2025-03-19 16:39:52','2025-03-19 16:39:52'),(6,11,1,'Coto','Coto dengan bumbu dapur rahasia keluarga','ACTIVE',13000.00,'2025-03-21 06:30:49','2025-03-21 06:30:49'),(7,11,1,'Konro + nasi🍚','konro komplit dengan nasi','ACTIVE',35000.00,'2025-03-21 06:33:18','2025-03-21 06:33:18'),(8,11,1,'Ayam Bakar + nasi','Ayam bakar sop saudara +  nasi','ACTIVE',25000.00,'2025-03-21 06:35:21','2025-03-21 06:35:21'),(9,11,1,'Sop Saudara','Sop Saudara + nasi','ACTIVE',20000.00,'2025-03-21 06:38:02','2025-03-21 06:38:02'),(10,11,1,'Sop Saudara + ikan Bakar','paket ikan bakar, nasi ,dan  sop saudara','ACTIVE',35000.00,'2025-03-21 09:09:55','2025-03-21 09:09:55'),(12,12,1,'Nasi Goreng Gila','(Pedas/Sedang/Biasa) pilih selera anda','ACTIVE',20000.00,'2025-03-22 01:49:49','2025-03-22 01:49:49'),(13,12,1,'Nasi Goreng Biasa','(Pedas/Sedang/biasa) pilih selera anda','ACTIVE',15000.00,'2025-03-22 01:52:46','2025-03-22 01:52:46'),(14,12,1,'mie kuah biasa','(Pedas/Sedang/Biasa) pilih selera anda','ACTIVE',15000.00,'2025-03-22 04:20:58','2025-03-22 04:20:58');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('41IHn8vyd6paSJCt944xppPDTkLS8vEPP6MvLLS3',NULL,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoieGdQVjE0aEwyQ25nS2E1ZjRmWFhPN2x5bFNwTmtReEtQZjJBcUY3bCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1742613604),('C5CB1FiUVfZy5MGSdICwW8UCfCq81GDAYq74XmLo',NULL,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoidk5xT3hyWUN3MDJNRHlHYUhZaVNGcjJVYnpJUXVkSWZ5MnBwWnVTWiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MToiaHR0cDovL2Rldi5hbnRhcmthbm1hYS5teS5pZC9hZG1pbi9tZXJjaGFudHM/cGFnZT0yIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly9kZXYuYW50YXJrYW5tYWEubXkuaWQvYWRtaW4vbWVyY2hhbnRzP3BhZ2U9MiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1742617783),('N3cjtGGgsgib2GGhydaKRQYMuw0hkyPBjKNuNp0Z',NULL,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoickxVMnNHT01uUlVha05kSU1JVXRGMWNFUU40Y3U1RjQ0QmRIT0pWVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1742613604),('t1XK7sGLNeuu9hp9BqKmRtN7y75yhYtVRspMsAO8',NULL,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVlJxdlJHRWtlRjk1QVR3Y2lPNFEwN0pPaW1pVlUwd3dqcHFYRkZOMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9kZXYuYW50YXJrYW5tYWEubXkuaWQvYWRtaW4vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1742617784),('YQWStBT5Fsm8gZx35CV4A8uUBAlNY5LlPkyixMhY',NULL,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWEU5cG9wZWNKYTRPd3AzbG5yRHJtQklGMDVRREhTYVlXVmN0MkFlNSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MDoiaHR0cDovL2Rldi5hbnRhcmthbm1hYS5teS5pZC9hZG1pbi91c2VycyI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vZGV2LmFudGFya2FubWFhLm15LmlkL2FkbWluL3VzZXJzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1742647745),('yxZsyoIikyFjZiySAXiyTYyI9zGaiQsYOSNzq9Qh',3,'172.18.0.3','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YTo3OntzOjY6Il90b2tlbiI7czo0MDoiRDZTOHRFdWo5YlhYWmVEZlNKS3U3OUdEVWpJN0pWdWozYUZzRjJlbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM0OiJodHRwOi8vZGV2LmFudGFya2FubWFhLm15LmlkL2FkbWluIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJHJUcW02TjVnQkNTMkNZdk9MUThSVC5kRHJsb08yeVNCdEF6dnNwQ1h1cUUyY3RGYWpPeUltIjtzOjg6ImZpbGFtZW50IjthOjA6e319',1742614675);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `user_location_id` bigint unsigned NOT NULL,
  `courier_id` bigint unsigned DEFAULT NULL,
  `base_merchant_id` bigint unsigned DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_price` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT NULL,
  `status` enum('PENDING','COMPLETED','CANCELED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` enum('MANUAL','ONLINE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('PENDING','COMPLETED','FAILED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `courier_approval` enum('PENDING','APPROVED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `timeout_at` timestamp NULL DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_courier_id_foreign` (`courier_id`),
  KEY `transactions_base_merchant_id_foreign` (`base_merchant_id`),
  CONSTRAINT `transactions_base_merchant_id_foreign` FOREIGN KEY (`base_merchant_id`) REFERENCES `merchants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,14,2,NULL,11,35000.00,24000.00,NULL,'CANCELED','MANUAL','PENDING','PENDING','2025-03-21 09:18:19',NULL,NULL,'2025-03-21 09:08:19','2025-03-22 12:48:07'),(2,4,4,1,12,15000.00,5000.00,NULL,'PENDING','MANUAL','PENDING','APPROVED','2025-03-22 13:17:24',NULL,NULL,'2025-03-22 13:07:24','2025-03-22 13:07:57');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_locations`
--

DROP TABLE IF EXISTS `user_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `address_type` enum('RUMAH','KANTOR','TOKO','LAINNYA') COLLATE utf8mb4_unicode_ci DEFAULT 'RUMAH',
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_locations_user_id_index` (`user_id`),
  KEY `user_locations_is_default_index` (`is_default`),
  KEY `user_locations_address_type_index` (`address_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_locations`
--

LOCK TABLES `user_locations` WRITE;
/*!40000 ALTER TABLE `user_locations` DISABLE KEYS */;
INSERT INTO `user_locations` VALUES (1,5,'IKHSAN','8HWQ+52, Bonto Matene, Kecamatan Segeri, Indonesia','PANGKAJENE KEPULAUAN','SEGERI',NULL,'Indonesia','90655',-4.65449257,119.58771338,'RUMAH','085757835996',1,'CITTA, DEKAT RUMAH WARNA UNGU, ADA POHON GERSENG DI DEPAN RUMAH',1,'2025-03-19 12:51:04','2025-03-19 12:51:11',NULL),(2,14,'Ical','Segeri, Kec. Segeri, Kab. Pangkep','PANGKAJENE KEPULAUAN','SEGERI',NULL,'Indonesia','90655',0.00000000,0.00000000,'RUMAH','081319194734',1,'Perempatan',1,'2025-03-21 09:06:57','2025-03-21 09:06:57',NULL),(3,1,'aswar','9H3M+7XH, Segeri, Kecamatan Segeri, 90655, Indonesia','PANGKAJENE KEPULAUAN','SEGERI',NULL,'Indonesia','90655',-4.64683790,119.58506880,'RUMAH','087886576650',1,'samping mesjid raya',1,'2025-03-21 09:10:52','2025-03-21 09:10:52',NULL),(4,4,'Ical','9H3P+843, Segeri, Kecamatan Segeri, 90655, Indonesia','PANGKAJENE KEPULAUAN','SEGERI',NULL,'Indonesia','90655',-4.64680200,119.58516390,'TOKO','081319194734',1,'Dekat Masjid Segeri',1,'2025-03-21 09:14:21','2025-03-21 09:16:29',NULL);
/*!40000 ALTER TABLE `user_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `roles` enum('ADMIN','USER','MERCHANT','COURIER') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USER',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Aswar Sumarlin','aswarthedoctor@gmail.com',NULL,'$2y$12$6e1ERiEDoSwp3niEF1fuf.RrUJN28Mj4536p6eRMosNmGE2tx5oxG',1,NULL,'087886576650',NULL,NULL,NULL,NULL,NULL,'profile-photos/user-1-3DAVREAN.jpg','2025-03-18 04:08:30','2025-03-18 04:09:54','USER'),(2,'Koneksi Rasa','koneksirasa@gmail.com',NULL,'$2y$12$19.MU3jjfX6IlD17U1QBj.Ebmr9xPsIDMSOHxEgZEGGC4GSEyvgDi',1,NULL,'087812379186',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-18 04:12:35','2025-03-19 10:56:08','MERCHANT'),(3,'AntarkanMa','antarkanma@gmail.com',NULL,'$2y$12$rTqm6N5gBCS2CYvOLQ8RT.dDrloO2ySBtAzvspCXuqE2ctFajOyIm',1,NULL,'087812379186',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-18 04:13:59','2025-03-18 04:13:59','ADMIN'),(4,'Faizal','faizalfajri890@gmail.com',NULL,'$2y$12$lRfzRVOsLWTAqICaYevKluckY0w5.8DM4YoxDMkmw/pVd6GbSqXFu',1,NULL,'081319194734',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-18 09:22:41','2025-03-18 09:22:41','USER'),(5,'FIRDATEST','firdapindang44@gmail.com',NULL,'$2y$12$yotOAjfWm1yczlROZuAROOLxoYKHx6kCmTUMLwZolqmsSUqF7GBTO',1,NULL,'085757835996',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 01:43:21','2025-03-19 01:43:21','USER'),(6,'R.M Empat Lawang','nasikuning@gmail.com',NULL,'$2y$12$WgDazmPq/xHh/FP2oqVnMO9oBEfLXa3bQuLNqR2TmqTPMBYQDH6T2',1,NULL,'082184703675',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 08:58:34','2025-03-21 18:43:52','MERCHANT'),(7,'Nasi Kuning Banjir','nasibanjir@gmail.com',NULL,'$2y$12$KUQ9t1eEh/dTAFGCE4WT0usLo.GlZBcvhsUi0Z6WlnaUP7abqWX6y',1,NULL,'082159859797',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 09:10:28','2025-03-19 10:16:47','MERCHANT'),(8,'Gorengan Mas diki','masdiki@gmail.com',NULL,'$2y$12$vPBUmARkRl9Q5.qJGQMQ6OSCuVpCtsWYPalrGKIZnTPY7GQueCLMW',1,NULL,'083345567894',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 09:11:40','2025-03-19 09:11:40','USER'),(10,'Nasi Kuning Banjir','nasikuningbanjir@gmail.com',NULL,'$2y$12$WwCAUuzhFUlmsrVBeMXxzOhLLq0wqNjCtGWKeVXJv1n4DseBUCJne',1,NULL,'08123456781',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 10:15:11','2025-03-19 10:15:11','USER'),(11,'NASI KUNING BANJIR','sayajer000333@gmail.com',NULL,'$2y$12$SzNtKcFSEgL1AYKSxCC.5e87XyrqFWAXdCOQEjkD4QfcbX3NDxhlm',1,NULL,'081355200321',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 10:20:23','2025-03-19 10:56:15','MERCHANT'),(12,'Frozen Food Segeri','frozenfoodsegeri@gmail.com',NULL,'$2y$12$z.AlqrPRxjtJT/VqV0ZH..ugia.s2bB5uiZteueyNWJQIgVgaKS1e',1,NULL,'0812345678',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 10:52:42','2025-03-19 10:56:40','MERCHANT'),(13,'Gorengan Segeri','gorengansegeri@gmail.con',NULL,'$2y$12$o7dou2/b1c3ftUHXmiMlg.Bj8DfJscjwvcKx.zXPEN/GooUEEGs5G',1,NULL,'08123456789',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 10:54:40','2025-03-19 11:15:12','MERCHANT'),(14,'Tahu Crispy Segeri','tahucrispy@gmail.con',NULL,'$2y$12$cFR6vsej8WKQ/JK34IudrOZTiz9K1jMxbG6KxANesn2qhIzopgvOm',1,NULL,'081234567891',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 10:55:45','2025-03-19 11:13:16','MERCHANT'),(15,'Toko Lima Satu','toko51@gmail.com',NULL,'$2y$12$jKZ7TRyoC4Dbu43pyNiMKe07iv8A1DDZoe/HaiTLR16n68XI3H1je',1,NULL,'081243432404',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 14:37:49','2025-03-19 14:48:35','MERCHANT'),(16,'Marisha Sekala Bumi','marishasekala22@gmail.com',NULL,'$2y$12$7S0WldKDu8dGJz5iwaXQwe3yxUdjEsO7cLrsyCVf.Sqr2x4x0pZx.',1,NULL,'082195325749',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 15:58:41','2025-03-19 16:17:04','MERCHANT'),(17,'OMI BOBA','hamsyukur@yahoo.com',NULL,'$2y$12$mwUR/Ov4gBYavv9GDk858eDLtYT8oL/Tok15Xi3Dvirm624sDkNqa',1,NULL,'085299722242',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-19 16:00:38','2025-03-20 20:54:24','MERCHANT'),(18,'Aji hawa','warunghardini@gmail.com',NULL,'$2y$12$KMKKBRV30wRLa/nCQldUGeEXi.JGVnIccIp0SCwPT.wCzRU9gX/IC',1,NULL,'081355010678',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-21 06:16:48','2025-03-21 06:18:46','MERCHANT'),(19,'Nasgor Jakarta Mas Rudy','nasgorjakartamasrudy@gmail.com',NULL,'$2y$12$bSaP3RT5z/n62rZEst7gX.kczP3H/LjYPThvHwCQhrwhRfpo28/1W',1,NULL,'081808670849',NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-21 18:42:50','2025-03-21 18:46:52','MERCHANT'),(20,'AntarKanMa','antarkanma@courier.com',NULL,'$2y$12$Z.hX85HdQL4KgOjoSPIHVu5w5dBkNWyDkrYcWnK9tV0GOQQdQwE/O',1,'antarkanma','087812379186',NULL,NULL,NULL,NULL,NULL,'profile-photos/courier-20.jpg','2025-03-22 12:46:01','2025-03-22 12:46:02','COURIER');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'antarkanma'
--

--
-- Dumping routines for database 'antarkanma'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-25 14:11:48
