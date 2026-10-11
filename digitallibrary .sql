-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 21, 2026 at 01:03 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `digitallibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `adminid` int NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` char(10) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`adminid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminid`, `username`, `password`, `email`, `phone`, `status`) VALUES
(1, 'admin', 'admin123', 'admin@digitallibrary.com', '9876543210', 1);

-- --------------------------------------------------------

--
-- Table structure for table `authorregistration`
--

DROP TABLE IF EXISTS `authorregistration`;
CREATE TABLE IF NOT EXISTS `authorregistration` (
  `authid` int NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(25) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` char(10) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`authid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `authorregistration`
--

INSERT INTO `authorregistration` (`authid`, `username`, `password`, `email`, `phone`, `status`) VALUES
(2, 'paaru', 'paaru123', 'paaru@example.com', '1234567890', 2),
(3, 'Aswin', '123456', 'author@example.com', '0123456789', 2);

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `bid` int NOT NULL AUTO_INCREMENT,
  `bname` varchar(50) NOT NULL,
  `AuthorName` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `cid` int NOT NULL,
  `CoverImage` varchar(255) NOT NULL,
  `story` text NOT NULL,
  `is_paid` varchar(10) NOT NULL,
  `price` int NOT NULL,
  `AddedDate` date NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`bid`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`bid`, `bname`, `AuthorName`, `description`, `cid`, `CoverImage`, `story`, `is_paid`, `price`, `AddedDate`, `status`) VALUES
(4, 'Your Name', 'Makoto Shinkai', 'The story centers on two teenagers: Mitsuha Miyamizu, a girl in the rural town of Itomori who dreams of life in Tokyo, and Taki Tachibana, a high school boy living in Tokyo.', 11, '2d50762314c0659abe826959e6927dee_d0ecb49a4457718bf5dd.jpg', '0fcb2eefd5a905bb013b9b4aa2c78773_2d426a58868b3.pdf', 'paid', 300, '2026-04-25', 1),
(9, 'Grave of the Fireflies', 'paaru', 'tragedy and brother bond ', 17, '8f1c26fc8b8b04bc827578168c008d45_a443b37bc8.jpg', '2e8d88163f4846cdbfe22044abd2547e_8cecf9a746d5a514.pdf', 'paid', 100, '2026-05-20', 1),
(10, 'Silent Voice', 'yoshifoki loima', 'The story follows Shoya Ishida, a high school student seeking to make amends with Shoko Nishimiya, a deaf girl he relentlessly bullied during their elementary school years', 17, '167e0e78ae8661f3988e24b4fea3f473_ff169f680fb935654.jpg', '0a456e0b78670042458a858d1aed0f47_b04d7615673a5b2e3295.pdf', 'free', 0, '2026-05-05', 1),
(11, 'Lost in The Never Woods', 'Aswin', 'It follows a girl named Wendy who returns to a mysterious forest where 	children have been disappearing. \r\n', 13, '93791acf83ff38feae81250960fb195f_afcd3c26d6482.png', 'a91db20c03e05cb1eb897c3008198bbc_2953809c0b02bef.pdf', 'paid', 299, '2026-06-21', 1),
(12, 'Beauty and the  Beast', 'Aswin', 'This story is about a kind girl named Belle who becomes a prisoner in a magical castle. ', 16, 'bbe723ba817dfe374e8f648b8ada4246_907e145a92b69811ea.jpg', '32b960d35e68d965fb4de3eb8f7b3d2b_7a813c0b4880.pdf', 'paid', 299, '2026-06-21', 1),
(13, 'Death Note', 'Aswin', 'This manga follows a student named Light Yagami who finds a mysterious notebook that can kill anyone whose name is written in it. He tries to create a perfect world without crime, but a genius detective known as L sets out to stop him.', 10, '4f3558cc2e3aa31cc6d216c402b86264_00ef9e7261c98c27.jpg', '72e8365652e9ba3f61851744f03efe08_91da468c94cafb09.pdf', 'paid', 499, '2026-06-21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `cid` int NOT NULL AUTO_INCREMENT,
  `cname` varchar(30) NOT NULL,
  `cdesc` text NOT NULL,
  `cimage` varchar(255) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cid`, `cname`, `cdesc`, `cimage`, `status`) VALUES
(17, 'Drama', 'Drama is a versatile genre that focuses on emotional stakes', '6c3a523fdfe37a9f6b4872131945634d_9a06f0e3e1c5c1.png', 1),
(14, 'Action', 'Focuses on physical movement, journeys, and combat.', 'ccff781ca9462a0ede1eeb737cbbc0c8_9844542bee3fcb.png', 1),
(15, 'SciFi', 'Future technology, space exploration, and time travel', '09b98475be2a6638a12fcc7e7be79db5_0378d97c8689da17.png', 1),
(16, 'Fantasy', 'Magic, supernatural elements, and world-building', '41fdbc44d9232c8649590691877a1ceb_cb80a0cb219ad.png', 1),
(13, 'Mystery', 'Solving a crime or a puzzle.', '2ce1376f7c0d94b60a6e1e613355e620_b392f123eb.png', 1),
(11, 'Romance', 'Focuses on a love story. A trending 2026 sub-genre is Romantasy.', 'd4168470eb2022d34e35143cdd0e2270_505579ffc29.png', 1),
(12, 'Thriller', 'High-stakes, fast-paced, and full of suspense or danger.', '9016658f4c2d1ea98e83c9dccb3a6012_1c7cc6f3250468504.png', 1),
(10, 'Manga', 'A massive world of storytelling that originated in Japan and has grown into a global phenomenon.', 'f515d77040111a85f4ce42303e3d681c_4286b08534dc0bf0.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `complaintid` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `bid` int NOT NULL DEFAULT '0',
  `complaint_type` varchar(30) NOT NULL DEFAULT 'Other',
  `subject` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `admin_reply` text,
  `filed_date` datetime NOT NULL,
  `resolved_date` datetime DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`complaintid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

DROP TABLE IF EXISTS `downloads`;
CREATE TABLE IF NOT EXISTS `downloads` (
  `downloadid` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `bid` int NOT NULL,
  `download_date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`downloadid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `downloads`
--

INSERT INTO `downloads` (`downloadid`, `userId`, `bid`, `download_date`, `status`) VALUES
(1, 3, 10, '2026-05-05 12:07:40', 1),
(2, 4, 10, '2026-06-21 14:30:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedid` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `sender_type` varchar(10) NOT NULL DEFAULT 'reader',
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `feedback_date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`feedid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedid`, `sender_id`, `sender_type`, `subject`, `message`, `feedback_date`, `status`) VALUES
(1, 2, 'author', 'user experience', 'the ux and ui is nice ❤️', '2026-05-05 10:03:18', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notifid` int NOT NULL AUTO_INCREMENT,
  `receiver_id` int NOT NULL DEFAULT '0',
  `receiver_type` varchar(10) NOT NULL DEFAULT 'reader',
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `sent_date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`notifid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payid` int NOT NULL AUTO_INCREMENT,
  `purchaseid` int NOT NULL,
  `userId` int NOT NULL,
  `bid` int NOT NULL,
  `amount` int NOT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'UPI',
  `transaction_id` varchar(100) NOT NULL DEFAULT '',
  `payment_date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`payid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payid`, `purchaseid`, `userId`, `bid`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`) VALUES
(1, 1, 3, 4, 300, 'UPI', 'TXN1777961446269', '2026-05-05 11:40:46', 1),
(2, 2, 4, 4, 300, 'UPI', 'TXN1782030586932', '2026-06-21 13:59:46', 1),
(3, 3, 4, 9, 100, 'NetBanking', 'TXN1782032382385', '2026-06-21 14:29:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `purchaseid` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `bid` int NOT NULL,
  `amount` int NOT NULL,
  `purchase_date` datetime NOT NULL,
  `payment_status` varchar(15) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) NOT NULL DEFAULT '',
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`purchaseid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchaseid`, `userId`, `bid`, `amount`, `purchase_date`, `payment_status`, `transaction_id`, `status`) VALUES
(1, 3, 4, 300, '2026-05-05 11:40:46', 'completed', 'TXN1777961446269', 1),
(2, 4, 4, 300, '2026-06-21 13:59:46', 'completed', 'TXN1782030586932', 1),
(3, 4, 9, 100, '2026-06-21 14:29:42', 'completed', 'TXN1782032382385', 1);

-- --------------------------------------------------------

--
-- Table structure for table `readerregistration`
--

DROP TABLE IF EXISTS `readerregistration`;
CREATE TABLE IF NOT EXISTS `readerregistration` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(25) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` char(10) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `readerregistration`
--

INSERT INTO `readerregistration` (`userId`, `username`, `password`, `email`, `phone`, `status`) VALUES
(4, 'joyboy', '123456', 'reader@example.com', '0123456789', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `reviewid` int NOT NULL AUTO_INCREMENT,
  `bid` int NOT NULL,
  `userId` int NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `review_text` text NOT NULL,
  `review_date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`reviewid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`reviewid`, `bid`, `userId`, `rating`, `review_text`, `review_date`, `status`) VALUES
(1, 4, 3, 4, 'very nice story', '2026-05-05 11:51:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `wid` int NOT NULL AUTO_INCREMENT,
  `bid` int NOT NULL,
  `userId` int NOT NULL,
  `AddedDate` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`wid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wid`, `bid`, `userId`, `AddedDate`, `status`) VALUES
(1, 4, 3, '2026-05-05 11:38:39', 1),
(2, 9, 3, '2026-05-05 11:54:31', 1),
(3, 13, 4, '2026-06-21 14:47:49', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
