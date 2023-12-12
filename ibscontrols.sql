-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.19-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema andrew
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ andrew;
USE andrew;

--
-- Table structure for table `andrew`.`andrew_test`
--

DROP TABLE IF EXISTS `andrew_test`;
CREATE TABLE `andrew_test` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `addr` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `andrew`.`andrew_test`
--

/*!40000 ALTER TABLE `andrew_test` DISABLE KEYS */;
INSERT INTO `andrew_test` (`id`,`title`,`content`,`addr`,`time`) VALUES 
 (1,'andrewm_temco','hosting account, or choose \"Add A Domain Name\", to register a new domain name (if you have free domain credits, you can register a domain for free). This will open a new window. \r\nYou will arrive at the pre-filled order form. Provide your preferred payment method, if you want to use your free domain credits for registering a domain, simply choose \"Pay With Domain Points\" as payment method. That option will only be available if you have unused domain credits. \r\n','61.171.253.4','2007-12-03 09:56:59'),
 (2,'Î÷°àÑÀÀú½ì×ÛÊö£º°ëÊÀ¼Í¶áÒ»¹Ú ÉÏ½ìÃÆËÀÐ¡×éÈü','µÚÒ»½ìÅ·ÖÞ±­£¬Î÷°àÑÀÊ×ÂÖ7-2ÌÔÌ­²¨À¼£¬µ«´ÎÂÖÓöµ½ËÕÁª£¬ÓÉÓÚÕþÖÎÔ­Òò£¬¶À²ÃÕß·ðÀÊ¸çÊ§ÎóÓÒÒíÕþ¸®ÀÕÁîÎ÷°àÑÀ¶ÓÍË³ö±ÈÈü£¬½á¹ûËÕÁª¶Ó²»Õ½¶øÊ¤£¬×îºó³É¹¦µÇ¶¥¡£\r\n\r\nÉÏÊÀ¼Í80Äê´úÇ°£¬Å·ÖÞ±­»¹²»ÊÇÈü»áÖÆ£¬Ö÷¿Í³¡½»·æ¶Ô±ÈÈüÓ°ÏìÆÄ´ó£¬µ¥³¡ÌÔÌ­Èü³¡µØÓÉ³éÇ©¾ö¶¨¡£1964ÄêÎ÷°àÑÀ¾Í°ÑÎÕ×¡ÁË»ú»á£¬Ò»¾ÙÎÊ¶¦ËûÃÇÖÁ½ñÎ¨Ò»µÄ³ÉÄê¹ú¼Ò¶Ó´óÈü¹Ú¾ü£¨°ÂÔË»áÄÐ×ã²»Ëã£©¡£\r\n\r\nµ±ÄêÎ÷°àÑÀ¶ÓÊ×Õ½1-1±»±±°®¶ûÀ¼±ÆÆ½£¬Ó¢¸ñÀ¼¼®Ö÷Ë§»ôÀ¼ÕÙ»ØÔÚº£ÍâÐ§Á¦µÄ´óÅÆÇòÐÇ£¬×îºóÔÚ±´¶û·¨Ë¹ÌØ1-0»÷°Ü±±°®¡£´ÎÂÖÁ½»ØºÏ7-1ºáÉ¨°®¶ûÀ¼£¬°ë¾öÈü¶ÔÕóÇ¿µÐÐÙÑÀÀû£¬°¢ÂüÎ÷°Â¼ÓÊ±ÈüµÄ½øÇò±£Ö¤¶·Å£Ê¿2-1¹ý¹Ø¡£¾öÈüÔÚ²®ÄÉÎÚ¾ÞÐÇ£¬Âí¶¡ÄÚË¹ÖÕ³¡Ç°¾øÉ±ËÕÁª£¬Î÷°àÑÀÔÚ¼ÒÃÅ¿ÚÅõÆðÅ·ÖÞ±­¡£\r\n\r\n','61.171.253.4','2007-12-03 10:04:55'),
 (3,'variant_date_to_timestamp','variant_date_to_timestampvariant_date_to_timestampvariant_date_to_timestamp fumction make data/time variant as unix time stamp','61.171.253.4','2007-12-03 10:07:58');
INSERT INTO `andrew_test` (`id`,`title`,`content`,`addr`,`time`) VALUES 
 (4,'WELCOME TO TEMCO CONTOLS LTD','We manufacture HVAC controls and a range of HVAC sensors, relays, valves and thermostats at some of the best prices on the globe!\r\n','58.41.81.215','2007-12-03 20:48:33'),
 (5,'','','61.171.118.168','2007-12-04 07:01:40'),
 (6,'PHPÉú³É´øÓÐÑ©»¨±³¾°µÄÑéÖ¤Âë','<?session_start();?>\r\n<FORM METHOD=POST ACTION=\"\">\r\n<input type=text name=number maxlength=4><img src=\"YanZhengMa.php?act=init\">\r\n<INPUT TYPE=\"submit\" name=\"sub\">\r\n</FORM>\r\n<?\r\n//¼ìÑéÐ£ÑéÂë\r\nif(isset($HTTP_POST_VARS[\"sub\"])):\r\nif($HTTP_POST_VARS[\"number\"] != $HTTP_SESSION_VARS[login_check_number] || empty($HTTP_POST_VARS[\"number\"])){\r\n    echo \"Ð£ÑéÂë²»ÕýÈ·!\" ;\r\n}else{\r\n    echo\"ÑéÖ¤ÂëÍ¨¹ý£¡\";\r\n}\r\nendif;\r\nshow_source(\'test.php\');\r\n//ÒÔÉÏ±¾Ò³µÄÔ´Âë\r\n\r\n\r\n//ÒÔÏÂÊÇÉú³ÉÑéÖ¤ÂëµÄÔ´Âë\r\nshow_source(\'YanZhengMa.php\');\r\n?>\r\n<?php\r\nsession_start();\r\nsession_register(\"login_check_number\");\r\n//×òÍí¿´µ½ÁËchianrenÉÏµÄÑéÖ¤ÂëÐ§¹û£¬¾Í¿¼ÂÇÁËÒ»ÏÂ£¬ÓÃPHPµÄGD¿âÍê³ÉÁËÀàËÆ¹¦ÄÜ\r\n//ÏÈ³ÉÉú±³¾°£¬ÔÙ°ÑÉú³ÉµÄÑéÖ¤Âë·ÅÉÏÈ¥\r\n$img_height=120;    //ÏÈ¶¨ÒåÍ¼Æ¬µÄ³¤¡¢¿í\r\n$img_width=40;\r\nif($HTTP_GET_VARS[\"act\"]== \"init\"){\r\n    //srand(microtime() * 100000);//PHP420ºó£¬srand²»ÊÇ±ØÐëµÄ\r\n    for($Tmpa=0;$Tmpa<4;$Tmpa++){\r\n        $nmsg.=dechex(rand(0,15));\r\n    }//by sports98\r\n\r\n\r\n    $HTTP_SESSION_VARS[login_check_number] = $nmsg;\r\n\r\n    //$HTTP_SESSION_VARS[login_check_number] = strval(mt_rand(\"1111\",\"9999\"));    //Éú³É4Î»µÄËæ»úÊý£¬·ÅÈësessionÖÐ\r\n    //Ë­ÄÜ×öÏÂ²¹³ä£¬¿ÉÒÔÍ¬Ê±Éú³É×ÖÄ¸ºÍÊý×Ö°¡£¿£¿----ÓÉsports98Íê³ÉÁË\r\n\r\n    $aimg = imageCreate($img_height,$img_width);    //Éú³ÉÍ¼Æ¬\r\n    ImageColorAllocate($aimg, 255,255,255);            //Í¼Æ¬µ×É«£¬ImageColorAllocateµÚ1´Î¶¨ÒåÑÕÉ«PHP¾ÍÈÏÎªÊÇµ×É«ÁË\r\n    $black = ImageColorAllocate($aimg, 0,0,0);        //¶¨ÒåÐèÒªµÄºÚÉ«\r\n    ImageRectangle($aimg,0,0,$img_height-1,$img_width-1,$black);//ÏÈ³ÉÒ»ºÚÉ«µÄ¾ØÐÎ°ÑÍ¼Æ¬°üÎ§\r\n\r\n    //ÏÂÃæ¸ÃÉú³ÉÑ©»¨±³¾°ÁË£¬ÆäÊµ¾ÍÊÇÔÚÍ¼Æ¬ÉÏÉú³ÉÒ»Ð©·ûºÅ\r\n    for ($i=1; $i<=100; $i++) {    //ÏÈÓÃ100¸ö×ö²âÊÔ\r\n        imageString($aimg,1,mt_rand(1,$img_height),mt_rand(1,$img_width),\"*\",imageColorAllocate($aimg,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255)));\r\n        //¹þ£¬¿´µ½ÁË°É£¬ÆäÊµÒ²²»ÊÇÑ©»¨£¬¾ÍÊÇÉú³É£ªºÅ¶øÒÑ¡£ÎªÁËÊ¹ËüÃÇ¿´ÆðÀ´\"ÔÓÂÒÎÞÕÂ¡¢5ÑÕ6É«\"£¬¾ÍµÃÔÚ1¸ö1¸öÉú³ÉËüÃÇµÄÊ±ºò£¬ÈÃËüÃÇµÄÎ»ÖÃ¡¢ÑÕÉ«£¬ÉõÖÁ´óÐ¡¶¼ÓÃËæ»úÊý£¬rand()»òmt_rand¶¼¿ÉÒÔÍê³É¡£\r\n    }\r\n\r\n    //ÉÏÃæÉú³ÉÁË±³¾°£¬ÏÖÔÚ¾Í¸Ã°ÑÒÑ¾­Éú³ÉµÄËæ»úÊý·ÅÉÏÀ´ÁË¡£µÀÀíºÍÉÏÃæ²î²»¶à£¬Ëæ»úÊý1¸ö1¸öµØ·Å£¬Í¬Ê±ÈÃËûÃÇµÄÎ»ÖÃ¡¢´óÐ¡¡¢ÑÕÉ«¶¼ÓÃ³ÉËæ»úÊý~~\r\n    //ÎªÁËÇø±ðÓÚ±³¾°£¬ÕâÀïµÄÑÕÉ«²»³¬¹ý200£¬ÉÏÃæµÄ²»Ð¡ÓÚ200\r\n    for ($i=0;$i<strlen($HTTP_SESSION_VARS[login_check_number]);$i++){\r\n        imageString($aimg, mt_rand(3,5),$i*$img_height/4+mt_rand(1,10),mt_rand(1,$img_width/2), $HTTP_SESSION_VARS[login_check_number][$i],imageColorAllocate($aimg,mt_rand(0,100),mt_rand(0,150),mt_rand(0,200)));\r\n    }\r\n    Header(\"Content-type: image/png\");    //¸æËßä¯ÀÀÆ÷£¬ÏÂÃæµÄÊý¾ÝÊÇÍ¼Æ¬£¬¶ø²»Òª°´ÎÄ×ÖÏÔÊ¾\r\n    ImagePng($aimg);                    //Éú³Épng¸ñÊ½¡£¡£¡£ºÙºÙÐ§¹ûÂùÏñ»ØÊÂµÄÂï¡£¡£¡£\r\n    ImageDestroy($aimg);\r\n}\r\n\r\n?> ','58.41.81.215','2007-12-04 20:56:35'),
 (7,'T3-8-13Relay','The T3-8-13Relay is an Analog Input & Relay Output Module with 8 Analog Input Channels & 13 Relay Output Channels on board.\r\n\r\nThe inputs can be configured to take 0-5V signals, 0-20mA signals, Thermistor temperature sensors, Dry Contact Closures, or high speed pulse inputs. The Ai8_R13 converts any analog signals into a 10-bit value between 0-1024.\r\n\r\nThe Relay Outputs are rated for 1Amp @ 24VAC and can be used to control various types of On/Off devices. Each Relay can be controlled through software or can be controlled at the device manually by using the built-in Hand/Off/Auto switches that are provided. Indicator LEDs for the inputs and outputs provide a visual indication of the current status of each point.\r\n\r\nThe Ai8_R13 can communicate with a higher level control system via Modbus485. The units are also accessible over Ethernet by using the Ai8_R13 BarioNet Gateway, which communicates with up to 8 of the modules concurrently and exposes all of the input values & allows control of the outputs via HTTP, TCP, or ModbusTCP. ','61.171.119.209','2007-12-05 18:51:47');
INSERT INTO `andrew_test` (`id`,`title`,`content`,`addr`,`time`) VALUES 
 (8,'Tstat 5 Series','CPU based Zone Controller Wide variety of control strategies. NETWORK port for simple MODBUS communications, a real gem for integrators. Now with FLASH update over the network, 100% and highly customizable sequences. ','61.171.119.209','2007-12-05 18:52:16'),
 (9,'Differential air pressure switch ','Application This presure switch can be used to sense(differential) presure and flow of air in ducts and popes.Typical applications include: \r\nClogged filter detection. \r\nDetection of icing on air conditioning coils and initiation of defrost cycle \r\nAir proving in heading or ventilation ducts. \r\nmaximumair flow cpntroller for variable air volume systems \r\nBurner air control ','61.171.119.209','2007-12-05 18:52:47');
/*!40000 ALTER TABLE `andrew_test` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `SessionID` varchar(255) NOT NULL default '',
  `ProductID` int(11) NOT NULL default '0',
  `AttributeID` int(11) NOT NULL default '0',
  `Qty` int(11) NOT NULL default '0',
  `DeleteFlag` tinyint(1) NOT NULL default '0',
  KEY `SessionID` (`SessionID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`cart_items`
--

/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL auto_increment,
  `ParentID` int(11) NOT NULL default '0',
  `CategoryName` varchar(25) NOT NULL default '',
  `CategoryDescription` varchar(255) NOT NULL default '',
  `PageText` text NOT NULL,
  `PageFormat` char(1) NOT NULL default 't',
  `Display` tinyint(1) NOT NULL default '1',
  `InMenu` tinyint(1) NOT NULL default '0',
  `MenuOrder` int(11) NOT NULL default '1',
  `CreatedDate` date NOT NULL default '0000-00-00',
  `LastModDate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`CategoryID`),
  KEY `parent_id` (`ParentID`),
  KEY `name` (`CategoryName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`categories`
--

/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`CategoryID`,`ParentID`,`CategoryName`,`CategoryDescription`,`PageText`,`PageFormat`,`Display`,`InMenu`,`MenuOrder`,`CreatedDate`,`LastModDate`) VALUES 
 (1,0,'Temp Sensor','Temperature sensor','Temperature sensor','t',1,1,1,'2008-04-29','2008-04-29'),
 (2,0,'Current Sensor','Current Sensor','Current Sensor','t',1,1,2,'2008-04-29','2008-04-29'),
 (3,0,'Control Valves','Control Valves Zone valves','Zone Valve','t',1,1,3,'2008-04-29','2008-05-10'),
 (4,0,'RTD Sensor','RTD Sensor','RTD Sensor','t',1,1,4,'2008-04-29','2008-05-17'),
 (5,0,'Solid Relays','Solid Relays','Solid Relays','t',1,1,5,'2008-04-29','2008-04-29'),
 (6,0,'Enclosures','Plastic Enclosure','Plastic Enclosure','t',1,1,6,'2008-05-10','2008-05-10'),
 (7,0,'Converters','Converters','Converters','t',1,1,7,'2008-05-13','2008-05-13'),
 (8,0,'PCB Design&Test','PCB Design&Test','PCB Design&Test','t',1,1,8,'2008-05-17','2008-05-17'),
 (9,0,'Motorized Valve','Motorized Valve','Motorized Valve','t',1,1,9,'2008-05-19','2008-05-19');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `CompanyID` int(11) NOT NULL auto_increment,
  `CompanyName` varchar(75) NOT NULL default '',
  `CompanyDescription` varchar(255) NOT NULL default '',
  `PageText` text NOT NULL,
  `PageFormat` char(1) NOT NULL default 't',
  `Display` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`CompanyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`companies`
--

/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL auto_increment,
  `OrderID` int(11) NOT NULL default '0',
  `ProductID` int(11) NOT NULL default '0',
  `AttributeID` int(11) NOT NULL default '0',
  `Price` float(7,2) NOT NULL default '0.00',
  `Qty` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`OrderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`order_items`
--

/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` (`id`,`OrderID`,`ProductID`,`AttributeID`,`Price`,`Qty`) VALUES 
 (1,1,9,9,150.00,1);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL auto_increment,
  `OrderStatus` varchar(25) NOT NULL default 'New',
  `OrderDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Username` varchar(32) NOT NULL default '',
  `Company` varchar(255) NOT NULL default '',
  `Title` varchar(255) NOT NULL default '',
  `FirstName` varchar(255) NOT NULL default '',
  `LastName` varchar(255) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `Telephone` varchar(255) NOT NULL default '',
  `Extension` varchar(255) NOT NULL default '',
  `Fax` varchar(255) NOT NULL default '',
  `BillingAddress` varchar(255) NOT NULL default '',
  `BillingAddress2` varchar(255) NOT NULL default '',
  `BillingCity` varchar(255) NOT NULL default '',
  `BillingState` varchar(255) NOT NULL default '',
  `BillingZip` varchar(255) NOT NULL default '',
  `BillingCountry` varchar(255) NOT NULL default '',
  `ShippingCompany` varchar(255) NOT NULL default '',
  `ShippingAddress` varchar(255) NOT NULL default '',
  `ShippingAddress2` varchar(255) NOT NULL default '',
  `ShippingCity` varchar(255) NOT NULL default '',
  `ShippingState` varchar(255) NOT NULL default '',
  `ShippingZip` varchar(255) NOT NULL default '',
  `ShippingCountry` varchar(255) NOT NULL default '',
  `ShippingChoice` varchar(255) NOT NULL default '',
  `ShippingWeight` float(5,2) NOT NULL default '0.00',
  `Comments` text NOT NULL,
  `MailingList` varchar(5) NOT NULL default '',
  `UPSMethod` varchar(5) NOT NULL default '',
  `UPSErrorMsg` varchar(255) NOT NULL default '',
  `SubTotal` float(7,2) NOT NULL default '0.00',
  `Tax` float(7,2) NOT NULL default '0.00',
  `Shipping` float(7,2) NOT NULL default '0.00',
  `ShippingExtra` float(7,2) NOT NULL default '0.00',
  `ShippingCredit` float(7,2) NOT NULL default '0.00',
  `EstShipDate` date NOT NULL default '0000-00-00',
  `TrackingUPS` varchar(255) NOT NULL default '',
  `TrackingAirborne` varchar(255) NOT NULL default '',
  `TrackingUSPS` varchar(255) NOT NULL default '',
  `CommentsAdmin` text NOT NULL,
  PRIMARY KEY  (`OrderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dumping data for table `andrew`.`orders`
--

/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`OrderID`,`OrderStatus`,`OrderDate`,`Username`,`Company`,`Title`,`FirstName`,`LastName`,`Email`,`Telephone`,`Extension`,`Fax`,`BillingAddress`,`BillingAddress2`,`BillingCity`,`BillingState`,`BillingZip`,`BillingCountry`,`ShippingCompany`,`ShippingAddress`,`ShippingAddress2`,`ShippingCity`,`ShippingState`,`ShippingZip`,`ShippingCountry`,`ShippingChoice`,`ShippingWeight`,`Comments`,`MailingList`,`UPSMethod`,`UPSErrorMsg`,`SubTotal`,`Tax`,`Shipping`,`ShippingExtra`,`ShippingCredit`,`EstShipDate`,`TrackingUPS`,`TrackingAirborne`,`TrackingUSPS`,`CommentsAdmin`) VALUES 
 (1,'Order Status:','2008-05-01 16:00:59','','fdsf','','sfsdf','sdfsdfsd','renwei98@gmail.com','321312321312','','','312312313123','12312','3123123','12312312','dsad','AT','dsadasdasdas','312312313123','12312','3123123','','dsad','AT','',0.30,'dsdasda','Yes','GND','',150.00,0.00,25.00,0.00,0.00,'2008-05-01','','','','');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`payment_authnet`
--

DROP TABLE IF EXISTS `payment_authnet`;
CREATE TABLE `payment_authnet` (
  `ID` int(11) NOT NULL auto_increment,
  `DateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `ResponseCode` varchar(255) NOT NULL default '',
  `ResponseSubcode` varchar(255) NOT NULL default '',
  `ResponseReasonCode` varchar(255) NOT NULL default '',
  `ResponseReasonText` varchar(255) NOT NULL default '',
  `ApprovalCode` varchar(255) NOT NULL default '',
  `AVSResultCode` varchar(255) NOT NULL default '',
  `TransactionID` varchar(255) NOT NULL default '',
  `InvoiceNumber` varchar(255) NOT NULL default '',
  `Description` varchar(255) NOT NULL default '',
  `Amount` varchar(255) NOT NULL default '',
  `Method` varchar(255) NOT NULL default '',
  `TransactionType` varchar(255) NOT NULL default '',
  `CustomerID` varchar(255) NOT NULL default '',
  `CardholderFirstName` varchar(255) NOT NULL default '',
  `CardholderLastName` varchar(255) NOT NULL default '',
  `Company` varchar(255) NOT NULL default '',
  `BillingAddress` varchar(255) NOT NULL default '',
  `City` varchar(255) NOT NULL default '',
  `State` varchar(255) NOT NULL default '',
  `Zip` varchar(255) NOT NULL default '',
  `Country` varchar(255) NOT NULL default '',
  `Phone` varchar(255) NOT NULL default '',
  `Fax` varchar(255) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `ShipToFirstName` varchar(255) NOT NULL default '',
  `ShipToLastName` varchar(255) NOT NULL default '',
  `ShipToCompany` varchar(255) NOT NULL default '',
  `ShipToAddress` varchar(255) NOT NULL default '',
  `ShipToCity` varchar(255) NOT NULL default '',
  `ShipToState` varchar(255) NOT NULL default '',
  `ShipToZip` varchar(255) NOT NULL default '',
  `ShipToCountry` varchar(255) NOT NULL default '',
  `TaxAmount` varchar(255) NOT NULL default '',
  `DutyAmount` varchar(255) NOT NULL default '',
  `FreightAmount` varchar(255) NOT NULL default '',
  `TaxExemptFlag` varchar(255) NOT NULL default '',
  `PONumber` varchar(255) NOT NULL default '',
  `MD5Hash` varchar(255) NOT NULL default '',
  `CardCode` varchar(255) NOT NULL default '',
  `SessionID` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dumping data for table `andrew`.`payment_authnet`
--

/*!40000 ALTER TABLE `payment_authnet` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_authnet` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`payment_cc`
--

DROP TABLE IF EXISTS `payment_cc`;
CREATE TABLE `payment_cc` (
  `ID` int(9) unsigned NOT NULL auto_increment,
  `DateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `OrderID` int(11) NOT NULL default '0',
  `CCName` varchar(255) NOT NULL default '',
  `CCType` varchar(255) NOT NULL default '',
  `CCNumber` blob NOT NULL,
  `CCSafe` varchar(16) NOT NULL default '',
  `CCMonth` tinyint(2) unsigned NOT NULL default '0',
  `CCYear` mediumint(4) unsigned NOT NULL default '0',
  `CCCvv` blob NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`payment_cc`
--

/*!40000 ALTER TABLE `payment_cc` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_cc` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`payment_manual`
--

DROP TABLE IF EXISTS `payment_manual`;
CREATE TABLE `payment_manual` (
  `ID` int(11) NOT NULL auto_increment,
  `DateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `OrderID` varchar(255) NOT NULL default '',
  `PaymentAmount` varchar(255) NOT NULL default '',
  `PaymentType` varchar(255) NOT NULL default '',
  `PaymentNotes` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dumping data for table `andrew`.`payment_manual`
--

/*!40000 ALTER TABLE `payment_manual` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_manual` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`payment_paypal`
--

DROP TABLE IF EXISTS `payment_paypal`;
CREATE TABLE `payment_paypal` (
  `ID` int(11) NOT NULL auto_increment,
  `DateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `receiver_email` varchar(255) NOT NULL default '',
  `item_name` varchar(255) NOT NULL default '',
  `item_number` varchar(255) NOT NULL default '',
  `quantity` varchar(255) NOT NULL default '',
  `invoice` varchar(255) NOT NULL default '',
  `custom` varchar(255) NOT NULL default '',
  `num_cart_items` varchar(255) NOT NULL default '',
  `payment_status` varchar(255) NOT NULL default '',
  `pending_reason` varchar(255) NOT NULL default '',
  `payment_date` varchar(255) NOT NULL default '',
  `payment_gross` varchar(255) NOT NULL default '',
  `payment_fee` varchar(255) NOT NULL default '',
  `txn_id` varchar(255) NOT NULL default '',
  `txn_type` varchar(255) NOT NULL default '',
  `first_name` varchar(255) NOT NULL default '',
  `last_name` varchar(255) NOT NULL default '',
  `address_street` varchar(255) NOT NULL default '',
  `address_city` varchar(255) NOT NULL default '',
  `address_state` varchar(255) NOT NULL default '',
  `address_zip` varchar(255) NOT NULL default '',
  `address_country` varchar(255) NOT NULL default '',
  `address_status` varchar(255) NOT NULL default '',
  `payer_email` varchar(255) NOT NULL default '',
  `payer_status` varchar(255) NOT NULL default '',
  `payment_type` varchar(255) NOT NULL default '',
  `notify_version` varchar(255) NOT NULL default '',
  `notify_sign` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dumping data for table `andrew`.`payment_paypal`
--

/*!40000 ALTER TABLE `payment_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_paypal` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL auto_increment,
  `CompanyID` int(11) NOT NULL default '0',
  `ProductName` varchar(50) NOT NULL default '',
  `ProductDescription` varchar(255) NOT NULL default '',
  `PageText` text NOT NULL,
  `PageFormat` char(1) NOT NULL default 't',
  `OnSpecial` tinyint(1) NOT NULL default '0',
  `CreatedDate` date NOT NULL default '0000-00-00',
  `LastModDate` date NOT NULL default '0000-00-00',
  `Display` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ProductID`),
  KEY `name` (`ProductName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`products`
--

/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`ProductID`,`CompanyID`,`ProductName`,`ProductDescription`,`PageText`,`PageFormat`,`OnSpecial`,`CreatedDate`,`LastModDate`,`Display`) VALUES 
 (1,0,'Current Sensor','Transmitters, Hardware Web Site includes  Transmitters, Hardware Web Site includes  Transmitters, ','Current Sensor','t',1,'2008-04-29','2008-05-05',1),
 (2,0,'Sensor -no.2','Sensor -no.2','Sensor -no.2','t',1,'2008-04-29','2008-04-29',1),
 (3,0,'Sensor- No.3','Sensor- No.3','Sensor- No.3','t',1,'2008-04-29','2008-04-29',1),
 (4,0,'Solid State Relays','Solid State Relays','Solid State Relays','t',1,'2008-04-29','2008-04-29',1),
 (5,0,'Solid State Relay2','Solid State Relay2','Solid State Relay2','t',1,'2008-04-29','2008-04-29',1),
 (6,0,'Mechnical Relays','Mechnical Relays','Mechnical Relays','t',1,'2008-04-29','2008-04-29',1),
 (7,0,'Valve-No1','Valve-No1','<img src=\"/images/products/7_01_th.jpg\" alt=\"\" width=\"100\" height=\"120\" border=\"0\" />','t',1,'2008-05-01','2008-05-01',1),
 (8,0,'Valve-No2','Valve-No2','Valve-No2\n\n<img src=\"/images/products/8_01_th.gif\" alt=\"\" width=\"100\" height=\"120\" border=\"0\" />','t',1,'2008-05-01','2008-05-01',1),
 (9,0,'Valve-No.3','Valve-No.3','Valve-No.3','t',1,'2008-05-01','2008-05-01',1);
INSERT INTO `products` (`ProductID`,`CompanyID`,`ProductName`,`ProductDescription`,`PageText`,`PageFormat`,`OnSpecial`,`CreatedDate`,`LastModDate`,`Display`) VALUES 
 (10,0,'Plastic enclosure no.1','Plastic enclosure no.1','Plastic enclosure no.1','t',0,'2008-05-10','2008-05-10',1),
 (11,0,'Plastic Enclosures','Plastic Enclosures','Plastic Enclosures','t',0,'2008-05-10','2008-05-10',1),
 (12,0,'Plastic Enclosure no 3','Plastic Enclosure no 3','Plastic Enclosure no 3','t',0,'2008-05-10','2008-05-10',1),
 (13,0,'Plastic Enclosure no 4','Plastic Enclosure no 4','Plastic Enclosure no 4','t',0,'2008-05-10','2008-05-10',1),
 (14,0,'Plastic Enclosure no 5','Plastic Enclosure no 5','Plastic Enclosure no 5','t',0,'2008-05-10','2008-05-10',1),
 (15,0,'Plastic Enclosure no 6','Plastic Enclosure no 6','Plastic Enclosure no 6','t',0,'2008-05-10','2008-05-10',1),
 (16,0,'Plastic Enclosure no 7','Plastic Enclosure no 7','Plastic Enclosure no 7','t',0,'2008-05-10','2008-05-10',1),
 (17,0,'TS-1001','Temperature sensor','Temperature sensor 1001 esay to install','t',0,'2008-05-13','2008-05-13',1),
 (18,0,'TS-1002','Temperature sensor 1002','Temperature sensor 1002','t',0,'2008-05-13','2008-05-13',1);
INSERT INTO `products` (`ProductID`,`CompanyID`,`ProductName`,`ProductDescription`,`PageText`,`PageFormat`,`OnSpecial`,`CreatedDate`,`LastModDate`,`Display`) VALUES 
 (19,0,'TS-1003','TS-1003','TS-1003','t',0,'2008-05-13','2008-05-13',1),
 (22,0,'USB to 232','USB to 232','USB to 232','t',0,'2008-05-17','2008-05-19',1),
 (21,0,'USB to 485/422','USB to 485/422','USB to 485/422','t',0,'2008-05-17','2008-05-17',1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`products_attributes`
--

DROP TABLE IF EXISTS `products_attributes`;
CREATE TABLE `products_attributes` (
  `ProductID` int(11) NOT NULL default '0',
  `AttributeID` int(11) NOT NULL auto_increment,
  `SKU` varchar(25) NOT NULL default '',
  `UPC` varchar(32) NOT NULL default '',
  `AttributeName` varchar(50) NOT NULL default '',
  `AttributeOrder` int(11) NOT NULL default '0',
  `AttributeCost` float(7,2) NOT NULL default '0.00',
  `AttributePrice` float(7,2) NOT NULL default '0.00',
  `ShippingPrice` float(7,2) NOT NULL default '0.00',
  `ShippingWeight` float(7,2) NOT NULL default '0.00',
  `ShippingLength` float(7,2) NOT NULL default '0.00',
  `ShippingWidth` float(7,2) NOT NULL default '0.00',
  `ShippingHeight` float(7,2) NOT NULL default '0.00',
  `Display` tinyint(1) NOT NULL default '0',
  `AttribtDescriptions` varchar(150) default NULL,
  `discountPrice` float(7,2) NOT NULL default '0.00',
  `oemPrice` float(7,2) NOT NULL default '0.00',
  `datasheet` varchar(255) default NULL,
  PRIMARY KEY  (`AttributeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`products_attributes`
--

/*!40000 ALTER TABLE `products_attributes` DISABLE KEYS */;
INSERT INTO `products_attributes` (`ProductID`,`AttributeID`,`SKU`,`UPC`,`AttributeName`,`AttributeOrder`,`AttributeCost`,`AttributePrice`,`ShippingPrice`,`ShippingWeight`,`ShippingLength`,`ShippingWidth`,`ShippingHeight`,`Display`,`AttribtDescriptions`,`discountPrice`,`oemPrice`,`datasheet`) VALUES 
 (1,1,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (2,2,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (3,3,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (4,4,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (5,5,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (6,6,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (7,7,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (8,8,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (9,9,'','','Valve-No.3',1,0.00,150.00,1.00,0.30,0.00,0.00,0.00,1,'Valve-No.3 Valve-No.3',0.00,0.00,NULL);
INSERT INTO `products_attributes` (`ProductID`,`AttributeID`,`SKU`,`UPC`,`AttributeName`,`AttributeOrder`,`AttributeCost`,`AttributePrice`,`ShippingPrice`,`ShippingWeight`,`ShippingLength`,`ShippingWidth`,`ShippingHeight`,`Display`,`AttribtDescriptions`,`discountPrice`,`oemPrice`,`datasheet`) VALUES 
 (10,10,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (11,11,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (12,12,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (13,13,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (14,14,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (15,15,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (16,16,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (17,17,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (18,18,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL);
INSERT INTO `products_attributes` (`ProductID`,`AttributeID`,`SKU`,`UPC`,`AttributeName`,`AttributeOrder`,`AttributeCost`,`AttributePrice`,`ShippingPrice`,`ShippingWeight`,`ShippingLength`,`ShippingWidth`,`ShippingHeight`,`Display`,`AttribtDescriptions`,`discountPrice`,`oemPrice`,`datasheet`) VALUES 
 (19,19,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (22,22,'','','USB-232',1,0.00,45.00,1.00,0.50,0.00,0.00,0.00,1,NULL,0.00,0.00,NULL),
 (21,21,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (20,23,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL),
 (0,24,'','','Base Product',1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0,NULL,0.00,0.00,NULL);
/*!40000 ALTER TABLE `products_attributes` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`products_categories`
--

DROP TABLE IF EXISTS `products_categories`;
CREATE TABLE `products_categories` (
  `ProductID` int(11) NOT NULL default '0',
  `CategoryID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ProductID`,`CategoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`products_categories`
--

/*!40000 ALTER TABLE `products_categories` DISABLE KEYS */;
INSERT INTO `products_categories` (`ProductID`,`CategoryID`) VALUES 
 (1,2),
 (2,2),
 (3,2),
 (4,5),
 (5,5),
 (6,5),
 (7,3),
 (8,3),
 (9,3),
 (10,6),
 (11,6),
 (12,6),
 (13,6),
 (14,6),
 (15,6),
 (16,6),
 (17,1),
 (18,1),
 (19,1),
 (21,7),
 (22,7);
/*!40000 ALTER TABLE `products_categories` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL default '',
  `data` text NOT NULL,
  `lastaccess` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`sessions`
--

/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` (`id`,`data`,`lastaccess`) VALUES 
 ('c7f3d1a79fbd6ee2b1fc86819ce47f48','UPSChoice|s:3:\"GND\";','2008-05-21 21:01:50'),
 ('86c62ec7f7a6e0a61b0696f5d5f658ad','UPSChoice|s:3:\"GND\";','2008-05-21 21:04:00'),
 ('e2a8f56e878f26eb75de6f2ef1dde45f','UPSChoice|s:3:\"GND\";','2008-05-21 20:56:03');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL auto_increment,
  `Company` varchar(255) NOT NULL default '',
  `Address1` varchar(255) NOT NULL default '',
  `Address2` varchar(255) NOT NULL default '',
  `City` varchar(255) NOT NULL default '',
  `State` varchar(255) NOT NULL default '',
  `Zip` varchar(255) NOT NULL default '',
  `Country` varchar(255) NOT NULL default '',
  `Telephone1` varchar(255) NOT NULL default '',
  `Telephone2` varchar(255) NOT NULL default '',
  `FAX` varchar(255) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `URL` varchar(255) NOT NULL default '',
  `CopyrightYear` varchar(255) NOT NULL default '',
  `ProductsPerPage` varchar(255) NOT NULL default '',
  `NumberOfColumns` varchar(255) NOT NULL default '',
  `ShowCartOnAdd` varchar(3) NOT NULL default '',
  `ShowShippingFields` varchar(3) NOT NULL default '',
  `OrderProductsBy` varchar(255) NOT NULL default '',
  `ShowImage` varchar(3) NOT NULL default '',
  `ShowProductID` varchar(3) NOT NULL default '',
  `ShowProductName` varchar(3) NOT NULL default '',
  `ShowShortDesc` varchar(3) NOT NULL default '',
  `ShowPrice` varchar(3) NOT NULL default '',
  `ShowMoreInfo` varchar(3) NOT NULL default '',
  `ShowQuantityBox` varchar(3) NOT NULL default '',
  `ShowBuyButton` varchar(3) NOT NULL default '',
  `ShowAddFreeItemToCart` varchar(3) NOT NULL default '',
  `EmailNotifications` varchar(255) NOT NULL default '',
  `PaymentPaypalTestmode` varchar(255) NOT NULL default '',
  `PaymentPaypalEmail` varchar(255) NOT NULL default '',
  `PaymentPaypalOrderButton` varchar(255) NOT NULL default '',
  `PaymentAuthnetTestmode` varchar(255) NOT NULL default '',
  `PaymentAuthnetLogin` varchar(255) NOT NULL default '',
  `PaymentAuthnetKey` varchar(255) NOT NULL default '',
  `PaymentAuthnetType` varchar(255) NOT NULL default '',
  `PaymentAuthnetOrderButton` varchar(255) NOT NULL default '',
  `PaymentCCCardsAccepted` varchar(255) NOT NULL default '',
  `PaymentCCValidate` varchar(255) NOT NULL default '',
  `PaymentCCOrderButton` varchar(255) NOT NULL default '',
  `PaymentManualOrderButton` varchar(255) NOT NULL default '',
  `ShippingOptionChoice` varchar(255) NOT NULL default '',
  `FreeIfOver` varchar(255) NOT NULL default '',
  `Option1RPO` varchar(255) NOT NULL default '',
  `Option2RPO` varchar(255) NOT NULL default '',
  `Option2CPI` varchar(255) NOT NULL default '',
  `Option4Max1` varchar(255) NOT NULL default '',
  `Option4Total1` varchar(255) NOT NULL default '',
  `Option4Max2` varchar(255) NOT NULL default '',
  `Option4Total2` varchar(255) NOT NULL default '',
  `Option4Max3` varchar(255) NOT NULL default '',
  `Option4Total3` varchar(255) NOT NULL default '',
  `Option4Max4` varchar(255) NOT NULL default '',
  `Option4Total4` varchar(255) NOT NULL default '',
  `Option4Max5` varchar(255) NOT NULL default '',
  `Option4Total5` varchar(255) NOT NULL default '',
  `Option5Max1` varchar(255) NOT NULL default '',
  `Option5Total1` varchar(255) NOT NULL default '',
  `Option5Max2` varchar(255) NOT NULL default '',
  `Option5Total2` varchar(255) NOT NULL default '',
  `Option5Max3` varchar(255) NOT NULL default '',
  `Option5Total3` varchar(255) NOT NULL default '',
  `Option5Max4` varchar(255) NOT NULL default '',
  `Option5Total4` varchar(255) NOT NULL default '',
  `Option5Max5` varchar(255) NOT NULL default '',
  `Option5Total5` varchar(255) NOT NULL default '',
  `Option6Max1` varchar(255) NOT NULL default '',
  `Option6Total1` varchar(255) NOT NULL default '',
  `Option6Max2` varchar(255) NOT NULL default '',
  `Option6Total2` varchar(255) NOT NULL default '',
  `Option6Max3` varchar(255) NOT NULL default '',
  `Option6Total3` varchar(255) NOT NULL default '',
  `Option6Max4` varchar(255) NOT NULL default '',
  `Option6Total4` varchar(255) NOT NULL default '',
  `Option6Max5` varchar(255) NOT NULL default '',
  `Option6Total5` varchar(255) NOT NULL default '',
  `Option6Max6` varchar(255) NOT NULL,
  `Option6Total6` varchar(255) NOT NULL,
  `Option6Max7` varchar(255) NOT NULL,
  `Option6Total7` varchar(255) NOT NULL,
  `Option6Max8` varchar(255) NOT NULL,
  `Option6Total8` varchar(255) NOT NULL,
  `Option6Max9` varchar(255) NOT NULL,
  `Option6Total9` varchar(255) NOT NULL,
  `Option6Max10` varchar(255) NOT NULL,
  `Option6Total10` varchar(255) NOT NULL,
  `Option6Max11` text NOT NULL,
  `Option6Total11` int(11) NOT NULL,
  `Option6Max12` char(8) NOT NULL,
  `Option6Total12` char(8) NOT NULL,
  `Option6Max13` text NOT NULL,
  `Option6Total13` text NOT NULL,
  `Option6Max14` text NOT NULL,
  `Option6Total14` text NOT NULL,
  `Option6Max15` text NOT NULL,
  `Option6Total15` text NOT NULL,
  `Option6Max16` text NOT NULL,
  `Option6Total16` text NOT NULL,
  `Option6Max17` int(11) NOT NULL,
  `Option6Total17` char(8) NOT NULL,
  `Option6Max18` char(8) NOT NULL,
  `Option6Total18` int(11) NOT NULL,
  `Option6Max19` text NOT NULL,
  `Option6Total19` int(11) NOT NULL,
  `Option7RPP` varchar(255) NOT NULL default '',
  `Option8OPTIONS` varchar(255) NOT NULL default '',
  `Option8USER` varchar(255) NOT NULL default '',
  `Option8ZIP` varchar(255) NOT NULL default '',
  `Option8TermsAgree` varchar(255) NOT NULL default '',
  `RSC1Text` varchar(255) NOT NULL default '',
  `RSC1Price` varchar(255) NOT NULL default '',
  `RSC2Text` varchar(255) NOT NULL default '',
  `RSC2Price` varchar(255) NOT NULL default '',
  `RSC3Text` varchar(255) NOT NULL default '',
  `RSC3Price` varchar(255) NOT NULL default '',
  `OrderTypes` text NOT NULL,
  `UseCaptcha` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`site_settings`
--

/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` (`id`,`Company`,`Address1`,`Address2`,`City`,`State`,`Zip`,`Country`,`Telephone1`,`Telephone2`,`FAX`,`Email`,`URL`,`CopyrightYear`,`ProductsPerPage`,`NumberOfColumns`,`ShowCartOnAdd`,`ShowShippingFields`,`OrderProductsBy`,`ShowImage`,`ShowProductID`,`ShowProductName`,`ShowShortDesc`,`ShowPrice`,`ShowMoreInfo`,`ShowQuantityBox`,`ShowBuyButton`,`ShowAddFreeItemToCart`,`EmailNotifications`,`PaymentPaypalTestmode`,`PaymentPaypalEmail`,`PaymentPaypalOrderButton`,`PaymentAuthnetTestmode`,`PaymentAuthnetLogin`,`PaymentAuthnetKey`,`PaymentAuthnetType`,`PaymentAuthnetOrderButton`,`PaymentCCCardsAccepted`,`PaymentCCValidate`,`PaymentCCOrderButton`,`PaymentManualOrderButton`,`ShippingOptionChoice`,`FreeIfOver`,`Option1RPO`,`Option2RPO`,`Option2CPI`,`Option4Max1`,`Option4Total1`,`Option4Max2`,`Option4Total2`,`Option4Max3`,`Option4Total3`,`Option4Max4`,`Option4Total4`,`Option4Max5`,`Option4Total5`,`Option5Max1`,`Option5Total1`,`Option5Max2`,`Option5Total2`,`Option5Max3`,`Option5Total3`,`Option5Max4`,`Option5Total4`,`Option5Max5`,`Option5Total5`,`Option6Max1`,`Option6Total1`,`Option6Max2`,`Option6Total2`,`Option6Max3`,`Option6Total3`,`Option6Max4`,`Option6Total4`,`Option6Max5`,`Option6Total5`,`Option6Max6`,`Option6Total6`,`Option6Max7`,`Option6Total7`,`Option6Max8`,`Option6Total8`,`Option6Max9`,`Option6Total9`,`Option6Max10`,`Option6Total10`,`Option6Max11`,`Option6Total11`,`Option6Max12`,`Option6Total12`,`Option6Max13`,`Option6Total13`,`Option6Max14`,`Option6Total14`,`Option6Max15`,`Option6Total15`,`Option6Max16`,`Option6Total16`,`Option6Max17`,`Option6Total17`,`Option6Max18`,`Option6Total18`,`Option6Max19`,`Option6Total19`,`Option7RPP`,`Option8OPTIONS`,`Option8USER`,`Option8ZIP`,`Option8TermsAgree`,`RSC1Text`,`RSC1Price`,`RSC2Text`,`RSC2Price`,`RSC3Text`,`RSC3Price`,`OrderTypes`,`UseCaptcha`) VALUES 
 (1,'IBS-Controls Ltd.','Shanghai China','Shanghai China','Shanghai','Yukon Territory','210012','CN','86-021-33770514','','','sales@ibs-controls.com','www.ibs-controls.com','2008','10','3','Yes','Yes','ProductName','Yes','No','Yes','Yes','Yes','Yes','No','No','No','sales@ibs-controls.com','No','paypal@temcocontrols.com','Pay with your Credit Card or PayPal','','','','AUTH_ONLY','','1,2,3,4,5,6','No','','Save Order and Mail Payment','6','','20.00','30','10.00','200','30','400','50','600','80','800','100','3000','120','5','30','8','60','11','100','14','120','100','300.00','0.5','25','1','31.5','1.5','38','2','44.5','2.5','51','3','57.5','3.5','63','4','69.5','4.5','76','5','82.5','5.5',89,'','','','','','','','','','',0,'','',0,'',0,'.99','GND,2DA,2DM,3DS,1DP,1DA,1DM,STD,XPR,XDM,XPD','YOURUSERNAME','97068','Yes','','','','','','','New\r\nIn Progress\r\nOn Hold\r\nCompleted\r\nCancelled Order',0);
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`taxes_countries`
--

DROP TABLE IF EXISTS `taxes_countries`;
CREATE TABLE `taxes_countries` (
  `ID` int(11) NOT NULL auto_increment,
  `Country` varchar(255) NOT NULL default '',
  `Code` varchar(2) NOT NULL default '',
  `TaxFlat` float(5,2) NOT NULL default '0.00',
  `TaxPercent` float(5,2) NOT NULL default '0.00',
  PRIMARY KEY  (`ID`),
  KEY `Country` (`Country`),
  KEY `Code` (`Code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`taxes_countries`
--

/*!40000 ALTER TABLE `taxes_countries` DISABLE KEYS */;
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (1,'Afghanistan','AF',0.00,0.00),
 (2,'Albania','AL',0.00,0.00),
 (3,'Algeria','DZ',0.00,0.00),
 (4,'American Samoa','AS',0.00,0.00),
 (5,'Andorra','AD',0.00,0.00),
 (6,'Angola','AO',0.00,0.00),
 (7,'Anguilla','AI',0.00,0.00),
 (8,'Antarctica','AQ',0.00,0.00),
 (9,'Antigua And Barbuda','AG',0.00,0.00),
 (10,'Argentina','AR',0.00,0.00),
 (11,'Armenia','AM',0.00,0.00),
 (12,'Aruba','AW',0.00,0.00),
 (13,'Australia','AU',0.00,0.00),
 (14,'Austria','AT',0.00,0.00),
 (15,'Azerbaijan','AZ',0.00,0.00),
 (16,'Bahamas','BS',0.00,0.00),
 (17,'Bahrain','BH',0.00,0.00),
 (18,'Bangladesh','BD',0.00,0.00),
 (19,'Barbados','BB',0.00,0.00),
 (20,'Belarus','BY',0.00,0.00),
 (21,'Belgium','BE',0.00,0.00),
 (22,'Belize','BZ',0.00,0.00),
 (23,'Benin','BJ',0.00,0.00),
 (24,'Bermuda','BM',0.00,0.00),
 (25,'Bhutan','BT',0.00,0.00),
 (26,'Bolivia','BO',0.00,0.00),
 (27,'Bosnia And Herzegovina','BA',0.00,0.00),
 (28,'Botswana','BW',0.00,0.00),
 (29,'Bouvet Island','BV',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (30,'Brazil','BR',0.00,0.00),
 (31,'British Indian Ocean Territory','IO',0.00,0.00),
 (32,'Brunei Darussalam','BN',0.00,0.00),
 (33,'Bulgaria','BG',0.00,0.00),
 (34,'Burkina Faso','BF',0.00,0.00),
 (35,'Burundi','BI',0.00,0.00),
 (36,'Cambodia','KH',0.00,0.00),
 (37,'Cameroon','CM',0.00,0.00),
 (38,'*Canada','CA',0.00,0.00),
 (39,'Cape Verde','CV',0.00,0.00),
 (40,'Cayman Islands','KY',0.00,0.00),
 (41,'Central African Republic','CF',0.00,0.00),
 (42,'Chad','TD',0.00,0.00),
 (43,'Chile','CL',0.00,0.00),
 (44,'China','CN',0.00,0.00),
 (45,'Christmas Island','CX',0.00,0.00),
 (46,'Cocos (Keeling) Islands','CC',0.00,0.00),
 (47,'Colombia','CO',0.00,0.00),
 (48,'Comoros','KM',0.00,0.00),
 (49,'Congo','CG',0.00,0.00),
 (50,'Congo, The Democratic Republic Of The','CD',0.00,0.00),
 (51,'Cook Islands','CK',0.00,0.00),
 (52,'Costa Rica','CR',0.00,0.00),
 (53,'Cote D\'ivoire','CI',0.00,0.00),
 (54,'Croatia','HR',0.00,0.00),
 (55,'Cuba','CU',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (56,'Cyprus','CY',0.00,0.00),
 (57,'Czech Republic','CZ',0.00,0.00),
 (58,'Denmark','DK',0.00,0.00),
 (59,'Djibouti','DJ',0.00,0.00),
 (60,'Dominica','DM',0.00,0.00),
 (61,'Dominican Republic','DO',0.00,0.00),
 (62,'East Timor','TP',0.00,0.00),
 (63,'Ecuador','EC',0.00,0.00),
 (64,'Egypt','EG',0.00,0.00),
 (65,'El Salvador','SV',0.00,0.00),
 (66,'Equatorial Guinea','GQ',0.00,0.00),
 (67,'Eritrea','ER',0.00,0.00),
 (68,'Estonia','EE',0.00,0.00),
 (69,'Ethiopia','ET',0.00,0.00),
 (70,'Falkland Islands (Malvinas)','FK',0.00,0.00),
 (71,'Faroe Islands','FO',0.00,0.00),
 (72,'Fiji','FJ',0.00,0.00),
 (73,'Finland','FI',0.00,0.00),
 (74,'France','FR',0.00,0.00),
 (75,'French Guiana','GF',0.00,0.00),
 (76,'French Polynesia','PF',0.00,0.00),
 (77,'French Southern Territories','TF',0.00,0.00),
 (78,'Gabon','GA',0.00,0.00),
 (79,'Gambia','GM',0.00,0.00),
 (80,'Georgia','GE',0.00,0.00),
 (81,'Germany','DE',0.00,0.00),
 (82,'Ghana','GH',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (83,'Gibraltar','GI',0.00,0.00),
 (84,'Greece','GR',0.00,0.00),
 (85,'Greenland','GL',0.00,0.00),
 (86,'Grenada','GD',0.00,0.00),
 (87,'Guadeloupe','GP',0.00,0.00),
 (88,'Guam','GU',0.00,0.00),
 (89,'Guatemala','GT',0.00,0.00),
 (90,'Guinea','GN',0.00,0.00),
 (91,'Guinea-bissau','GW',0.00,0.00),
 (92,'Guyana','GY',0.00,0.00),
 (93,'Haiti','HT',0.00,0.00),
 (94,'Heard Island And Mcdonald Islands','HM',0.00,0.00),
 (95,'Holy See (Vatican City State)','VA',0.00,0.00),
 (96,'Honduras','HN',0.00,0.00),
 (97,'Hong Kong','HK',0.00,0.00),
 (98,'Hungary','HU',0.00,0.00),
 (99,'Iceland','IS',0.00,0.00),
 (100,'India','IN',0.00,0.00),
 (101,'Indonesia','ID',0.00,0.00),
 (102,'Iran, Islamic Republic Of','IR',0.00,0.00),
 (103,'Iraq','IQ',0.00,0.00),
 (104,'Ireland','IE',0.00,0.00),
 (105,'Israel','IL',0.00,0.00),
 (106,'Italy','IT',0.00,0.00),
 (107,'Jamaica','JM',0.00,0.00),
 (108,'Japan','JP',0.00,0.00),
 (109,'Jordan','JO',0.00,0.00),
 (110,'Kazakstan','KZ',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (111,'Kenya','KE',0.00,0.00),
 (112,'Kiribati','KI',0.00,0.00),
 (113,'Korea, Democratic People\'s Republic Of','KP',0.00,0.00),
 (114,'Korea, Republic Of','KR',0.00,0.00),
 (115,'Kuwait','KW',0.00,0.00),
 (116,'Kyrgyzstan','KG',0.00,0.00),
 (117,'Lao People\'s Democratic Republic','LA',0.00,0.00),
 (118,'Latvia','LV',0.00,0.00),
 (119,'Lebanon','LB',0.00,0.00),
 (120,'Lesotho','LS',0.00,0.00),
 (121,'Liberia','LR',0.00,0.00),
 (122,'Libyan Arab Jamahiriya','LY',0.00,0.00),
 (123,'Liechtenstein','LI',0.00,0.00),
 (124,'Lithuania','LT',0.00,0.00),
 (125,'Luxembourg','LU',0.00,0.00),
 (126,'Macau','MO',0.00,0.00),
 (127,'Macedonia, The Former Yugoslav Republic Of','MK',0.00,0.00),
 (128,'Madagascar','MG',0.00,0.00),
 (129,'Malawi','MW',0.00,0.00),
 (130,'Malaysia','MY',0.00,0.00),
 (131,'Maldives','MV',0.00,0.00),
 (132,'Mali','ML',0.00,0.00),
 (133,'Malta','MT',0.00,0.00),
 (134,'Marshall Islands','MH',0.00,0.00),
 (135,'Martinique','MQ',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (136,'Mauritania','MR',0.00,0.00),
 (137,'Mauritius','MU',0.00,0.00),
 (138,'Mayotte','YT',0.00,0.00),
 (139,'Mexico','MX',0.00,0.00),
 (140,'Micronesia, Federated States Of','FM',0.00,0.00),
 (141,'Moldova, Republic Of','MD',0.00,0.00),
 (142,'Monaco','MC',0.00,0.00),
 (143,'Mongolia','MN',0.00,0.00),
 (144,'Montserrat','MS',0.00,0.00),
 (145,'Morocco','MA',0.00,0.00),
 (146,'Mozambique','MZ',0.00,0.00),
 (147,'Myanmar','MM',0.00,0.00),
 (148,'Namibia','NA',0.00,0.00),
 (149,'Nauru','NR',0.00,0.00),
 (150,'Nepal','NP',0.00,0.00),
 (151,'Netherlands','NL',0.00,0.00),
 (152,'Netherlands Antilles','AN',0.00,0.00),
 (153,'New Caledonia','NC',0.00,0.00),
 (154,'New Zealand','NZ',0.00,0.00),
 (155,'Nicaragua','NI',0.00,0.00),
 (156,'Niger','NE',0.00,0.00),
 (157,'Nigeria','NG',0.00,0.00),
 (158,'Niue','NU',0.00,0.00),
 (159,'Norfolk Island','NF',0.00,0.00),
 (160,'Northern Mariana Islands','MP',0.00,0.00),
 (161,'Norway','NO',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (162,'Oman','OM',0.00,0.00),
 (163,'Pakistan','PK',0.00,0.00),
 (164,'Palau','PW',0.00,0.00),
 (165,'Palestinian Territory, Occupied','PS',0.00,0.00),
 (166,'Panama','PA',0.00,0.00),
 (167,'Papua New Guinea','PG',0.00,0.00),
 (168,'Paraguay','PY',0.00,0.00),
 (169,'Peru','PE',0.00,0.00),
 (170,'Philippines','PH',0.00,0.00),
 (171,'Pitcairn','PN',0.00,0.00),
 (172,'Poland','PL',0.00,0.00),
 (173,'Portugal','PT',0.00,0.00),
 (174,'Puerto Rico','PR',0.00,0.00),
 (175,'Qatar','QA',0.00,0.00),
 (176,'Reunion','RE',0.00,0.00),
 (177,'Romania','RO',0.00,0.00),
 (178,'Russian Federation','RU',0.00,0.00),
 (179,'Rwanda','RW',0.00,0.00),
 (180,'Saint Helena','SH',0.00,0.00),
 (181,'Saint Kitts And Nevis','KN',0.00,0.00),
 (182,'Saint Lucia','LC',0.00,0.00),
 (183,'Saint Pierre And Miquelon','PM',0.00,0.00),
 (184,'Saint Vincent And The Grenadines','VC',0.00,0.00),
 (185,'Samoa','WS',0.00,0.00),
 (186,'San Marino','SM',0.00,0.00),
 (187,'Sao Tome And Principe','ST',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (188,'Saudi Arabia','SA',0.00,0.00),
 (189,'Senegal','SN',0.00,0.00),
 (190,'Seychelles','SC',0.00,0.00),
 (191,'Sierra Leone','SL',0.00,0.00),
 (192,'Singapore','SG',0.00,0.00),
 (193,'Slovakia','SK',0.00,0.00),
 (194,'Slovenia','SI',0.00,0.00),
 (195,'Solomon Islands','SB',0.00,0.00),
 (196,'Somalia','SO',0.00,0.00),
 (197,'South Africa','ZA',0.00,0.00),
 (198,'South Georgia And The South Sandwich Islands','GS',0.00,0.00),
 (199,'Spain','ES',0.00,0.00),
 (200,'Sri Lanka','LK',0.00,0.00),
 (201,'Sudan','SD',0.00,0.00),
 (202,'Suriname','SR',0.00,0.00),
 (203,'Svalbard And Jan Mayen','SJ',0.00,0.00),
 (204,'Swaziland','SZ',0.00,0.00),
 (205,'Sweden','SE',0.00,0.00),
 (206,'Switzerland','CH',0.00,0.00),
 (207,'Syrian Arab Republic','SY',0.00,0.00),
 (208,'Taiwan, Province Of China','TW',0.00,0.00),
 (209,'Tajikistan','TJ',0.00,0.00),
 (210,'Tanzania, United Republic Of','TZ',0.00,0.00),
 (211,'Thailand','TH',0.00,0.00),
 (212,'Togo','TG',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (213,'Tokelau','TK',0.00,0.00),
 (214,'Tonga','TO',0.00,0.00),
 (215,'Trinidad And Tobago','TT',0.00,0.00),
 (216,'Tunisia','TN',0.00,0.00),
 (217,'Turkey','TR',0.00,0.00),
 (218,'Turkmenistan','TM',0.00,0.00),
 (219,'Turks And Caicos Islands','TC',0.00,0.00),
 (220,'Tuvalu','TV',0.00,0.00),
 (221,'Uganda','UG',0.00,0.00),
 (222,'Ukraine','UA',0.00,0.00),
 (223,'United Arab Emirates','AE',0.00,0.00),
 (224,'United Kingdom','GB',0.00,0.00),
 (225,'*United States','US',0.00,0.00),
 (226,'United States Minor Outlying Islands','UM',0.00,0.00),
 (227,'Uruguay','UY',0.00,0.00),
 (228,'Uzbekistan','UZ',0.00,0.00),
 (229,'Vanuatu','VU',0.00,0.00),
 (230,'Venezuela','VE',0.00,0.00),
 (231,'Viet Nam','VN',0.00,0.00),
 (232,'Virgin Islands, British','VG',0.00,0.00),
 (233,'Virgin Islands, U.S.','VI',0.00,0.00),
 (234,'Wallis And Futuna','WF',0.00,0.00),
 (235,'Western Sahara','EH',0.00,0.00),
 (236,'Yemen','YE',0.00,0.00),
 (237,'Yugoslavia','YU',0.00,0.00);
INSERT INTO `taxes_countries` (`ID`,`Country`,`Code`,`TaxFlat`,`TaxPercent`) VALUES 
 (238,'Zambia','ZM',0.00,0.00),
 (239,'Zimbabwe','ZW',0.00,0.00);
/*!40000 ALTER TABLE `taxes_countries` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`taxes_states`
--

DROP TABLE IF EXISTS `taxes_states`;
CREATE TABLE `taxes_states` (
  `ID` int(11) NOT NULL auto_increment,
  `Code` varchar(2) NOT NULL default '',
  `State` varchar(255) NOT NULL default '',
  `TaxFlat` float(5,2) NOT NULL default '0.00',
  `TaxPercent` float(5,2) NOT NULL default '0.00',
  PRIMARY KEY  (`ID`),
  KEY `State` (`Code`),
  KEY `Code` (`State`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`taxes_states`
--

/*!40000 ALTER TABLE `taxes_states` DISABLE KEYS */;
INSERT INTO `taxes_states` (`ID`,`Code`,`State`,`TaxFlat`,`TaxPercent`) VALUES 
 (425,'AL','Alabama',0.00,6.50),
 (426,'AK','Alaska',0.00,10.00),
 (427,'AS','American Samoa',0.00,10.00),
 (428,'AZ','Arizona',0.00,10.00),
 (429,'AR','Arkansas',0.00,10.00),
 (430,'CA','California',0.00,6.50),
 (431,'CO','Colorado',0.00,10.00),
 (432,'CT','Connecticut',0.00,10.00),
 (433,'DE','Delaware',0.00,10.00),
 (434,'DC','District of Columbia',0.00,10.00),
 (435,'FM','Federated States of Micronesia',0.00,10.00),
 (436,'FL','Florida',0.00,10.00),
 (437,'GA','Georgia',0.00,10.00),
 (438,'GU','Guam',0.00,10.00),
 (439,'HI','Hawaii',0.00,10.00),
 (440,'ID','Idaho',0.00,10.00),
 (441,'IL','Illinois',0.00,10.00),
 (442,'IN','Indiana',0.00,10.00),
 (443,'IA','Iowa',0.00,10.00),
 (444,'KS','Kansas',0.00,10.00),
 (445,'KY','Kentucky',0.00,10.00),
 (446,'LA','Louisiana',0.00,10.00),
 (447,'ME','Maine',0.00,10.00),
 (448,'MH','Marshall Islands',0.00,10.00),
 (449,'MD','Maryland',0.00,10.00),
 (450,'MA','Massachusetts',0.00,10.00),
 (451,'MI','Michigan',0.00,10.00);
INSERT INTO `taxes_states` (`ID`,`Code`,`State`,`TaxFlat`,`TaxPercent`) VALUES 
 (452,'MN','Minnesota',0.00,10.00),
 (453,'MS','Mississippi',0.00,10.00),
 (454,'MO','Missouri',0.00,10.00),
 (455,'MT','Montana',0.00,10.00),
 (456,'NE','Nebraska',0.00,10.00),
 (457,'NV','Nevada',0.00,10.00),
 (458,'NH','New Hampshire',0.00,10.00),
 (459,'NJ','New Jersey',0.00,10.00),
 (460,'NM','New Mexico',0.00,10.00),
 (461,'NY','New York',0.00,10.00),
 (462,'NC','North Carolina',0.00,10.00),
 (463,'ND','North Dakota',0.00,10.00),
 (464,'MP','Northern Mariana Islands',0.00,10.00),
 (465,'OH','Ohio',0.00,10.00),
 (466,'OK','Oklahoma',0.00,10.00),
 (467,'OR','Oregon',0.00,10.00),
 (468,'PA','Pennsylvania',0.00,10.00),
 (469,'PR','Puerto Rico',0.00,10.00),
 (470,'RI','Rhode Island',0.00,10.00),
 (471,'SC','South Carolina',0.00,10.00),
 (472,'SD','South Dakota',0.00,10.00),
 (473,'TN','Tennessee',0.00,10.00),
 (474,'TX','Texas',0.00,10.00),
 (475,'UT','Utah',0.00,10.00),
 (476,'VT','Vermont',0.00,10.00),
 (477,'VI','Virgin Islands, U.S.',0.00,10.00),
 (478,'VA','Virginia',0.00,10.00);
INSERT INTO `taxes_states` (`ID`,`Code`,`State`,`TaxFlat`,`TaxPercent`) VALUES 
 (479,'WA','Washington',0.00,10.00),
 (480,'WV','West Virginia',0.00,10.00),
 (481,'WI','Wisconsin',0.00,10.00),
 (482,'WY','Wyoming',0.00,10.00),
 (483,'Ot','Other/Not-Listed',0.00,0.00),
 (506,'NF','Newfoundland and Labrador',0.00,0.00),
 (505,'NB','New Brunswick',0.00,0.00),
 (504,'MB','Manitoba',0.00,0.00),
 (502,'AB','Alberta',10.00,10.00),
 (503,'BC','British Columbia',10.00,10.00),
 (507,'NT','Northwest Territories',0.00,0.00),
 (508,'NS','Nova Scotia',0.00,0.00),
 (509,'NU','Nunavut',0.00,0.00),
 (510,'ON','Ontario',0.00,0.00),
 (511,'PE','Prince Edward Island',0.00,0.00),
 (512,'QC','Quebec',0.00,0.00),
 (513,'SK','Saskatchewan',0.00,0.00),
 (514,'YT','Yukon Territory',0.00,0.00);
/*!40000 ALTER TABLE `taxes_states` ENABLE KEYS */;


--
-- Table structure for table `andrew`.`website_text`
--

DROP TABLE IF EXISTS `website_text`;
CREATE TABLE `website_text` (
  `PageID` int(11) NOT NULL auto_increment,
  `PageName` varchar(255) NOT NULL default '',
  `PageTitle` varchar(255) NOT NULL default '',
  `PageText` text NOT NULL,
  `PageFormat` char(1) NOT NULL default 't',
  PRIMARY KEY  (`PageID`),
  UNIQUE KEY `PageName` (`PageName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `andrew`.`website_text`
--

/*!40000 ALTER TABLE `website_text` DISABLE KEYS */;
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (1,'search.php','Your Search Results  HVAC Products ','The results of your search are listed below. Click on any product for more information.','t'),
 (2,'specials.php','Sensor Equipment. USA  Online Specials','Our current online specials are listed below. Click on any product to find out more information and to order.\n','t'),
 (3,'shipping.php','Our Shipping and Handling Policies','You can enter a description of your shipping rates and terms, such as estimated time to delivery, etc.\r\n\r\nThis will be shown in a popup window when the customer clicks on \"View Shipping Rates\" when viewing their shopping cart or checking out.                                 ','t'),
 (4,'contact.php','Contact Us International HVAC Products Supplier','Use the contact form on this page to contact us with your comments, questions, or suggestions.\r\n\r\nYou can also reach us by telephone, FAX, or email.','t'),
 (5,'cart.php','Your Shopping Cart','<center>Your Shopping Cart Contains the Following Items:</center>','t'),
 (6,'index.php','Welcome to IBS-Controls ltd.,CN','<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\" style=\"padding:0px;margin:0px;\">\r\n	<tr><td valign=\"top\"><h3>WELCOME TO SENSOR EQUIPMENT LTD.,<br></h3>\r\n<img border=\"0\" src=\"/images/building.jpg\" width=\"122\" height=\"172\" title=\"Sensor Equipment ,Current Sensor supplier\" title=\" IBS-Controls ltd.,CN ,Current Sensor supplier\"\" style=\"float:left;border:1px solid #999;padding:5px;margin:5px;\">\r\n\r\nWith more then 20 years of experience in hvac products supply in Wa US, SE(Sensor Equipment) offers s an immense range of hvac products (temperature sensor , current sensor , pressure sensor  zone valves , current sensor ,thermostat,plastic components etc.) we supply the relation products at the low price on the global.\r\nAfter 20 years of development. SE(Sensor Equipment) has won the truest from customer for its professional technology and first-class service from design to production .All its products are exported to Asia, Europe , North America, South America. The middle East and Africa. The annual sales volume has increased year by year.\r\n</p>\r\n<br />\r\n<br />\r\nOur company believes that the high technology and best service are the most important factors in running a international business trade. With the goal of  “high quality products, prompt delivery,” SE pledges to satisfy customers various requirement\r\nOEM orders are accepted.Please feel free to contact us at any time. \r\n</td>\r\n\r\n	</tr>\r\n</table>\r\n<hr style=\"size:2xp;color:#E8E8E8;\">\r\n<font style=\"family:arial;font-size:1.6em;color:#8e8e8e;font-weight:800;\">Featured Products:</font>\r\n<hr style=\"size:2xp;color:#E8E8E8;\">\r\n\r\n<div align=\"center\">','h');
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (25,'email/reset_password.php','Password Recovery','Hello $var->fullname,\n\nYour password has been reset, your new login \ninformation is:\n\n     username: $var->username\n     password: $var->newpassword\n\nIt is highly recommended that you log in and change your \npassword as soon as possible.\n\nThank you for using our website!\n\nSincerely, \n\nSupport \n$var->support\n$var->URL','t'),
 (7,'checkout/index.php','IBS-Controls ltd Check Out','Check out using the form below.  \r\n\r\n','t'),
 (8,'checkout/payment.php','Purchase Confirmation','Please confirm your order and billing information below. When finshed, click \"<b>Proceed with Purchase</b>\" at the bottom of the screen to be taken to PayPal\'s Website to complete the ordering process.  You can use your Visa, MasterCard, Discover, American Express or your checking account to pay for your order through PayPal.  You can press the \"<b>Make Changes</b>\" button to go back to the previous page and make any desired changes to your order.','t'),
 (9,'checkout/payment_manual.php','Your Order has been saved','Thanks for your order!  Please use the link below to print a receipt.  \r\n\r\nYour order will not be processed until we have received your check or money order in the mail.  \r\n\r\nPlease note that checks may add up to 7 business days to the time of delivery.','t');
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (20,'admin/index.php','IBS-Controls ltd Administrator','<div align=\"center\">\r\n	<big><big><b>Welcome to IBS-Controls ltd Administrative Area!</b></big></big><br>\r\n	<small><b>Powered by <a href=\"http://www.temcocontrols.com\">T</a><a href=\"http://www.temcocontrols.com\" target=\"_blank\">emcocontrols \r\n	Ltd.US</a></b></small></div>\r\n<p><big>Use the menu choices to your left to manage your products, categories, orders and customers, edit the text on the various pages of your website, update your company preferences, set shipping &amp; tax options, or to access any other custom modules you may have added to your website.</big></p>\r\n<p><font size=\"4\">This </font><big>\r\nControl Panel  is where you will be able to view your website statistics, edit your website keywords and description, and submit any necessary maintenance requests, among many other options.</big></p>','h'),
 (12,'checkout/payment_authnet_success.php','Your Credit Card Order Has Been Processed','Thanks for your order!  Please use the link below to print a receipt.  ','t'),
 (13,'checkout/payment_authnet_failure.php','Errors Processing Your Credit Card','We\'re sorry, but your credit card information was not accepted.  Please re-check the credit card type, number, and expiration date you entered.','t');
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (14,'checkout/payment_paypal_complete.php','Your PayPal Order Has Been Processed','Thanks for your order!  Please use the link below to print a receipt.  ','t'),
 (15,'email/order_authnet.php','Thanks for Your Order from Sensor Equipment Ltd.','Hello,\n\nYour order has been entered, we will contact you shortly regarding fulfillment. \n\nThanks for your business,\n\nSensor Equipemnt Ltd. USA\n4416 South Parkside Court\nSpokane, Washington 99223\nUS\n\nTel:(214) 800-4562\nFax:(206) 888-8888\nsales@sensorequipment.com','t'),
 (16,'email/order_manual.php','Thanks for Your Order from Sensor Equipment Ltd. USA','Thanks for your order.  We have saved your order information, but we will not be able to begin any final work or ship your order until your payment arrives.  Please make payment using any of the following methods:\n\nMail in a printed copy of your invoice to:\nSensor Equipment Ltd. USA\n4416 South Parkside Court\nSpokane, Washington 99223\nUS\n\nTel:(452) 542-6564\nFax:(656) 456-6588\n\nYou can also call us directly and pay for your order using your Visa, Mastercard, or Discover.  Please be sure to have your order number available when you call.\n\n\nThanks Again,\n\nTemcocontrols Ltd. USA\nsales@temcocontrols.com','t'),
 (17,'checkout/payment_cc_failure.php','Errors Processing Your Credit Card','We\'re sorry, but your credit card information was not accepted.  Please re-check the credit card type, number, and expiration date you entered.','t');
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (18,'checkout/payment_cc_success.php','Your Credit Card Order Has Been Processed','Thanks for your order!  Please use the link below to print a receipt.  ','t'),
 (19,'email/order_cc.php','Thanks for Your Order from COMPANY','Thanks for your order.  We have saved your order information, but we will not be able to begin any final work or ship your order until your payment arrives.  Please make payment using any of the following methods:\r\n\r\nMail in a printed copy of your invoice to:\r\nCOMPANY NAME\r\nAddress Line 1\r\nAddress Line 2\r\nCity, State, Zip\r\n\r\nYou can also call us directly and pay for your order using your Visa, Mastercard, or Discover.  Please be sure to have your order number available when you call.\r\n\r\nOur toll-free payment line is:\r\n(800)123-4567\r\n(8-5 EST, M-F)\r\n\r\nThanks Again,\r\n\r\nCOMPANY\r\nEMAIL','t'),
 (21,'checkout/popup_cvv.php','CVV2 FAQ','<div align=\"left\">\r\n<ol>\r\n	<li><b>What is CVV2?</b><br>\r\n	CVV2 is an authentication mechanism created by card companies to help reduce fraud with online transactions. It requires the card holder to have the card physically in hand in order to enter three or four digits found printed on the card itself.<br>\r\n	<br></li>\r\n	<li>\r\n		<b>Where do I find the numbers?</b><br>\r\n		On Visa, MasterCard and Discover credit cards, the number is located on the back of the card in the signature area:<br>\r\n		<img hspace=\"20\" vspace=\"10\" src=\"/common/cart4/images/cvv2_visa.gif\" width=\"215\" height=\"103\"><br>\r\n		On American Express cards, the number is located on the front of the card, just above the card number, usually on the right:<br>\r\n		<img alt=\"AmEx CVV2\" hspace=\"20\" src=\"/common/cart4/images/cvv2_amex.gif\" width=\"248\" height=\"129\"><br>\r\n		Diner\'s Club and some older credit cards may not have the CVV2 codes.<br>\r\n		<br>\r\n	</li>\r\n	<li><b>My card doesn\'t have CVV2. What do I do?</b><br>\r\n	If your card does not have CVV2 numbers, leave this entry blank. However, this information helps us process your Credit Card order faster.<br>\r\n	<br></li>\r\n	<li><b>Why are you requiring this?</b><br>\r\n	Credit card fraud is on the rise on the Internet. Requiring CVV2 codes is just one more action we take to ensure that our customers\' security is protected.</li>\r\n</ol>\r\n</div>','h'),
 (31,'company.php','Shop by Company','All of our companies that we carry products for are shown below.','t');
INSERT INTO `website_text` (`PageID`,`PageName`,`PageTitle`,`PageText`,`PageFormat`) VALUES 
 (32,'links.php','HVAC Products Links','','t');
/*!40000 ALTER TABLE `website_text` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
