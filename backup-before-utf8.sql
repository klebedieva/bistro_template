/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: database.internal    Database: main
-- ------------------------------------------------------
-- Server version	10.6.23-MariaDB-deb11-log

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
-- Table structure for table `allergen`
--

DROP TABLE IF EXISTS `allergen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `allergen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_25BF08CE77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allergen`
--

LOCK TABLES `allergen` WRITE;
/*!40000 ALTER TABLE `allergen` DISABLE KEYS */;
INSERT INTO `allergen` VALUES (1,'Gluten','gluten'),(2,'Lactose','lactose'),(3,'Fruits ?? coque','nuts'),(4,'??ufs','eggs'),(5,'Poisson','fish'),(6,'Crustac??s','shellfish'),(7,'Soja','soy'),(8,'Arachides','peanuts');
/*!40000 ALTER TABLE `allergen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badge`
--

DROP TABLE IF EXISTS `badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `badge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badge`
--

LOCK TABLES `badge` WRITE;
/*!40000 ALTER TABLE `badge` DISABLE KEYS */;
INSERT INTO `badge` VALUES (91,'Sp??cialit??','sp??cialit??'),(92,'V??g??tarien','v??g??tarien'),(93,'Fait maison','fait-maison'),(94,'M??diterran??en','m??diterran??en'),(96,'Traditionnel','traditionnel'),(97,'Fusion','fusion'),(98,'Saison','saison'),(99,'Sans Gluten','sans-gluten');
/*!40000 ALTER TABLE `badge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_message`
--

DROP TABLE IF EXISTS `contact_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` longtext NOT NULL,
  `consent` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `replied_by_id` int(11) DEFAULT NULL,
  `is_replied` tinyint(1) NOT NULL,
  `replied_at` datetime DEFAULT NULL,
  `reply_message` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2C9211FED6FBBEB5` (`replied_by_id`),
  CONSTRAINT `FK_2C9211FED6FBBEB5` FOREIGN KEY (`replied_by_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_message`
--

LOCK TABLES `contact_message` WRITE;
/*!40000 ALTER TABLE `contact_message` DISABLE KEYS */;
INSERT INTO `contact_message` VALUES (41,'Test','Test','test@test.com','0124857255','commande','test commande test',1,'2025-10-01 12:33:18',9,1,'2025-10-01 12:33:38','Tets test test'),(56,'Julien','Roux','julien.roux@example.com','0777777777','commande','hhhhhhhhhhhhhhhhhhhhh',1,'2025-10-22 17:04:36',NULL,0,NULL,NULL),(59,'KTest','LTest','testkl@test.com','0781233456','evenement_prive','hsjdnckdvzhzzkh',1,'2025-10-26 13:37:01',9,1,'2025-10-26 13:38:55','Merci pour votre int??r??t pour l\'organisation d\'un ??v??nement priv?? au Trois Quarts. Afin de vous proposer une offre adapt??e, pouvez???vous nous pr??ciser la date souhait??e, le nombre de personnes et vos besoins (menu, boissons, budget) ?\r\n\r\nNous reviendrons vers vous rapidement avec une proposition personnalis??e.'),(60,'Test','Test','test@test.com','0781233456','reservation','Test test test',1,'2025-11-03 20:06:31',NULL,0,NULL,NULL),(61,'KTest','LTest','testkl@test.com','0781233456','commande','dsggggggggggggggg',1,'2025-11-03 20:07:22',NULL,0,NULL,NULL),(62,'KTest','LTest','testkl@test.com','0781233456','commande','hhhhhhhhhhhhhhhhhhhhhh',1,'2025-11-04 17:04:08',NULL,0,NULL,NULL),(63,'Julien','Roux','julien.roux@example.com','0986665433','reservation','qqqqqqqqqqqqqqqq',1,'2025-11-05 17:20:31',NULL,0,NULL,NULL),(64,'Julien','Roux','julien.roux@example.com','0986665433','evenement_prive','qqqqqqqqqqqqqqqq',0,'2025-11-06 11:18:18',NULL,0,NULL,NULL),(65,'KTest','LTest','testkl@test.com','0781233456','commande','kkkkkkkkkkkkkkk',0,'2025-11-06 18:40:05',NULL,0,NULL,NULL),(66,'KTest','LTest','testkl@test.com','0781233456','evenement_prive','aaaaaaaaaaaaaaaaa',0,'2025-11-06 19:03:47',NULL,0,NULL,NULL),(67,'KTest','LTest','testkl@test.com','0781233456','reservation','ccccccccccccccc',0,'2025-11-07 19:39:18',NULL,0,NULL,NULL),(68,'KTest','LTest','testkl@test.com','0781233456','reservation','hhhhhhhhhhhhhhhhhh',0,'2025-11-08 11:30:38',NULL,0,NULL,NULL),(69,'Test','Test','test@test.com',NULL,'reservation','Bonjour ! Je voudrais r??server une table pour deux personnes aujourd???hui ?? 19h00.',0,'2025-11-09 15:21:51',NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `contact_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon`
--

DROP TABLE IF EXISTS `coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` longtext DEFAULT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_64BF3F0277153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon`
--

LOCK TABLES `coupon` WRITE;
/*!40000 ALTER TABLE `coupon` DISABLE KEYS */;
INSERT INTO `coupon` VALUES (1,'PROMO10','10% de r??duction sur votre commande','percentage',10.00,20.00,NULL,100,7,'2025-10-23 18:19:51','2025-11-23 18:19:51',1,'2025-10-24 18:19:51','2025-11-11 10:13:39'),(2,'WELCOME5','5??? de r??duction pour les nouveaux clients','fixed',5.00,15.00,NULL,50,0,'2025-10-23 18:19:51','2025-12-23 18:19:51',1,'2025-10-24 18:19:51','2025-10-24 18:19:51'),(3,'SUMMER20','20% de r??duction (max 10???) - Promotion ??t??','percentage',20.00,25.00,10.00,200,0,'2025-10-23 18:19:51','2026-01-22 18:19:51',1,'2025-10-24 18:19:51','2025-10-24 18:19:51'),(4,'SPECIAL3','3??? de r??duction sans minimum','fixed',3.00,NULL,NULL,30,0,'2025-10-23 18:19:51','2025-11-08 18:19:51',1,'2025-10-24 18:19:51','2025-10-24 18:19:51'),(5,'VIP15','15% de r??duction pour les commandes VIP (min 50???)','percentage',15.00,50.00,20.00,NULL,0,'2025-10-23 18:19:51',NULL,1,'2025-10-24 18:19:51','2025-10-24 18:19:51'),(6,'EXPIRED','Code promo expir?? (pour test)','fixed',10.00,NULL,NULL,100,0,'2025-09-24 18:19:51','2025-10-23 18:19:51',1,'2025-10-24 18:19:51','2025-10-24 18:19:51'),(7,'INACTIVE','Code promo inactif (pour test)','percentage',25.00,NULL,NULL,NULL,0,'2025-10-23 18:19:51','2025-11-23 18:19:51',0,'2025-10-24 18:19:51','2025-10-24 18:19:51');
/*!40000 ALTER TABLE `coupon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20250904104138','2025-09-04 10:41:55',42),('DoctrineMigrations\\Version20250904110638','2025-09-04 11:06:45',42),('DoctrineMigrations\\Version20250904151450','2025-09-04 15:15:23',148),('DoctrineMigrations\\Version20250904152142','2025-09-04 15:21:56',39),('DoctrineMigrations\\Version20250905074303','2025-09-05 07:43:11',66),('DoctrineMigrations\\Version20250905125003','2025-09-05 12:50:10',108),('DoctrineMigrations\\Version20250908123138','2025-09-08 12:32:02',41),('DoctrineMigrations\\Version20250909174642','2025-09-10 12:32:31',147),('DoctrineMigrations\\Version20250910123239','2025-09-10 12:32:46',188),('DoctrineMigrations\\Version20250915090813','2025-09-15 11:08:35',87),('DoctrineMigrations\\Version20250916084304','2025-09-16 10:43:12',117),('DoctrineMigrations\\Version20250923094220','2025-09-23 11:45:28',82),('DoctrineMigrations\\Version20250923095704','2025-09-23 11:57:14',360),('DoctrineMigrations\\Version20250923114552','2025-09-23 13:46:37',16),('DoctrineMigrations\\Version20250925135658','2025-09-25 15:58:09',286),('DoctrineMigrations\\Version20250926000001','2025-09-26 11:32:29',86),('DoctrineMigrations\\Version20250926095358','2025-09-26 11:54:05',55),('DoctrineMigrations\\Version20250929121846','2025-09-29 14:23:13',166),('DoctrineMigrations\\Version20250929130204','2025-09-30 11:21:58',2),('DoctrineMigrations\\Version20250930084341',NULL,NULL),('DoctrineMigrations\\Version20250930085641',NULL,NULL),('DoctrineMigrations\\Version20250930085647',NULL,NULL),('DoctrineMigrations\\Version20250930090038',NULL,NULL),('DoctrineMigrations\\Version20250930092818',NULL,NULL),('DoctrineMigrations\\Version20251006141412',NULL,NULL),('DoctrineMigrations\\Version20251006141804','2025-10-06 16:18:25',121),('DoctrineMigrations\\Version20251006144447','2025-10-06 16:45:09',44),('DoctrineMigrations\\Version20251009092646','2025-10-09 11:26:54',105),('DoctrineMigrations\\Version20251014152948','2025-10-14 17:30:12',83),('DoctrineMigrations\\Version20251016152755','2025-10-16 17:29:23',172),('DoctrineMigrations\\Version20251024161627','2025-10-24 18:16:44',266),('DoctrineMigrations\\Version20251105120000','2025-11-05 09:45:20',650),('DoctrineMigrations\\Version20251105123000','2025-11-05 12:02:00',488),('DoctrineMigrations\\Version20251105165415','2025-11-10 21:37:02',779);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drink`
--

DROP TABLE IF EXISTS `drink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `drink` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drink`
--

LOCK TABLES `drink` WRITE;
/*!40000 ALTER TABLE `drink` DISABLE KEYS */;
INSERT INTO `drink` VALUES (74,'C??tes du Rh??ne rouge',5.00,'vins'),(75,'Ros?? de Provence',4.00,'vins'),(76,'Blanc de Cassis',5.00,'vins'),(77,'Pression 25cl',3.00,'bieres'),(78,'Pression 50cl',5.00,'bieres'),(79,'Bi??re artisanale',6.00,'bieres'),(80,'Caf?? expresso',2.00,'chaudes'),(81,'Cappuccino',3.00,'chaudes'),(82,'Th?? / Infusion',2.50,'chaudes'),(83,'Jus de fruits frais',4.00,'fraiches'),(84,'Sodas',3.00,'fraiches'),(85,'Eau min??rale',2.00,'fraiches');
/*!40000 ALTER TABLE `drink` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_images`
--

DROP TABLE IF EXISTS `gallery_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `display_order` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_images`
--

LOCK TABLES `gallery_images` WRITE;
/*!40000 ALTER TABLE `gallery_images` DISABLE KEYS */;
INSERT INTO `gallery_images` VALUES (1,'Terrasse conviviale','Un espace agr??able pour vos repas en ext??rieur','terrasse_2.jpg','terrasse',3,1,'2025-10-14 17:30:26','2025-10-15 08:42:27'),(2,'Vue de la terrasse','D??tente et convivialit?? au soleil','terrasse_3.jpg','terrasse',4,1,'2025-10-14 17:30:26','2025-10-16 17:49:42'),(4,'Notre terrasse','Un lieu convivial pour se d??tendre','terrasse_5.jpg','terrasse',11,1,'2025-10-14 17:30:26',NULL),(5,'Salle principale','L\'int??rieur chaleureux de notre brasserie','interieur_1.jpg','interieur',5,1,'2025-10-14 17:30:26',NULL),(6,'Ambiance int??rieure','Un cadre accueillant pour vos repas','interieur_2.jpg','interieur',6,1,'2025-10-14 17:30:26',NULL),(7,'Ambiance chaleureuse','L\'atmosph??re conviviale du Trois Quarts','ambiance_1.jpg','ambiance',7,1,'2025-10-14 17:30:26',NULL),(8,'Moments conviviaux','Des instants de partage et de plaisir','ambiance_2.jpg','ambiance',8,1,'2025-10-14 17:30:26',NULL),(9,'D??coration soign??e','Un d??cor chaleureux pour vos moments au restaurant','ambiance_4.jpg','ambiance',9,1,'2025-10-14 17:30:26',NULL),(10,'Nos plats','Une cuisine g??n??reuse et savoureuse','plat_1.jpg','plats',12,1,'2025-10-14 17:30:26',NULL),(11,'Sp??cialit??s maison','Des recettes traditionnelles avec cr??ativit??','plat_2.jpg','plats',13,1,'2025-10-14 17:30:26',NULL),(12,'Cuisine du march??','Des produits frais et de saison','plat_3.jpg','plats',14,1,'2025-10-14 17:30:26',NULL),(16,'Nos plats','Une cuisine g??n??reuse et savoureuse','67b77f4e78e2e743576dd4a9ff0925601b129c1a.jpg','plats',15,1,'2025-10-15 09:11:53','2025-10-15 09:30:04'),(17,'Sp??cialit??s maison','Des recettes traditionnelles avec cr??ativit??','1bf1d7003dc4740ee73881650dd08459603bb77a.jpg','plats',16,1,'2025-10-15 09:26:44','2025-10-15 09:30:16'),(18,'Nos d??lices','Des plats pr??par??s avec passion','918a6b4eebcd790c1fcb93da34dc80dd654055ea.jpg','plats',17,1,'2025-10-15 09:37:50',NULL),(19,'Accueil chaleureux','Un message plein de convivialit??','e12aaa222b5d118ba5193744f172a38a486f14d9.jpg','ambiance',18,1,'2025-10-15 09:47:14',NULL),(20,'Comptoir r??tro','Bois et verre color?? pour une touche vintage','4e63d0e9c5013716b8754ab3fc2ace46ee69356e.png','interieur',19,1,'2025-10-15 09:48:17',NULL),(21,'Fa??ade ensoleill??e','Le charme du 3/4 au c??ur de Marseille','67a2d9015e156a19cac86564ea8f8746f109c30a.webp','terrasse',20,1,'2025-10-15 10:44:44','2025-10-15 10:45:00'),(22,'Auvent rouge','La touche embl??matique du Trois Quarts','b5da2579c83226a384f2b7d3922e167d0175ad93.webp','terrasse',22,1,'2025-10-15 10:47:07',NULL),(23,'Vins naturels','Une s??lection authentique et pleine de caract??re','bbe1877de893fe7efa2017be68b027b5afa6c2f6.jpg','plats',21,1,'2025-10-15 10:47:44',NULL);
/*!40000 ALTER TABLE `gallery_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item`
--

DROP TABLE IF EXISTS `menu_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(32) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `ingredients` longtext DEFAULT NULL,
  `preparation` longtext DEFAULT NULL,
  `chef_tip` longtext DEFAULT NULL,
  `nutrition_calories_kcal` int(11) DEFAULT NULL,
  `nutrition_proteins_g` decimal(6,1) DEFAULT NULL,
  `nutrition_carbs_g` decimal(6,1) DEFAULT NULL,
  `nutrition_fats_g` decimal(6,1) DEFAULT NULL,
  `nutrition_fiber_g` decimal(6,1) DEFAULT NULL,
  `nutrition_sodium_mg` int(11) DEFAULT NULL,
  `prep_time_min` int(11) DEFAULT NULL,
  `prep_time_max` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item`
--

LOCK TABLES `menu_item` WRITE;
/*!40000 ALTER TABLE `menu_item` DISABLE KEYS */;
INSERT INTO `menu_item` VALUES (137,'Asperges Printemps à la Ricotta','Asperges vertes fraîches, crème de ricotta maison, oignons marinés et graines de moutarde toastées. Un contraste de textures et de saveurs végétariennes.',14.00,'entrees','entree-1-1758878863-1762853881.png','2025-09-29 16:07:12','2025-11-11 10:38:01','[\"asperges vertes fraîches\",\"ricotta maison\",\"oignons rouges\",\"graines de moutarde\",\"vinaigre de cidre\",\"huile d\'olive extra vierge\",\"sel\",\"poivre\",\"herbes fraîches\",\"citron\",\"sucre de canne\"]','Nos asperges vertes sont cuites à la vapeur pour préserver leur croquant et leur saveur naturelle. La ricotta est préparée maison avec du lait frais et la crème est assaisonnée avec des herbes fraîches. Les oignons sont marinés dans un vinaigre parfumé et les graines de moutarde sont toastées pour un contraste de textures.','Vin blanc sec pour accompagner les saveurs fraîches.',280,12.0,15.0,16.0,6.0,450,20,25),(138,'??uf Mollet au Safran et Petits Pois','??uf mollet au safran, cr??me onctueuse de petits pois et tuiles noires au s??same. Un plat v??g??tarien raffin?? aux saveurs printani??res.',13.00,'entrees','entree-2-1759157112.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"??ufs frais\",\"safran de qualit??\",\"petits pois frais\",\"graines de s??same noir\",\"cr??me fra??che\",\"beurre\",\"huile d\'olive extra vierge\",\"sel\",\"poivre\",\"herbes fra??ches\",\"citron\"]','Notre ??uf mollet est cuit ?? la perfection avec du safran de qualit?? pour une couleur dor??e et un go??t unique. La cr??me de petits pois est pr??par??e avec des pois frais et de la cr??me fra??che pour une texture onctueuse. Les tuiles noires au s??same ajoutent un contraste de texture et de saveur.','Vin blanc sec pour accompagner les saveurs d??licates.',240,14.0,12.0,14.0,4.0,400,15,20),(139,'Seiches Saut??es ?? la Chermoula','Seiches saut??es, chermoula aux jeunes pousses d\'??pinards, coulis de betteraves et f??ta. Un plat m??diterran??en aux saveurs marocaines.',15.00,'entrees','entree-3-1759157124.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"seiches\",\"jeunes pousses d\'??pinards\",\"betteraves\",\"f??ta\",\"ail\",\"coriandre\",\"citron\",\"huile d\'olive\",\"??pices marocaines\",\"sel\",\"poivre\"]','Nos seiches sont saut??es ?? la perfection pour pr??server leur tendret?? et leur saveur naturelle. La chermoula est pr??par??e avec des jeunes pousses d\'??pinards fra??ches, de l\'ail, de la coriandre et des ??pices marocaines authentiques. Le coulis de betteraves apporte une touche de douceur et de couleur, tandis que la f??ta ajoute une note sal??e et cr??meuse.','Vin blanc sec, parfait pour les notes m??diterran??ennes.',260,22.0,8.0,14.0,6.0,550,20,25),(140,'Boulette d\'agneau','Boulettes d\'agneau parfum??es aux herbes, carottes r??ties au cumin et miel, yaourt grec ?? la citronnelle et miel, accompagn?? de riz basmati.',22.00,'plats','plat-1-1759157137.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"agneau hach??\",\"oignon\",\"ail\",\"persil\",\"cumin\",\"paprika\",\"carotte\",\"miel\",\"yaourt grec\",\"riz basmati\"]','Nos boulettes d\'agneau sont pr??par??es ?? la main avec de l\'agneau hach?? frais, parfum??es aux herbes de Provence et ??pices traditionnelles. Les carottes sont r??ties au cumin et miel pour un go??t unique.','Servir avec yaourt ?? la citronnelle; touche de miel pour l\'??quilibre.',480,28.0,25.0,22.0,4.0,750,25,30),(141,'Galinette po??l??e ?? l\'ajo blanco','Filet de galinette po??l?? ?? la perfection, servi avec une soupe froide traditionnelle ?? l\'ail et amandes, poivre du Sichuan et huile parfum??e ?? la ciboulette.',24.00,'plats','plat-2-1759157165.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"galinette\",\"ail\",\"amandes\",\"pain rassis\",\"huile d\'olive\",\"poivre du Sichuan\",\"ciboulette\",\"vinaigre de cidre\",\"sel\",\"beurre\"]','Notre galinette est po??l??e ?? la perfection avec du beurre et des herbes fra??ches. L\'ajo blanco est pr??par?? selon la tradition andalouse avec de l\'ail frais et des amandes torr??fi??es, cr??ant un contraste unique entre chaud et froid.','Huile ?? la ciboulette et poivre du Sichuan en touche finale.',520,35.0,22.0,32.0,6.0,850,25,30),(142,'Sashimi de ventr??che de thon fum??','Sashimi de ventr??che de thon fum?? au charbon, cr??me fum??e et herbes fra??ches, servi avec une sauce soja et wasabi.',24.00,'plats','plat-9-1759157179.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"ventr??che de thon\",\"cr??me fum??e\",\"charbon actif\",\"herbes fra??ches\",\"sauce soja\",\"wasabi\",\"gingembre\",\"citron\",\"huile de s??same\",\"sel\",\"poivre\"]','Notre sashimi de ventr??che de thon est fum?? au charbon actif pour une saveur unique et intense. La cr??me fum??e ajoute une touche cr??meuse et les herbes fra??ches apportent fra??cheur et ??quilibre.','Sak?? ou vin blanc sec pour les notes fum??es et japonaises.',280,32.0,8.0,12.0,2.0,850,15,20),(143,'Magret de canard au fenouil confit','Magret de canard, fenouil confit au vin blanc, cr??me de betterave et herbes fra??ches.',28.00,'plats','plat-7-1759157242.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"magret de canard\",\"fenouil\",\"vin blanc\",\"betterave\",\"cr??me fra??che\",\"herbes fra??ches\",\"beurre\",\"sel\",\"poivre\"]','Notre magret de canard est pr??par?? selon la tradition fran??aise, servi avec du fenouil confit au vin blanc et une cr??me de betterave parfum??e aux herbes fra??ches.','Un Bordeaux rouge accompagne les notes riches du canard.',580,35.0,12.0,38.0,4.0,800,30,35),(144,'Velout?? de ch??taignes aux pleurottes','Velout?? cr??meux de ch??taignes, pleurottes saut??es et coppa grill??e, parfum?? aux herbes de Provence.',16.00,'plats','plat-8-1759157260.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"ch??taignes\",\"pleurottes\",\"coppa\",\"cr??me fra??che\",\"oignon\",\"ail\",\"herbes de Provence\",\"beurre\",\"huile d\'olive\",\"sel\",\"poivre\",\"bouillon de l??gumes\"]','Notre velout?? de ch??taignes est pr??par?? avec des ch??taignes fra??ches de saison, cr??meux et parfum?? aux herbes de Provence. Les pleurottes sont saut??es ?? la perfection et la coppa grill??e ajoute une touche de saveur unique.','Vin blanc sec pour les saveurs terreuses.',320,12.0,28.0,18.0,6.0,650,25,30),(145,'Spaghettis ?? l\'ail noir et parmesan','Spaghettis al dente, sauce au jus de veau parfum?? ?? l\'ail noir, citron confit et parmesan affin??.',20.00,'plats','plat-3-1759157278.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"spaghettis\",\"jus de veau\",\"ail noir\",\"citron confit\",\"parmesan\",\"beurre\",\"huile d\'olive\",\"sel\",\"poivre\",\"herbes fra??ches\"]','Nos spaghettis sont cuits al dente selon la tradition italienne. Le jus de veau est r??duit avec de l\'ail noir pour une saveur profonde et complexe, rehauss?? par le citron confit et le parmesan affin??.','Un rouge l??ger accompagne tr??s bien les saveurs du jus de veau.',480,18.0,65.0,15.0,3.0,750,20,25),(146,'Loup de mer aux pois chiches','Loup de mer grill??, salade de pois chiches, tomates s??ch??es, petits pois et olives de Kalamata.',26.00,'plats','plat-5-1759157291.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"loup de mer\",\"pois chiches\",\"tomates s??ch??es\",\"petits pois\",\"olives de Kalamata\",\"huile d\'olive\",\"citron\",\"ail\",\"herbes fra??ches\",\"sel\",\"poivre\"]','Notre loup de mer est grill?? ?? la perfection selon les traditions m??diterran??ennes. La salade de pois chiches est pr??par??e avec des tomates s??ch??es, petits pois et olives de Kalamata pour un go??t authentique.','Vin blanc sec pour sublimer le poisson.',380,32.0,28.0,16.0,8.0,650,25,30),(147,'Potimarron R??ti aux Saveurs d\'Asie','Potimarron r??ti au four, mousseline de chou-fleur, roquette fra??che et jaune d\'??uf confit au soja, parsem?? de nori. Un plat v??g??tarien fusion.',18.00,'plats','plat-10-1759157314.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"potimarron\",\"chou-fleur\",\"roquette\",\"??ufs\",\"sauce soja\",\"nori\",\"beurre\",\"cr??me fra??che\",\"sel\",\"poivre\",\"huile d\'olive\"]','Notre potimarron est r??ti au four pour d??velopper ses saveurs naturelles sucr??es. La mousseline de chou-fleur est pr??par??e avec de la cr??me fra??che et du beurre pour une texture onctueuse. Le jaune d\'??uf est confit dans la sauce soja pour un go??t umami unique, et le nori ajoute une touche japonaise authentique.','Vin blanc sec ou sak?? pour l\'accord franco-japonais.',320,8.0,25.0,18.0,8.0,600,30,35),(148,'Gaspacho Tomates et Melon','Gaspacho tomates, melon, basilic et f??ta. Un plat rafra??chissant sans gluten aux saveurs m??diterran??ennes.',12.00,'plats','plat-12-1759157326.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"tomates\",\"melon\",\"basilic\",\"f??ta\",\"huile d\'olive\",\"vinaigre\",\"ail\",\"sel\",\"poivre\"]','Notre gaspacho est pr??par?? avec des tomates fra??ches et du melon de saison pour une soupe froide rafra??chissante. Le basilic frais apporte une touche aromatique et la f??ta ajoute une note sal??e qui ??quilibre parfaitement la douceur du melon.','Servir tr??s frais pour un meilleur ??quilibre.',180,8.0,15.0,12.0,6.0,400,15,20),(149,'Tartelette aux Marrons Suisses','Tartelette aux marrons suisses, meringu??e. Un dessert traditionnel aux saveurs automnales.',8.00,'desserts','dessert-1-1759157343.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"marrons suisses\",\"p??te sabl??e\",\"meringue italienne\",\"cr??me p??tissi??re\",\"sucre\",\"beurre\",\"??ufs\"]','Notre tartelette aux marrons suisses est pr??par??e avec une p??te sabl??e maison et des marrons suisses de qualit??. La cr??me p??tissi??re est parfum??e ?? la vanille et la meringue italienne est pr??par??e ?? la perfection pour un contraste de textures et de saveurs.','Accompagner d\'un caf?? ou d\'un th??.',320,6.0,45.0,12.0,3.0,200,30,35),(150,'Tartelette Ricotta au Miel et Fraises','Tartelette ricotta au miel, fraises fra??ches et compot??e de rhubarbe. Un dessert printanier raffin??.',9.00,'desserts','dessert-2-1759157356.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"ricotta\",\"miel\",\"fraises\",\"rhubarbe\",\"p??te sabl??e\",\"sucre\",\"beurre\",\"??ufs\",\"vanille\"]','Notre tartelette ricotta est pr??par??e avec une ricotta fra??che et du miel de qualit??. Les fraises fra??ches et la compot??e de rhubarbe apportent une touche de fra??cheur et d\'acidit?? qui ??quilibre parfaitement la douceur du miel et de la ricotta.','Th?? vert ou caf?? l??ger.',280,8.0,35.0,14.0,4.0,180,25,30),(151,'Cr??meux Yuzu aux Fruits Rouges','Cr??meux yuzu, fruits rouges frais, meringues et noisettes. Un dessert fusion aux saveurs japonaises.',10.00,'desserts','dessert-3-1759157367.png','2025-09-29 16:07:12','2025-09-29 16:56:51','[\"yuzu\",\"fruits rouges\",\"meringues\",\"noisettes\",\"cr??me fra??che\",\"sucre\",\"??ufs\",\"vanille\"]','Notre cr??meux yuzu est pr??par?? avec du yuzu frais import?? du Japon pour une saveur authentique et unique. Les fruits rouges frais apportent une touche de fra??cheur et d\'acidit??, tandis que les meringues et noisettes ajoutent un contraste de textures et de saveurs.','Th?? vert japonais pour souligner le yuzu.',260,6.0,30.0,16.0,5.0,150,20,25);
/*!40000 ALTER TABLE `menu_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_allergen`
--

DROP TABLE IF EXISTS `menu_item_allergen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_allergen` (
  `menu_item_id` int(11) NOT NULL,
  `allergen_id` int(11) NOT NULL,
  PRIMARY KEY (`menu_item_id`,`allergen_id`),
  KEY `IDX_EF7195939AB44FE0` (`menu_item_id`),
  KEY `IDX_EF7195936E775A4A` (`allergen_id`),
  CONSTRAINT `FK_EF7195936E775A4A` FOREIGN KEY (`allergen_id`) REFERENCES `allergen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EF7195939AB44FE0` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_allergen`
--

LOCK TABLES `menu_item_allergen` WRITE;
/*!40000 ALTER TABLE `menu_item_allergen` DISABLE KEYS */;
INSERT INTO `menu_item_allergen` VALUES (137,2),(138,2),(138,3),(138,4),(139,2),(139,5),(140,1),(140,2),(141,1),(141,3),(141,5),(142,5),(142,7),(143,2),(144,2),(145,1),(145,2),(146,5),(147,2),(147,4),(147,7),(148,2),(149,1),(149,2),(149,4),(150,1),(150,2),(151,2),(151,3),(151,4);
/*!40000 ALTER TABLE `menu_item_allergen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_badge`
--

DROP TABLE IF EXISTS `menu_item_badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_badge` (
  `menu_item_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  PRIMARY KEY (`menu_item_id`,`badge_id`),
  KEY `IDX_F5FAE71F9AB44FE0` (`menu_item_id`),
  KEY `IDX_F5FAE71FF7A2C2FC` (`badge_id`),
  CONSTRAINT `FK_F5FAE71F9AB44FE0` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F5FAE71FF7A2C2FC` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_badge`
--

LOCK TABLES `menu_item_badge` WRITE;
/*!40000 ALTER TABLE `menu_item_badge` DISABLE KEYS */;
INSERT INTO `menu_item_badge` VALUES (137,92),(137,93),(138,92),(138,93),(139,93),(139,94),(140,93),(141,91),(141,96),(142,91),(142,97),(143,91),(143,96),(144,96),(144,98),(145,96),(146,94),(146,96),(147,92),(147,97),(148,94),(148,99),(149,93),(149,98),(150,93),(150,98),(151,93),(151,97);
/*!40000 ALTER TABLE `menu_item_badge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_tag`
--

DROP TABLE IF EXISTS `menu_item_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_tag` (
  `menu_item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`menu_item_id`,`tag_id`),
  KEY `IDX_C8CD89279AB44FE0` (`menu_item_id`),
  KEY `IDX_C8CD8927BAD26311` (`tag_id`),
  CONSTRAINT `FK_C8CD89279AB44FE0` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C8CD8927BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_tag`
--

LOCK TABLES `menu_item_tag` WRITE;
/*!40000 ALTER TABLE `menu_item_tag` DISABLE KEYS */;
INSERT INTO `menu_item_tag` VALUES (137,31),(138,31),(147,31),(148,31),(148,33),(149,31),(150,31),(151,31);
/*!40000 ALTER TABLE `menu_item_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `delivery_mode` varchar(255) NOT NULL DEFAULT 'delivery',
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_zip` varchar(20) DEFAULT NULL,
  `delivery_instructions` longtext DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'card',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `no` varchar(255) NOT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_first_name` varchar(255) DEFAULT NULL,
  `client_last_name` varchar(255) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `IDX_F529939866C5951B` (`coupon_id`),
  CONSTRAINT `FK_F529939866C5951B` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
INSERT INTO `order` VALUES (5,'pending','delivery','5 Avenue Jean Boccace','84000',NULL,3.00,'cash',65.45,6.55,75.00,'2025-10-09 12:16:14','ORD-20251009-2611','test@test.com','Test New','Test','Test','0124857255',NULL,0.00),(6,'confirmed','delivery','5 Avenue Jean Boccace','84000',NULL,5.00,'card',16.36,1.64,23.00,'2025-10-09 17:37:00','ORD-20251009-0693','test@test.com','Test Test','Test','Test','0124857255',NULL,0.00),(7,'confirmed','delivery','10 CR de Fontainieu','13014','test',5.00,'card',37.27,3.73,46.00,'2025-10-10 10:43:57','ORD-20251010-8823','test@test.com','Test Test','Test','Test','0612345634',NULL,0.00),(11,'confirmed','delivery','Bd Jard. Zoologique','13004',NULL,5.00,'card',21.82,2.18,29.00,'2025-10-20 15:01:18','ORD-20251020-4575','test@test.com','Test Test','Test','Test','0781233456',NULL,0.00),(12,'delivered','delivery','Bd Jard. Zoologique','13004',NULL,5.00,'cash',16.36,1.64,23.00,'2025-10-20 15:22:04','ORD-20251020-8301','testkl@test.com','Test Test','Test','Test','0781233456',NULL,0.00),(13,'confirmed','delivery','Bd Jard. Zoologique','13004',NULL,5.00,'card',8.18,0.82,14.00,'2025-10-20 16:26:07','ORD-20251020-8206','testkl@test.com','Test Test','Test','Test','0781224415',NULL,0.00),(20,'cancelled','delivery','Bd Jard. Zoologique','13004','',5.00,'card',18.18,1.82,22.50,'2025-10-24 18:26:36','ORD-20251024-1289','testkl@test.com','Test Test','Test','Test','0781233456',1,2.50),(21,'confirmed','delivery','Bd Jard. Zoologique','13004','',5.00,'card',76.36,7.64,80.10,'2025-10-26 13:45:57','ORD-20251026-3853','testkl@test.com','Test Test','Test','Test','0781233456',1,8.90),(22,'preparing','delivery','Bd Jard. Zoologique','13004','',5.00,'card',12.73,1.27,19.00,'2025-11-03 11:49:40','ORD-20251103-2618','testkl@test.com','Test Test','Test','Test','0781233456',NULL,0.00),(42,'pending','delivery','Bd Jard. Zoologique','13004','',5.00,'cash',47.27,4.73,57.00,'2025-11-09 11:27:15','ORD-20251109-3793','testkl@test.com','test test','test','test','+33781233456',NULL,0.00),(43,'confirmed','delivery','GiFi JEANNE D ARC, 48-50 Bd Jeanne dArc','13005','',5.00,'card',25.45,2.55,29.70,'2025-11-11 10:13:36','ORD-20251111-5504','testprod@test.com','Test Prod Test Prod','Test Prod','Test Prod','0781112233',1,3.30);
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_52EA1F098D9F6D38` (`order_id`),
  KEY `IDX_52EA1F099AB44FE0` (`menu_item_id`),
  CONSTRAINT `FK_52EA1F098D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_52EA1F099AB44FE0` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item`
--

LOCK TABLES `order_item` WRITE;
/*!40000 ALTER TABLE `order_item` DISABLE KEYS */;
INSERT INTO `order_item` VALUES (6,5,141,'Galinette po??l??e ?? l\'ajo blanco',24.00,3,72.00,NULL),(7,6,147,'Potimarron R??ti aux Saveurs d\'Asie',18.00,1,18.00,NULL),(8,7,138,'??uf Mollet au Safran et Petits Pois',13.00,1,13.00,NULL),(9,7,137,'Asperges Printemps ?? la Ricotta',14.00,2,28.00,NULL),(13,11,142,'Sashimi de ventr??che de thon fum??',24.00,1,24.00,NULL),(14,12,147,'Potimarron R??ti aux Saveurs d\'Asie',18.00,1,18.00,NULL),(15,13,150,'Tartelette Ricotta au Miel et Fraises',9.00,1,9.00,NULL),(22,20,145,'Spaghettis ?? l\'ail noir et parmesan',20.00,1,20.00,NULL),(23,21,143,'Magret de canard au fenouil confit',28.00,3,84.00,NULL),(24,22,137,'Asperges Printemps ?? la Ricotta',14.00,1,14.00,NULL),(47,42,138,'??uf Mollet au Safran et Petits Pois',13.00,4,52.00,NULL),(48,43,137,'Asperges Printemps ?? la Ricotta',14.00,2,28.00,NULL);
/*!40000 ALTER TABLE `order_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(5) NOT NULL,
  `guests` int(11) NOT NULL,
  `message` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `is_confirmed` tinyint(1) NOT NULL,
  `confirmed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `confirmation_message` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (19,'Karyna','Lebedieva','karinatatli91@gmail.com','0123456785','2025-09-30','16:00',8,'test','2025-09-30 15:30:14','confirmed',1,'2025-09-30 15:30:31','Votre r??servation pour le 30/09/2025 ?? 16:00 pour 8 personnes a ??t?? confirm??e.\r\n\r\nNous avons h??te de vous accueillir au restaurant !'),(20,'Test','Test','test@test.com','0124857255','2025-09-30','16:30',8,NULL,'2025-09-30 15:30:51','confirmed',1,'2025-09-30 15:31:03','Votre r??servation pour le 30/09/2025 ?? 16:30 pour 8 personnes a ??t?? confirm??e.\r\n\r\nNous avons h??te de vous accueillir au restaurant !'),(23,'Test','Test','test@test.com','0124857255','2025-09-30','16:30',7,NULL,'2025-09-30 16:01:11','cancelled',0,NULL,NULL),(24,'Karyna','Lebedieva','karinatatli91@gmail.com','0123456785','2025-10-01','19:00',3,'test','2025-10-01 12:09:01','confirmed',1,'2025-10-01 12:11:06','Votre r??servation pour le 01/10/2025 ?? 19:00 pour 3 personnes a ??t?? confirm??e.\r\n\r\nNous avons h??te de vous accueillir au restaurant !'),(25,'Test','Test','test@test.com','0124857255','2025-10-09','17:00',5,NULL,'2025-10-09 11:03:03','pending',0,'2025-10-09 12:52:28',NULL),(40,'KTest','LTest','testkl@test.com','0781233456','2025-10-23','19:30',6,'<script></script>','2025-10-23 12:16:32','pending',0,NULL,NULL),(41,'KTest','LTest','testkl@test.com','0781233456','2025-10-26','16:00',5,NULL,'2025-10-26 13:39:40','confirmed',1,'2025-10-26 13:40:20','Votre r??servation pour le 26/10/2025 ?? 16:00 pour 5 personnes a ??t?? confirm??e.\r\n\r\nNous avons h??te de vous accueillir au restaurant !'),(42,'Julien','Roux','julien.roux@example.com','0866543432','2025-11-04','18:30',2,NULL,'2025-11-04 17:14:28','pending',0,NULL,NULL),(43,'KTest','LTest','testkl@test.com','0781233456','2025-11-05','17:30',1,NULL,'2025-11-05 17:20:41','pending',0,NULL,NULL),(44,'KTest','LTest','testkl@test.com','0781233456','2025-11-05','19:00',2,NULL,'2025-11-05 18:21:38','pending',0,NULL,NULL),(45,'Julien','Roux','julien.roux@example.com','0986666664','2025-11-06','20:00',5,NULL,'2025-11-06 11:18:34','pending',0,NULL,NULL),(46,'Julien','Roux','julien.roux@example.com','0986666664','2025-11-06','18:00',1,NULL,'2025-11-06 17:45:36','pending',0,NULL,NULL),(47,'Julien','Roux','julien.roux@example.com','0986666664','2025-11-06','19:30',4,NULL,'2025-11-06 18:40:33','pending',0,NULL,NULL),(48,'Julien','Roux','julien.roux@example.com','0986666664','2025-11-06','21:30',5,NULL,'2025-11-06 18:41:22','pending',0,NULL,NULL),(49,'Julien','Roux','julien.roux@example.com','0986666664','2025-11-06','20:00',3,NULL,'2025-11-06 19:04:00','pending',0,NULL,NULL),(51,'Test','Test','test@test.com','0781111112','2025-11-10','22:00',7,NULL,'2025-11-10 21:40:38','pending',0,NULL,NULL);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `is_approved` tinyint(1) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6970EB0F9AB44FE0` (`menu_item_id`),
  CONSTRAINT `FK_6970EB0F9AB44FE0` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,'Camille B.','camille@example.com',5,'Ambiance chaleureuse et cuisine savoureuse. On s\'est r??gal??s !','2025-09-15 17:10:40',1,NULL),(2,'Louis M.','louis@example.com',4,'Tr??s bons plats, service efficace. On reviendra.','2025-09-17 17:10:40',1,NULL),(3,'Sofia R.','sofia@example.com',5,'Desserts excellents et carte vari??e. Parfait en famille.','2025-09-19 17:10:40',1,NULL),(4,'Alex P.',NULL,3,'Cadre agr??able, portions un peu justes pour moi.','2025-09-20 17:10:40',1,NULL),(5,'Nina D.','nina@example.com',5,'Meilleur restaurant du quartier, bravo ?? l\'??quipe !','2025-09-21 17:10:40',1,NULL),(6,'Hugo T.',NULL,4,'Tr??s bon rapport qualit??-prix.','2025-09-22 17:10:40',1,NULL),(7,'Marine L.','marine@example.com',5,'Service adorable et plats d??licats.','2025-09-23 17:10:40',1,NULL),(8,'Pierre C.',NULL,4,'Belle carte et vins sympas.','2025-09-24 17:10:40',1,NULL),(9,'Iris V.','iris@example.com',5,'Une super exp??rience de bout en bout.','2025-09-25 17:10:40',1,NULL),(10,'Marie L.',NULL,5,'Asperges parfaitement cuites, ricotta maison d??licieuse !','2025-09-26 17:10:40',1,137),(11,'Thomas G.',NULL,4,'Tr??s frais et ??quilibr??, j\'ai ador??.','2025-09-25 17:10:40',1,137),(12,'Claire P.',NULL,5,'Un must pour les v??g??tariens.','2025-09-24 17:10:40',1,137),(13,'Julien R.',NULL,5,'Cuisson de l\'??uf impeccable, saveur de safran subtile.','2025-09-26 17:10:40',1,138),(14,'Sophie D.',NULL,4,'Cr??meux et gourmand, tr??s bon plat.','2025-09-25 17:10:40',1,138),(15,'Antoine M.',NULL,4,'Belle d??couverte !','2025-09-24 17:10:40',1,138),(16,'L??a C.',NULL,5,'Seiches tendres et assaisonnement parfait.','2025-09-26 17:10:40',1,139),(17,'Nicolas B.',NULL,4,'Superbes saveurs marocaines.','2025-09-25 17:10:40',1,139),(18,'??lodie F.',NULL,5,'Coup de c??ur !','2025-09-24 17:10:40',1,139),(19,'Hugo V.',NULL,5,'Boulettes parfum??es, yaourt ?? la citronnelle top.','2025-09-26 17:10:40',1,140),(20,'Camille S.',NULL,4,'Tr??s bon, j\'en reprendrais.','2025-09-25 17:10:40',1,140),(21,'Pauline T.',NULL,4,'Carottes r??ties excellentes.','2025-09-24 17:10:40',1,140),(22,'Arthur K.',NULL,5,'Cuisson parfaite, contraste chaud/froid r??ussi.','2025-09-26 17:10:40',1,141),(23,'Manon J.',NULL,4,'D??licat et original.','2025-09-25 17:10:40',1,141),(24,'Lucas D.',NULL,5,'Magnifique plat.','2025-09-24 17:10:40',1,141),(25,'Marie L.',NULL,5,'Alliance ail noir et parmesan incroyable.','2025-09-26 17:10:40',1,145),(26,'Thomas G.',NULL,4,'Tr??s savoureux, portions g??n??reuses.','2025-09-25 17:10:40',1,145),(27,'Claire P.',NULL,4,'Bonne ma??trise des sauces.','2025-09-24 17:10:40',1,145),(28,'Julien R.',NULL,5,'Poisson grill?? ?? la perfection, salade de pois chiches top.','2025-09-26 17:10:40',1,146),(29,'Sophie D.',NULL,4,'Tr??s bon ??quilibre des saveurs.','2025-09-25 17:10:40',1,146),(30,'Antoine M.',NULL,4,'Je recommande.','2025-09-24 17:10:40',1,146),(31,'L??a C.',NULL,5,'Magret tendre, sauce betterave d??licieuse.','2025-09-26 17:10:40',1,143),(32,'Nicolas B.',NULL,4,'Super cuisson, fenouil confit r??ussi.','2025-09-25 17:10:40',1,143),(33,'??lodie F.',NULL,5,'Excellent.','2025-09-24 17:10:40',1,143),(34,'Hugo V.',NULL,4,'Velout?? onctueux, pleurottes bien saisies.','2025-09-26 17:10:40',1,144),(35,'Camille S.',NULL,4,'R??confortant et parfum??.','2025-09-25 17:10:40',1,144),(36,'Pauline T.',NULL,5,'Parfait en entr??e.','2025-09-24 17:10:40',1,144),(37,'Arthur K.',NULL,5,'Fumage ma??tris??, texture fondante.','2025-09-26 17:10:40',1,142),(38,'Manon J.',NULL,4,'Tr??s fin et original.','2025-09-25 17:10:40',1,142),(39,'Lucas D.',NULL,5,'Explosion de saveurs.','2025-09-24 17:10:40',1,142),(40,'Marie L.',NULL,5,'Magnifique accord franco-japonais.','2025-09-26 17:10:40',1,147),(41,'Thomas G.',NULL,4,'Tr??s ??quilibr??, textures int??ressantes.','2025-09-25 17:10:40',1,147),(42,'Claire P.',NULL,4,'Belle d??couverte v??g??tarienne.','2025-09-24 17:10:40',1,147),(43,'Julien R.',NULL,5,'Ultra frais, parfait l\'??t??.','2025-09-26 17:10:40',1,148),(44,'Sophie D.',NULL,4,'Le basilic et la f??ta apportent un plus.','2025-09-25 17:10:40',1,148),(45,'Antoine M.',NULL,4,'Tr??s agr??able.','2025-09-24 17:10:40',1,148),(46,'L??a C.',NULL,5,'Meringue parfaite, saveur de marrons au top.','2025-09-26 17:10:40',1,149),(47,'Nicolas B.',NULL,4,'Tr??s bon dessert de saison.','2025-09-25 17:10:40',1,149),(48,'??lodie F.',NULL,5,'Un r??gal !','2025-09-24 17:10:40',1,149),(49,'Hugo V.',NULL,5,'Ricotta/miel/fraises : accord gagnant.','2025-09-26 17:10:40',1,150),(50,'Camille S.',NULL,4,'L??ger et gourmand.','2025-09-25 17:10:40',1,150),(51,'Pauline T.',NULL,4,'Tr??s bon.','2025-09-24 17:10:40',1,150),(52,'Arthur K.',NULL,5,'Yuzu bien pr??sent, textures au top.','2025-09-26 17:10:40',1,151),(53,'Manon J.',NULL,4,'D??licieux et original.','2025-09-25 17:10:40',1,151),(54,'Lucas D.',NULL,5,'Mon dessert pr??f??r?? !','2025-09-24 17:10:40',1,151),(55,'Thomas G.',NULL,5,'Fumage ma??tris??, texture fondante.','2025-09-29 17:13:13',1,142),(56,'Claire P.',NULL,4,'Tr??s fin et original.','2025-09-29 17:13:13',1,142),(57,'Julien R.',NULL,5,'Explosion de saveurs.','2025-09-29 17:13:13',1,142),(103,'Marie Martinez',NULL,5,'Une brasserie authentique avec une ambiance chaleureuse ! J\'ai ador?? leur bouillabaisse, un vrai d??lice marseillais. Le service ??tait impeccable et l\'??quipe tr??s accueillante. La terrasse est parfaite pour les soir??es d\'??t??. Je recommande vivement !','2025-10-26 14:21:55',1,NULL),(104,'Sophie Rousseau',NULL,4,'Depuis qu\'on a d??couvert Le Trois Quarts, on y va r??guli??rement ! L\'atmosph??re est familiale, les portions g??n??reuses et les prix raisonnables. Leur tarte aux l??gumes du soleil est un d??lice. Les enfants adorent aussi, c\'est rare de nos jours !','2025-10-26 14:23:59',1,NULL),(105,'Jean-Claude Bernardin',NULL,5,'Une vraie brasserie marseillaise comme on les aime ! Produits frais, cuisine maison et ambiance d??contract??e. J\'ai pris leur pav?? de saumon aux herbes de Provence, c\'??tait parfait. Le patron prend toujours le temps de discuter avec ses clients. ?? bient??t !','2025-10-26 14:24:45',1,NULL),(106,'TNom','testkl@test.com',4,'????????????????????','2025-11-04 17:29:09',0,NULL),(107,'TNom','testkl@test.com',3,'??????????????????????','2025-11-04 17:29:29',0,NULL),(108,'TNom','testkl@test.com',4,'??????????????????????','2025-11-04 17:29:47',0,141),(109,'TNom','testkl@test.com',3,'hhhhhhhhhhhh','2025-11-04 18:44:07',0,138),(110,'Alice','alice@example.com',5,'Excellent restaurant! Great food and service.','2025-11-04 19:44:07',0,NULL),(111,'TNom','testkl@test.com',2,'aaaaaaaaaaaaaaaa','2025-11-05 17:20:12',0,NULL),(112,'TNom','testkl@test.com',4,'qqqqqqqqqqqqqq','2025-11-05 17:21:01',0,138),(113,'TNom','testkl@test.com',3,'ssssssssssss','2025-11-06 11:18:04',0,NULL),(114,'TNom','testkl@test.com',2,'ffffffffffffffffffffff','2025-11-06 18:39:17',0,NULL),(115,'TNom','testkl@test.com',3,'kkkkkkkkkkkkkkk','2025-11-06 18:39:49',0,145),(116,'TNom','testkl@test.com',3,'aaaaaaaaaaaaaaaaaaa','2025-11-06 19:03:34',0,NULL),(117,'TNom','testkl@test.com',3,'xxxxxxxxxxxx','2025-11-07 19:37:49',0,NULL),(118,'TNom','testkl@test.com',2,'gggggggggggggg','2025-11-08 12:58:57',0,NULL),(120,'TNom','testkl@test.com',3,'ffffffffffffffffff','2025-11-10 13:32:28',0,NULL),(121,'TNom','testkl@test.com',4,'rrrrrrrrrrrrrrrrrrrr','2025-11-10 13:32:49',0,139),(122,'Test',NULL,4,'test test test','2025-11-10 21:39:48',0,NULL);
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `zone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,'T01',2,'Salle'),(2,'T02',2,'Salle'),(3,'T03',2,'Salle'),(4,'T04',2,'Salle'),(5,'T05',4,'Salle'),(6,'T06',4,'Salle'),(7,'T07',4,'Salle');
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (31,'Vegetarian','vegetarian'),(32,'Vegan','vegan'),(33,'Sans gluten','glutenFree');
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `last_login_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,'admin@letroisquarts.com','$argon2id$v=19$m=65536,t=4,p=1$MHAuREdKY1pOVGRJbjlKRQ$+htZD/0EpWWL811jYRho+mUYjzk+XZZMlT9a2SD8vRI','Admin',1,'2025-09-29 16:15:41','2025-11-11 10:10:10','ROLE_ADMIN'),(10,'moderator@letroisquarts.com','$argon2id$v=19$m=65536,t=4,p=1$YVRjOTNoV0FyUTYzdEZpMw$qYD3/oA6vkUsRFstTdb2clrRQ/vGKSz49fxpnbv6wqo','Moderator',1,'2025-09-29 16:15:42','2025-10-29 17:46:38','ROLE_MODERATOR');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-11  9:40:32
