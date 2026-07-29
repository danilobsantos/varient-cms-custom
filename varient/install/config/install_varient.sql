-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20250519.4c4fa606a0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2026 at 02:01 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `install_varient`
--

-- --------------------------------------------------------

--
-- Table structure for table `ad_spaces`
--

CREATE TABLE `ad_spaces` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `ad_space` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ad_code_desktop` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `desktop_width` int DEFAULT NULL,
  `desktop_height` int DEFAULT NULL,
  `ad_code_mobile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `mobile_width` int DEFAULT NULL,
  `mobile_height` int DEFAULT NULL,
  `display_category_id` int DEFAULT NULL,
  `paragraph_number` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent_id` int DEFAULT '0',
  `parent_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `depth` int DEFAULT NULL,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `block_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_order` int DEFAULT '0',
  `show_on_homepage` tinyint(1) DEFAULT '1',
  `show_on_menu` tinyint(1) DEFAULT '1',
  `is_premium` tinyint(1) DEFAULT '0',
  `is_exclusive` tinyint(1) DEFAULT '0',
  `exclusive_price` decimal(15,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ci_sessions`
--

INSERT INTO `ci_sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('vr_session:9ed1609f0fbf19fc3b113b64480db30e', '127.0.0.1', '2026-04-15 00:04:38', 0x5f5f63695f6c6173745f726567656e65726174657c693a313737363231313434363b617574685f757365725f69647c693a313b617574685f746f6b656e7c733a36343a2265323665343034616231383330386531623064633661373964363134653833386162333964353439646632366264616662363133666538626633663135626638223b5f63695f70726576696f75735f75726c7c733a37343a22687474703a2f2f6c6f63616c686f73743a383838382f76617269656e742d332e302f61646d696e2f676c6f62616c2d73657474696e67733f6163746976655f7461623d67656e6572616c223b737563636573737c733a32373a224368616e676573207375636365737366756c6c7920736176656421223b5f5f63695f766172737c613a313a7b733a373a2273756363657373223b733a333a226f6c64223b7d);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT '0',
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `like_count` int DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `site_lang` int NOT NULL DEFAULT '1',
  `multilingual_system` tinyint(1) DEFAULT '1',
  `theme_mode` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'light',
  `timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'America/New_York',
  `app_icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `app_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `show_rss` tinyint(1) DEFAULT '1',
  `rss_content_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''summary''',
  `rss_max_posts_per_feed` int DEFAULT '50',
  `g_analytics_status` tinyint(1) DEFAULT '0',
  `g_analytics_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_featured_section` tinyint(1) DEFAULT '1',
  `show_latest_posts` tinyint(1) DEFAULT '1',
  `pwa_status` tinyint(1) DEFAULT '0',
  `registration_system` tinyint(1) DEFAULT '1',
  `post_url_structure` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''slug''',
  `comment_system` tinyint(1) DEFAULT '1',
  `comment_approval_system` tinyint(1) DEFAULT '1',
  `show_post_author` tinyint(1) DEFAULT '1',
  `show_post_date` tinyint(1) DEFAULT '1',
  `show_pageviews` tinyint(1) DEFAULT '1',
  `popular_posts_limit` smallint DEFAULT '5',
  `related_posts_limit` smallint DEFAULT '6',
  `custom_header_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `custom_footer_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `adsense_activation_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `emoji_reactions` tinyint(1) DEFAULT '1',
  `mail_contact_status` tinyint(1) DEFAULT '0',
  `mail_contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_cache_system` tinyint(1) DEFAULT '0',
  `app_cache_system` tinyint(1) DEFAULT '0',
  `content_cache_ttl` int DEFAULT '1800',
  `auto_cache_refresh` tinyint(1) DEFAULT '0',
  `email_verification` tinyint(1) DEFAULT '0',
  `pagination_per_page` int DEFAULT '16',
  `file_manager_show_all_files` tinyint(1) DEFAULT '1',
  `audio_download_button` tinyint(1) DEFAULT '1',
  `require_approval_new_posts` tinyint(1) DEFAULT '1',
  `require_approval_edited_posts` tinyint(1) DEFAULT '1',
  `show_home_link` tinyint(1) DEFAULT '1',
  `post_formats` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_user_email_on_profile` tinyint(1) DEFAULT '1',
  `reward_system_status` tinyint(1) DEFAULT '0',
  `reward_amount` double DEFAULT '1',
  `human_verification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payout_methods` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `premium_membership_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `security_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `currency_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `captcha_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `newsletter_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `email_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `storage_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `active_storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `maintenance_mode_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `social_login_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `featured_content_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sitemap_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `google_news_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `auto_post_deletion_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `redirect_rss_posts_to_original` tinyint(1) DEFAULT '0',
  `image_file_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''JPG''',
  `allowed_file_extensions` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_upload_limits` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `google_maps_status` tinyint(1) DEFAULT '0',
  `google_maps_api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delete_images_with_post` tinyint(1) DEFAULT '0',
  `sticky_sidebar` tinyint(1) DEFAULT '0',
  `ai_writer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bulk_post_upload_for_authors` tinyint DEFAULT '1',
  `routes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `invoice_prefix` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'INV',
  `cron_secret_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_system_cron_run` timestamp NULL DEFAULT NULL,
  `version` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `site_lang`, `multilingual_system`, `theme_mode`, `timezone`, `app_icon`, `app_key`, `logo`, `show_rss`, `rss_content_type`, `rss_max_posts_per_feed`, `g_analytics_status`, `g_analytics_id`, `show_featured_section`, `show_latest_posts`, `pwa_status`, `registration_system`, `post_url_structure`, `comment_system`, `comment_approval_system`, `show_post_author`, `show_post_date`, `show_pageviews`, `popular_posts_limit`, `related_posts_limit`, `custom_header_codes`, `custom_footer_codes`, `adsense_activation_code`, `emoji_reactions`, `mail_contact_status`, `mail_contact`, `content_cache_system`, `app_cache_system`, `content_cache_ttl`, `auto_cache_refresh`, `email_verification`, `pagination_per_page`, `file_manager_show_all_files`, `audio_download_button`, `require_approval_new_posts`, `require_approval_edited_posts`, `show_home_link`, `post_formats`, `show_user_email_on_profile`, `reward_system_status`, `reward_amount`, `human_verification`, `payout_methods`, `premium_membership_settings`, `security_settings`, `currency_settings`, `captcha_settings`, `newsletter_settings`, `email_settings`, `storage_settings`, `active_storage`, `maintenance_mode_settings`, `social_login_settings`, `featured_content_settings`, `sitemap_settings`, `google_news_settings`, `auto_post_deletion_settings`, `redirect_rss_posts_to_original`, `image_file_format`, `allowed_file_extensions`, `file_upload_limits`, `google_maps_status`, `google_maps_api_key`, `delete_images_with_post`, `sticky_sidebar`, `ai_writer`, `bulk_post_upload_for_authors`, `routes`, `invoice_prefix`, `cron_secret_key`, `last_system_cron_run`, `version`) VALUES
(1, 1, 1, 'light', 'America/New_York', NULL, NULL, '{\"width\":150,\"height\":50}', 1, 'summary', 50, 0, '', 1, 1, 0, 1, 'slug', 1, 1, 1, 1, 1, 5, 6, NULL, NULL, NULL, 1, 0, NULL, 0, 0, 1800, 0, 0, 16, 0, 1, 1, 1, 1, '{\"article\":1,\"gallery\":1,\"sorted_list\":1,\"table_of_contents\":1,\"video\":1,\"audio\":1,\"trivia_quiz\":1,\"personality_quiz\":1,\"poll\":1,\"recipe\":1,\"event\":1}', 1, 0, 0.25, '{\"status\":0,\"time_spent\":0,\"mouse\":0,\"scroll\":0}', '{\"paypal_status\":0,\"paypal_min_amount\":0,\"bitcoin_status\":0,\"bitcoin_min_amount\":0,\"iban_status\":0,\"iban_min_amount\":0,\"swift_status\":0,\"swift_min_amount\":0}', '{\"subscription_status\":0,\"subscription_mode\":\"all\",\"exclusive_sale_status\":0,\"default_content_price\":\"0\",\"paywall_appearance\":\"hard\",\"subscription_button_color\":\"#18181b\",\"subscription_button_visibility\":0}', '{\"max_login_attempts\":5,\"lockout_time\":300,\"password_min_length\":4,\"password_require_complex\":0,\"spam_protection_mode_post\":\"sanitize\",\"spam_protection_mode_public\":\"block\"}', '{\"code\":\"USD\",\"symbol\":\"$\",\"symbol_direction\":\"left\",\"thousand_separator\":\".\",\"decimal_separator\":\",\"}', NULL, '{\"status\":1,\"popup_status\":0}', NULL, NULL, 'local', '{\"status\":0,\"title\":\"Coming Soon!\",\"description\":\"Our website is under construction. We\'ll be here soon with our new awesome site.\"}', NULL, '{\"slider_source\":\"manual\",\"slider_sorting\":\"slider_order\",\"slider_duration\":10,\"slider_limit\":15,\"featured_status\":false,\"featured_source\":\"manual\",\"featured_sorting\":\"featured_order\",\"featured_duration\":10,\"exclude_slider_posts\":\"0\",\"recommended_sorting\":\"by_date\",\"recommended_duration\":20,\"recommended_limit\":5,\"br_news_status\":\"1\",\"br_news_source\":\"manual\",\"br_news_sorting\":\"by_date\",\"br_news_duration\":5,\"br_news_limit\":15}', '{\"frequency\":\"auto\",\"last_modification\":\"auto\",\"priority\":\"auto\"}', '{\"status\":0,\"site_name\":\"My News\",\"content_type\":\"content\",\"post_limit\":50}', '{\"status\":0,\"days\":30,\"deletion_method\":\"all\"}', 0, 'JPG', '[\"jpg\",\"png\",\"pdf\"]', '{\"image\":20,\"video\":50,\"audio\":20,\"file\":50}', 0, '', 0, 0, NULL, 1, '{\"admin\":\"admin\",\"profile\":\"profile\",\"tag\":\"tag\",\"reading_list\":\"reading-list\",\"account_settings\":\"account-settings\",\"social_accounts\":\"social-accounts\",\"change_password\":\"change-password\",\"forgot_password\":\"forgot-password\",\"reset_password\":\"reset-password\",\"delete_account\":\"delete-account\",\"manage_subscription\":\"manage-subscription\",\"purchased_content\":\"purchased-content\",\"billing\":\"billing\",\"payment_history\":\"payment-history\",\"sign_up\":\"sign-up\",\"posts\":\"posts\",\"search\":\"search\",\"rss_feeds\":\"rss-feeds\",\"gallery\":\"gallery\",\"subscription\":\"subscription\",\"plans\":\"plans\",\"checkout\":\"checkout\",\"payment\":\"payment\",\"invoice\":\"invoice\",\"success\":\"success\"}', 'INV', NULL, '2026-04-14 16:19:29', '3.0');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `vat_rate` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `vat_rate`) VALUES
(1, 'Afghanistan', 0.00),
(2, 'Albania', 0.00),
(3, 'Algeria', 0.00),
(4, 'American Samoa', 0.00),
(5, 'Andorra', 0.00),
(6, 'Angola', 0.00),
(7, 'Anguilla', 0.00),
(8, 'Antarctica', 0.00),
(9, 'Antigua and Barbuda', 0.00),
(10, 'Argentina', 0.00),
(11, 'Armenia', 0.00),
(12, 'Aruba', 0.00),
(13, 'Australia', 0.00),
(14, 'Austria', 0.00),
(15, 'Azerbaijan', 0.00),
(16, 'Bahamas', 0.00),
(17, 'Bahrain', 0.00),
(18, 'Bangladesh', 0.00),
(19, 'Barbados', 0.00),
(20, 'Belarus', 0.00),
(21, 'Belgium', 0.00),
(22, 'Belize', 0.00),
(23, 'Benin', 0.00),
(24, 'Bermuda', 0.00),
(25, 'Bhutan', 0.00),
(26, 'Bolivia', 0.00),
(27, 'Bosnia and Herzegovina', 0.00),
(28, 'Botswana', 0.00),
(29, 'Bouvet Island', 0.00),
(30, 'Brazil', 0.00),
(31, 'British Indian Ocean Territory', 0.00),
(32, 'Brunei Darussalam', 0.00),
(33, 'Bulgaria', 0.00),
(34, 'Burkina Faso', 0.00),
(35, 'Burundi', 0.00),
(36, 'Cambodia', 0.00),
(37, 'Cameroon', 0.00),
(38, 'Canada', 0.00),
(39, 'Cape Verde', 0.00),
(40, 'Cayman Islands', 0.00),
(41, 'Central African Republic', 0.00),
(42, 'Chad', 0.00),
(43, 'Chile', 0.00),
(44, 'China', 0.00),
(45, 'Christmas Island', 0.00),
(46, 'Cocos (Keeling) Islands', 0.00),
(47, 'Colombia', 0.00),
(48, 'Comoros', 0.00),
(49, 'Congo', 0.00),
(50, 'Cook Islands', 0.00),
(51, 'Costa Rica', 0.00),
(52, 'Croatia (Hrvatska)', 0.00),
(53, 'Cuba', 0.00),
(54, 'Cyprus', 0.00),
(55, 'Czech Republic', 0.00),
(56, 'Denmark', 0.00),
(57, 'Djibouti', 0.00),
(58, 'Dominica', 0.00),
(59, 'Dominican Republic', 0.00),
(60, 'East Timor', 0.00),
(61, 'Ecuador', 0.00),
(62, 'Egypt', 0.00),
(63, 'El Salvador', 0.00),
(64, 'Equatorial Guinea', 0.00),
(65, 'Eritrea', 0.00),
(66, 'Estonia', 0.00),
(67, 'Ethiopia', 0.00),
(68, 'Falkland Islands (Malvinas)', 0.00),
(69, 'Faroe Islands', 0.00),
(70, 'Fiji', 0.00),
(71, 'Finland', 0.00),
(72, 'France', 0.00),
(73, 'France, Metropolitan', 0.00),
(74, 'French Guiana', 0.00),
(75, 'French Polynesia', 0.00),
(76, 'French Southern Territories', 0.00),
(77, 'Gabon', 0.00),
(78, 'Gambia', 0.00),
(79, 'Georgia', 0.00),
(80, 'Germany', 0.00),
(81, 'Ghana', 0.00),
(82, 'Gibraltar', 0.00),
(83, 'Greece', 0.00),
(84, 'Greenland', 0.00),
(85, 'Grenada', 0.00),
(86, 'Guadeloupe', 0.00),
(87, 'Guam', 0.00),
(88, 'Guatemala', 0.00),
(89, 'Guernsey', 0.00),
(90, 'Guinea', 0.00),
(91, 'Guinea-Bissau', 0.00),
(92, 'Guyana', 0.00),
(93, 'Haiti', 0.00),
(94, 'Heard and McDonald Islands', 0.00),
(95, 'Honduras', 0.00),
(96, 'Hong Kong', 0.00),
(97, 'Hungary', 0.00),
(98, 'Iceland', 0.00),
(99, 'India', 0.00),
(100, 'Indonesia', 0.00),
(101, 'Iran', 0.00),
(102, 'Iraq', 0.00),
(103, 'Ireland', 0.00),
(104, 'Isle of Man', 0.00),
(105, 'Israel', 0.00),
(106, 'Italy', 0.00),
(107, 'Ivory Coast', 0.00),
(108, 'Jamaica', 0.00),
(109, 'Japan', 0.00),
(110, 'Jersey', 0.00),
(111, 'Jordan', 0.00),
(112, 'Kazakhstan', 0.00),
(113, 'Kenya', 0.00),
(114, 'Kiribati', 0.00),
(115, 'Kosovo', 0.00),
(116, 'Kuwait', 0.00),
(117, 'Kyrgyzstan', 0.00),
(118, 'Lao', 0.00),
(119, 'Latvia', 0.00),
(120, 'Lebanon', 0.00),
(121, 'Lesotho', 0.00),
(122, 'Liberia', 0.00),
(123, 'Libyan Arab Jamahiriya', 0.00),
(124, 'Liechtenstein', 0.00),
(125, 'Lithuania', 0.00),
(126, 'Luxembourg', 0.00),
(127, 'Macau', 0.00),
(128, 'Macedonia', 0.00),
(129, 'Madagascar', 0.00),
(130, 'Malawi', 0.00),
(131, 'Malaysia', 0.00),
(132, 'Maldives', 0.00),
(133, 'Mali', 0.00),
(134, 'Malta', 0.00),
(135, 'Marshall Islands', 0.00),
(136, 'Martinique', 0.00),
(137, 'Mauritania', 0.00),
(138, 'Mauritius', 0.00),
(139, 'Mayotte', 0.00),
(140, 'Mexico', 0.00),
(141, 'Micronesia, Federated States of', 0.00),
(142, 'Moldova, Republic of', 0.00),
(143, 'Monaco', 0.00),
(144, 'Mongolia', 0.00),
(145, 'Montenegro', 0.00),
(146, 'Montserrat', 0.00),
(147, 'Morocco', 0.00),
(148, 'Mozambique', 0.00),
(149, 'Myanmar', 0.00),
(150, 'Namibia', 0.00),
(151, 'Nauru', 0.00),
(152, 'Nepal', 0.00),
(153, 'Netherlands', 0.00),
(154, 'Netherlands Antilles', 0.00),
(155, 'New Caledonia', 0.00),
(156, 'New Zealand', 0.00),
(157, 'Nicaragua', 0.00),
(158, 'Niger', 0.00),
(159, 'Nigeria', 0.00),
(160, 'Niue', 0.00),
(161, 'Norfolk Island', 0.00),
(162, 'North Korea', 0.00),
(163, 'Northern Mariana Islands', 0.00),
(164, 'Norway', 0.00),
(165, 'Oman', 0.00),
(166, 'Pakistan', 0.00),
(167, 'Palau', 0.00),
(168, 'Palestine', 0.00),
(169, 'Panama', 0.00),
(170, 'Papua New Guinea', 0.00),
(171, 'Paraguay', 0.00),
(172, 'Peru', 0.00),
(173, 'Philippines', 0.00),
(174, 'Pitcairn', 0.00),
(175, 'Poland', 0.00),
(176, 'Portugal', 0.00),
(177, 'Puerto Rico', 0.00),
(178, 'Qatar', 0.00),
(179, 'Reunion', 0.00),
(180, 'Romania', 0.00),
(181, 'Russian Federation', 0.00),
(182, 'Rwanda', 0.00),
(183, 'Saint Kitts and Nevis', 0.00),
(184, 'Saint Lucia', 0.00),
(185, 'Saint Vincent and the Grenadines', 0.00),
(186, 'Samoa', 0.00),
(187, 'San Marino', 0.00),
(188, 'Sao Tome and Principe', 0.00),
(189, 'Saudi Arabia', 0.00),
(190, 'Senegal', 0.00),
(191, 'Serbia', 0.00),
(192, 'Seychelles', 0.00),
(193, 'Sierra Leone', 0.00),
(194, 'Singapore', 0.00),
(195, 'Slovakia', 0.00),
(196, 'Slovenia', 0.00),
(197, 'Solomon Islands', 0.00),
(198, 'Somalia', 0.00),
(199, 'South Africa', 0.00),
(200, 'South Georgia South Sandwich Islands', 0.00),
(201, 'South Korea', 0.00),
(202, 'Spain', 0.00),
(203, 'Sri Lanka', 0.00),
(204, 'St. Helena', 0.00),
(205, 'St. Pierre and Miquelon', 0.00),
(206, 'Sudan', 0.00),
(207, 'Suriname', 0.00),
(208, 'Svalbard and Jan Mayen Islands', 0.00),
(209, 'Swaziland', 0.00),
(210, 'Sweden', 0.00),
(211, 'Switzerland', 0.00),
(212, 'Syrian Arab Republic', 0.00),
(213, 'Taiwan', 0.00),
(214, 'Tajikistan', 0.00),
(215, 'Tanzania', 0.00),
(216, 'Thailand', 0.00),
(217, 'Togo', 0.00),
(218, 'Tokelau', 0.00),
(219, 'Tonga', 0.00),
(220, 'Trinidad and Tobago', 0.00),
(221, 'Tunisia', 0.00),
(222, 'Turkey', 0.00),
(223, 'Turkmenistan', 0.00),
(224, 'Turks and Caicos Islands', 0.00),
(225, 'Tuvalu', 0.00),
(226, 'Uganda', 0.00),
(227, 'Ukraine', 0.00),
(228, 'United Arab Emirates', 0.00),
(229, 'United Kingdom', 0.00),
(230, 'United States', 0.00),
(231, 'United States minor outlying islands', 0.00),
(232, 'Uruguay', 0.00),
(233, 'Uzbekistan', 0.00),
(234, 'Vanuatu', 0.00),
(235, 'Vatican City State', 0.00),
(236, 'Venezuela', 0.00),
(237, 'Vietnam', 0.00),
(238, 'Virgin Islands (British)', 0.00),
(239, 'Virgin Islands (U.S.)', 0.00),
(240, 'Wallis and Futuna Islands', 0.00),
(241, 'Western Sahara', 0.00),
(242, 'Yemen', 0.00),
(243, 'Zaire', 0.00),
(244, 'Zambia', 0.00),
(245, 'Zimbabwe', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `email_blacklist`
--

CREATE TABLE `email_blacklist` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `custom_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `id` int NOT NULL,
  `following_id` int DEFAULT NULL,
  `follower_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fonts`
--

CREATE TABLE `fonts` (
  `id` int NOT NULL,
  `font_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_family` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `path_400` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path_600` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path_700` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fonts`
--

INSERT INTO `fonts` (`id`, `font_name`, `font_family`, `font_key`, `font_type`, `font_source`, `path_400`, `path_600`, `path_700`, `is_default`) VALUES
(1, 'Arial', 'Arial, Helvetica, sans-serif', 'arial', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(2, 'Courier New', '\"Courier New\", Courier, monospace', 'courier-new', 'monospace', 'system', NULL, NULL, NULL, 1),
(3, 'DM Sans', '\"DM Sans\", sans-serif', 'dm-sans', 'sans-serif', 'local', 'assets/fonts/dm-sans/dm-sans-400.woff2', 'assets/fonts/dm-sans/dm-sans-600.woff2', 'assets/fonts/dm-sans/dm-sans-700.woff2', 1),
(4, 'Georgia', 'Georgia, serif', 'georgia', 'serif', 'system', NULL, NULL, NULL, 1),
(5, 'Helvetica Neue', '\"Helvetica Neue\", Helvetica, Arial, sans-serif', 'helvetica', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(6, 'IBM Plex Sans', '\"IBM Plex Sans\", sans-serif', 'ibm-plex-sans', 'sans-serif', 'local', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-400.woff2', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-600.woff2', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-700.woff2', 1),
(7, 'Inter', '\"Inter\", sans-serif', 'inter', 'sans-serif', 'local', 'assets/fonts/inter/inter-400.woff2', 'assets/fonts/inter/inter-600.woff2', 'assets/fonts/inter/inter-700.woff2', 1),
(8, 'Jost', '\"Jost\", sans-serif', 'jost', 'sans-serif', 'local', 'assets/fonts/jost/jost-400.woff2', 'assets/fonts/jost/jost-600.woff2', 'assets/fonts/jost/jost-700.woff2', 1),
(9, 'Libre Baskerville', '\"Libre Baskerville\", serif', 'libre-baskerville', 'serif', 'local', 'assets/fonts/libre-baskerville/libre-baskerville-400.woff2', 'assets/fonts/libre-baskerville/libre-baskerville-600.woff2', 'assets/fonts/libre-baskerville/libre-baskerville-700.woff2', 1),
(10, 'Merriweather', '\"Merriweather\", serif', 'merriweather', 'serif', 'local', 'assets/fonts/merriweather/merriweather-400.woff2', 'assets/fonts/merriweather/merriweather-600.woff2', 'assets/fonts/merriweather/merriweather-700.woff2', 1),
(11, 'Montserrat', '\"Montserrat\", sans-serif', 'montserrat', 'sans-serif', 'local', 'assets/fonts/montserrat/montserrat-400.woff2', 'assets/fonts/montserrat/montserrat-600.woff2', 'assets/fonts/montserrat/montserrat-700.woff2', 1),
(12, 'Noto Serif', '\"Noto Serif\", serif', 'noto-serif', 'serif', 'local', 'assets/fonts/noto-serif/noto-serif-400.woff2', 'assets/fonts/noto-serif/noto-serif-600.woff2', 'assets/fonts/noto-serif/noto-serif-700.woff2', 1),
(13, 'Open Sans', '\"Open Sans\", sans-serif', 'open-sans', 'sans-serif', 'local', 'assets/fonts/open-sans/open-sans-400.woff2', 'assets/fonts/open-sans/open-sans-600.woff2', 'assets/fonts/open-sans/open-sans-700.woff2', 1),
(14, 'Plus Jakarta Sans', '\"Plus Jakarta Sans\", sans-serif', 'plus-jakarta-sans', 'sans-serif', 'local', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-400.woff2', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-600.woff2', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-700.woff2', 1),
(15, 'Poppins', '\"Poppins\", sans-serif', 'poppins', 'sans-serif', 'local', 'assets/fonts/poppins/poppins-400.woff2', 'assets/fonts/poppins/poppins-600.woff2', 'assets/fonts/poppins/poppins-700.woff2', 1),
(16, 'Roboto', '\"Roboto\", sans-serif', 'roboto', 'sans-serif', 'local', 'assets/fonts/roboto/roboto-400.woff2', 'assets/fonts/roboto/roboto-600.woff2', 'assets/fonts/roboto/roboto-700.woff2', 1),
(17, 'Source Sans 3', '\"Source Sans 3\", sans-serif', 'source-sans-3', 'sans-serif', 'local', 'assets/fonts/source-sans-3/source-sans-3-400.woff2', 'assets/fonts/source-sans-3/source-sans-3-600.woff2', 'assets/fonts/source-sans-3/source-sans-3-700.woff2', 1),
(18, 'Tahoma', 'Tahoma, Geneva, sans-serif', 'tahoma', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(19, 'Times New Roman', '\"Times New Roman\", Times, serif', 'times-new-roman', 'serif', 'system', NULL, NULL, NULL, 1),
(20, 'Trebuchet MS', '\"Trebuchet MS\", Helvetica, sans-serif', 'trebuchet-ms', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(21, 'Verdana', 'Verdana, Geneva, sans-serif', 'verdana', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(22, 'Work Sans', '\"Work Sans\", sans-serif', 'work-sans', 'sans-serif', 'local', 'assets/fonts/work-sans/work-sans-400.woff2', 'assets/fonts/work-sans/work-sans-600.woff2', 'assets/fonts/work-sans/work-sans-700.woff2', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gallery_albums`
--

CREATE TABLE `gallery_albums` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_order` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_categories`
--

CREATE TABLE `gallery_categories` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `path_big` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_album_cover` tinyint(1) DEFAULT '0',
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int NOT NULL,
  `image_big` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_slider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_mid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_extension` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'jpg',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `short_form` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `language_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `text_direction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `text_editor_lang` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'en',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `language_order` smallint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `short_form`, `language_code`, `text_direction`, `text_editor_lang`, `status`, `language_order`) VALUES
(1, 'English', 'en', 'en-US', 'ltr', 'en', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `language_translations`
--

CREATE TABLE `language_translations` (
  `id` int NOT NULL,
  `lang_id` smallint DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `translation` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `language_translations`
--

INSERT INTO `language_translations` (`id`, `lang_id`, `label`, `translation`) VALUES
(1, 1, 'about_event', 'About the Event'),
(2, 1, 'about_me', 'About Me'),
(3, 1, 'accept_all', 'Accept All'),
(4, 1, 'access_ends_on', 'Access Ends On'),
(5, 1, 'access_key', 'Access Key'),
(6, 1, 'access_level', 'Access Level'),
(7, 1, 'account', 'Account'),
(8, 1, 'account_settings', 'Account Settings'),
(9, 1, 'activate', 'Activate'),
(10, 1, 'activated', 'Activated'),
(11, 1, 'active', 'Active'),
(12, 1, 'active_payment_request_error', 'You already have an active payment request! Once this is complete, you can make a new request.'),
(13, 1, 'active_provider', 'Active Provider'),
(14, 1, 'active_storage', 'Active Storage'),
(15, 1, 'activity', 'Activity'),
(16, 1, 'additional_images', 'Additional Images'),
(17, 1, 'additional_info', 'Additional Info'),
(18, 1, 'address', 'Address'),
(19, 1, 'add_album', 'Add Album'),
(20, 1, 'add_answer', 'Add Answer'),
(21, 1, 'add_article', 'Add Article'),
(22, 1, 'add_audio', 'Add Audio'),
(23, 1, 'add_badge', 'Add Badge'),
(24, 1, 'add_breaking_news', 'Add to Breaking News'),
(25, 1, 'add_category', 'Add Category'),
(26, 1, 'add_custom_field', 'Add Custom Field'),
(27, 1, 'add_email', 'Add Email'),
(28, 1, 'add_event', 'Add Event'),
(29, 1, 'add_featured', 'Add to Featured'),
(30, 1, 'add_feed', 'Add Feed'),
(31, 1, 'add_font', 'Add Font'),
(32, 1, 'add_gallery', 'Add Gallery'),
(33, 1, 'add_iframe', 'Add Iframe'),
(34, 1, 'add_image', 'Add Image'),
(35, 1, 'add_image_url', 'Add Image Url'),
(36, 1, 'add_language', 'Add Language'),
(37, 1, 'add_menu_link', 'Add Menu Link'),
(38, 1, 'add_new', 'Add New'),
(39, 1, 'add_new_item', 'Add New Item'),
(40, 1, 'add_new_plan', 'Add New Plan'),
(41, 1, 'add_nofollow_seo_safe', 'Add \"Nofollow\" (SEO Safe)'),
(42, 1, 'add_option', 'Add Option'),
(43, 1, 'add_page', 'Add Page'),
(44, 1, 'add_payout', 'Add Payout'),
(45, 1, 'add_personality_quiz', 'Add Personality Quiz'),
(46, 1, 'add_plan', 'Add Plan'),
(47, 1, 'add_poll', 'Add Poll'),
(48, 1, 'add_post', 'Add Post'),
(49, 1, 'add_posts_as_draft', 'Add Posts as Draft'),
(50, 1, 'add_question', 'Add Question'),
(51, 1, 'add_quiz', 'Add Quiz'),
(52, 1, 'add_reading_list', 'Add to Reading List'),
(53, 1, 'add_recipe', 'Add Recipe'),
(54, 1, 'add_recommended', 'Add to Recommended'),
(55, 1, 'add_result', 'Add Result'),
(56, 1, 'add_role', 'Add Role'),
(57, 1, 'add_slider', 'Add to Slider'),
(58, 1, 'add_sorted_list', 'Add Sorted List'),
(59, 1, 'add_table_of_contents', 'Add Table of Contents'),
(60, 1, 'add_tag', 'Add Tag'),
(61, 1, 'add_trivia_quiz', 'Add Trivia Quiz'),
(62, 1, 'add_user', 'Add User'),
(63, 1, 'add_video', 'Add Video'),
(64, 1, 'add_widget', 'Add Widget'),
(65, 1, 'admin', 'Admin'),
(66, 1, 'admin_panel', 'Admin Panel'),
(67, 1, 'admin_panel_link', 'Admin Panel Link'),
(68, 1, 'adsense_activation_code', 'AdSense Activation Code'),
(69, 1, 'advanced', 'Advanced'),
(70, 1, 'ad_free_experience', 'Ad-Free Experience'),
(71, 1, 'ad_size', 'Ad Size'),
(72, 1, 'ad_space', 'Ad Space'),
(73, 1, 'ad_spaces', 'Ad Spaces'),
(74, 1, 'ad_space_header', 'Header'),
(75, 1, 'ad_space_index_bottom', 'Index (Bottom)'),
(76, 1, 'ad_space_index_top', 'Index (Top)'),
(77, 1, 'ad_space_in_article', 'In-Article'),
(78, 1, 'ad_space_paragraph_exp', 'The ad will be displayed after the paragraph number you selected'),
(79, 1, 'ad_space_posts_bottom', 'Posts (Bottom)'),
(80, 1, 'ad_space_posts_exp', 'This ad will be displayed on Posts, Category, Profile, Tag, Search and Profile pages'),
(81, 1, 'ad_space_posts_top', 'Posts (Top)'),
(82, 1, 'ad_space_post_bottom', 'Post Details (Bottom)'),
(83, 1, 'ad_space_post_top', 'Post Details (Top)'),
(84, 1, 'ago', 'ago'),
(85, 1, 'ai_content_generator', 'AI Content Generator'),
(86, 1, 'ai_writer', 'AI Writer'),
(87, 1, 'ai_writer_api_error', 'Generation failed. The API returned no content, possibly due to insufficient credits. Please check your API account.'),
(88, 1, 'album', 'Album'),
(89, 1, 'albums', 'Albums'),
(90, 1, 'album_cover', 'Album Cover'),
(91, 1, 'album_name', 'Album Name'),
(92, 1, 'all', 'All'),
(93, 1, 'allowed_file_extensions', 'Allowed File Extensions'),
(94, 1, 'allow_single_content_sales', 'Allow Single Content Sales'),
(95, 1, 'all_permissions', 'All Permissions'),
(96, 1, 'all_posts', 'All Posts'),
(97, 1, 'all_time', 'All Time'),
(98, 1, 'all_users_can_vote', 'All Users Can Vote'),
(99, 1, 'all_website_content', 'All Website Content'),
(100, 1, 'all_website_content_exp', 'Force all posts on the website to be premium.'),
(101, 1, 'alt_tag', 'Alt Tag (Accessibility)'),
(102, 1, 'always', 'Always'),
(103, 1, 'amount', 'Amount'),
(104, 1, 'analytics', 'Analytics'),
(105, 1, 'analytics_exp', 'Help us improve by collecting anonymous usage data.'),
(106, 1, 'angry', 'Angry'),
(107, 1, 'answer', 'Answer'),
(108, 1, 'answers', 'Answers'),
(109, 1, 'answer_format', 'Answer Format'),
(110, 1, 'answer_text', 'Answer Text'),
(111, 1, 'api_key', 'API Key'),
(112, 1, 'apple_music', 'Apple Music'),
(113, 1, 'application_icon', 'Application Icon'),
(114, 1, 'application_icon_exp', 'This icon will be used as the application icon, favicon, and PWA icon across the platform.'),
(115, 1, 'application_key', 'Application Key'),
(116, 1, 'apply', 'Apply'),
(117, 1, 'apply_setting_to_subcategories', 'Apply this setting to all subcategories'),
(118, 1, 'apply_to_all', 'Apply to All'),
(119, 1, 'approve', 'Approve'),
(120, 1, 'approved', 'Approved'),
(121, 1, 'approved_comments', 'Approved Comments'),
(122, 1, 'app_cache', 'Application Cache'),
(123, 1, 'app_cache_exp', 'Caches core application data such as settings, languages, categories, static pages, and other related entities. Persisted until explicitly updated when data changes.'),
(124, 1, 'app_id', 'App ID'),
(125, 1, 'app_name', 'Application Name'),
(126, 1, 'app_secret', 'App Secret'),
(127, 1, 'app_store', 'App Store'),
(128, 1, 'April', 'Apr'),
(129, 1, 'article', 'Article'),
(130, 1, 'article_post_exp', 'An article with images and embed videos'),
(131, 1, 'assign_subscription', 'Assign Subscription'),
(132, 1, 'attachments', 'Attachments'),
(133, 1, 'audio', 'Audio'),
(134, 1, 'audios', 'Audios'),
(135, 1, 'audios_exp', 'Select your audios and create your playlist'),
(136, 1, 'audio_download_button', 'Audio Download Button'),
(137, 1, 'audio_post_exp', 'Upload audios and create playlist'),
(138, 1, 'August', 'Aug'),
(139, 1, 'author', 'Author'),
(140, 1, 'author_earnings', 'Author Earnings'),
(141, 1, 'automatically_calculated', 'Automatically Calculated'),
(142, 1, 'auto_post_deletion', 'Auto Post Deletion'),
(143, 1, 'auto_update', 'Auto Update'),
(144, 1, 'avatar', 'Avatar'),
(145, 1, 'back', 'Back'),
(146, 1, 'back_to_billing', 'Back to Billing'),
(147, 1, 'back_to_gallery', 'Back to Gallery'),
(148, 1, 'badge', 'Badge'),
(149, 1, 'badges', 'Badges'),
(150, 1, 'balance', 'Balance'),
(151, 1, 'bank_account_holder_name', 'Bank Account Holder\'s Name'),
(152, 1, 'bank_branch_city', 'Bank Branch City'),
(153, 1, 'bank_branch_country', 'Bank Branch Country'),
(154, 1, 'bank_name', 'Bank Name'),
(155, 1, 'banned', 'Banned'),
(156, 1, 'banner', 'Banner'),
(157, 1, 'banner_desktop', 'Desktop Banner'),
(158, 1, 'banner_desktop_exp', 'This ad will be displayed on screens larger than 992px'),
(159, 1, 'banner_mobile', 'Mobile Banner'),
(160, 1, 'banner_mobile_exp', 'This ad will be displayed on screens smaller than 992px'),
(161, 1, 'ban_user', 'Ban User'),
(162, 1, 'basic_information', 'Basic Information'),
(163, 1, 'behance', 'Behance'),
(164, 1, 'billed_by', 'Billed By'),
(165, 1, 'billed_to', 'Billed To'),
(166, 1, 'billing_address', 'Billing Address'),
(167, 1, 'billing_and_payments', 'Billing & Payments'),
(168, 1, 'billing_cycle', 'Billing Cycle'),
(169, 1, 'billing_details', 'Billing Details'),
(170, 1, 'billing_details_exp', 'Manage your billing address and tax information for a seamless checkout experience.'),
(171, 1, 'billing_information', 'Billing Information'),
(172, 1, 'billing_order_summary', 'Billing & Order Summary'),
(173, 1, 'bitcoin', 'Bitcoin'),
(174, 1, 'bitcoin_address', 'Bitcoin Address'),
(175, 1, 'blacklisted_emails_exp', 'Blacklisted emails are restricted from all email-based actions, including logging in, posting comments, and sending contact messages.'),
(176, 1, 'block_color', 'Top Header and Block Heads Color'),
(177, 1, 'block_style', 'Block Style'),
(178, 1, 'bluesky', 'Bluesky'),
(179, 1, 'bot_verification_failed', 'Verification failed. Please verify you are not a robot!'),
(180, 1, 'breaking', 'Breaking'),
(181, 1, 'breaking_news', 'Breaking News'),
(182, 1, 'browse_files', 'Browse Files'),
(183, 1, 'btn_send', 'Send'),
(184, 1, 'btn_submit', 'Submit'),
(185, 1, 'btn_subscribe', 'Subscribe'),
(186, 1, 'bucket_name', 'Bucket Name'),
(187, 1, 'bulk_actions', 'Bulk Actions'),
(188, 1, 'bulk_post_upload', 'Bulk Post Upload'),
(189, 1, 'bulk_post_upload_for_authors', 'Bulk Post Upload for Authors'),
(190, 1, 'bulk_vat_update', 'Bulk VAT Update'),
(191, 1, 'button_text', 'Button Text'),
(192, 1, 'button_visibility', 'Button Visibility'),
(193, 1, 'by_date', 'by Date'),
(194, 1, 'by_featured_order', 'by Featured Order'),
(195, 1, 'by_slider_order', 'by Slider Order'),
(196, 1, 'cache_refresh_time', 'Cache Refresh Time (Minute)'),
(197, 1, 'cache_refresh_time_exp', 'After this time, your cache files will be refreshed.'),
(198, 1, 'cache_system', 'Cache System'),
(199, 1, 'cancel', 'Cancel'),
(200, 1, 'cancelled', 'Cancelled'),
(201, 1, 'cancel_subscription', 'Cancel Subscription'),
(202, 1, 'cancel_subscription_exp', 'Are you sure you want to cancel your subscription? You will still have access to all premium features until the end of your current billing cycle.'),
(203, 1, 'capacity_limit', 'Capacity Limit'),
(204, 1, 'captcha', 'Captcha'),
(205, 1, 'captcha_provider', 'Captcha Provider'),
(206, 1, 'captcha_provider_warning', 'We strongly recommend using Cloudflare Turnstile as it offers a privacy-focused, cookie-free solution (GDPR compliant). Google reCAPTCHA may collect user data and track visitors, which requires you to obtain explicit user consent via a Cookie Banner. Ensuring compliance with data privacy laws is the sole responsibility of the site administrator.'),
(207, 1, 'captcha_settings', 'Captcha Settings'),
(208, 1, 'categories', 'Categories'),
(209, 1, 'category', 'Category'),
(210, 1, 'category_block_style', 'Category Block Style'),
(211, 1, 'category_id_finder', 'Category Id Finder'),
(212, 1, 'category_id_finder_exp', 'You can use this section to find out the Id of a category'),
(213, 1, 'category_name', 'Category Name'),
(214, 1, 'category_select_widget', 'Select the widgets you want to show in the sidebar'),
(215, 1, 'change', 'Change'),
(216, 1, 'change_avatar', 'Change Avatar'),
(217, 1, 'change_logo', 'Change logo'),
(218, 1, 'change_password', 'Change Password'),
(219, 1, 'change_password_exp', 'Update your credentials with a strong and unique password to keep your account secure.'),
(220, 1, 'change_plan', 'Change Plan'),
(221, 1, 'change_user_role', 'Change User Role'),
(222, 1, 'checkout', 'Checkout'),
(223, 1, 'checkout_one_time_payment_success', 'Thank you for your purchase! Your payment has been completed successfully. You now have full access to the purchased content.'),
(224, 1, 'checkout_subscription_success', 'Your subscription has been successfully activated and you now have unlimited access to all our premium content.'),
(225, 1, 'checkout_success_email_support_note', 'A confirmation email with your purchase details has been sent to your billing email address. If you encounter any issues, you can contact us here:'),
(226, 1, 'choose_content_hiding_method', 'Choose Content Hiding Method'),
(227, 1, 'choose_post_format', 'Choose a Post Format'),
(228, 1, 'choose_post_format_exp', 'Choose the type of content you want to create'),
(229, 1, 'circle', 'Circle'),
(230, 1, 'city', 'City'),
(231, 1, 'click_copy_icon_copy_url', 'Click copy icon to copy URL'),
(232, 1, 'client_id', 'Client ID'),
(233, 1, 'client_secret', 'Client Secret'),
(234, 1, 'close', 'Close'),
(235, 1, 'cloudflare_turnstile', 'Cloudflare Turnstile'),
(236, 1, 'color', 'Color'),
(237, 1, 'color_code', 'Color Code'),
(238, 1, 'comma', 'Comma'),
(239, 1, 'comma_separated_options', 'Comma separated options (e.g., Meat, Vegan, None)'),
(240, 1, 'comment', 'Comment'),
(241, 1, 'comments', 'Comments'),
(242, 1, 'comments_contact', 'Comments & Contact Messages'),
(243, 1, 'comment_approval_system', 'Comment Approval System'),
(244, 1, 'comment_system', 'Comment System'),
(245, 1, 'community', 'Community'),
(246, 1, 'company_invoice_details', 'Company & Invoice Details'),
(247, 1, 'company_invoice_details_exp', 'Set your company name, address, and tax information for invoices.'),
(248, 1, 'company_name', 'Company Name'),
(249, 1, 'completed', 'Completed'),
(250, 1, 'complete_payment', 'Complete Payment'),
(251, 1, 'complete_payment_button_exp', 'Please click the button below to securely complete your payment.'),
(252, 1, 'complete_payment_legal_exp', 'By completing the payment, you agree to our policies:'),
(253, 1, 'complete_registration', 'Complete Registration'),
(254, 1, 'complexity_requirement', 'Complexity Requirement'),
(255, 1, 'confirm_action', 'Are you sure you want to perform this action?'),
(256, 1, 'confirm_and_pay', 'Confirm and Pay'),
(257, 1, 'confirm_ban', 'Are you sure you want to ban this user?'),
(258, 1, 'confirm_comment', 'Are you sure you want to delete this comment?'),
(259, 1, 'confirm_delete', 'Are you sure you want to delete this item?'),
(260, 1, 'confirm_delete_file', 'Are you sure you want to delete this file?'),
(261, 1, 'confirm_delete_selected_files', 'Are you sure you want to delete selected files?'),
(262, 1, 'confirm_password', 'Confirm Password'),
(263, 1, 'confirm_user_email', 'Confirm User Email'),
(264, 1, 'contact', 'Contact'),
(265, 1, 'contact_message', 'Contact Message'),
(266, 1, 'contact_messages', 'Contact Messages'),
(267, 1, 'contact_messages_will_send', 'Contact messages will be sent to this email.'),
(268, 1, 'contact_settings', 'Contact Settings'),
(269, 1, 'contact_text', 'Contact Text'),
(270, 1, 'content', 'Content'),
(271, 1, 'content_cache', 'Content Cache'),
(272, 1, 'content_cache_exp', 'Caches dynamic content such as posts and comments. Automatically refreshed at set intervals to keep data current and minimize database queries.'),
(273, 1, 'content_font', 'Content Font (Post & Page Text)'),
(274, 1, 'content_images', 'Content Images'),
(275, 1, 'content_images_upload_exp', 'Content images are associated with specific content types, therefore general uploads are not supported in this section. Images must be uploaded through the relevant content creation form.'),
(276, 1, 'content_management', 'Content Management'),
(277, 1, 'content_settings', 'Content Settings'),
(278, 1, 'content_source', 'Content Source'),
(279, 1, 'continue_with_google', 'Continue with Google'),
(280, 1, 'cookies_warning', 'Cookies Warning'),
(281, 1, 'cook_time', 'Cook Time'),
(282, 1, 'copy', 'Copy'),
(283, 1, 'copyright', 'Copyright'),
(284, 1, 'correct', 'Correct'),
(285, 1, 'correct_answer', 'Correct Answer'),
(286, 1, 'country', 'Country'),
(287, 1, 'country_specific_rates', 'Country Specific Rates'),
(288, 1, 'create_account', 'Create an Account'),
(289, 1, 'create_account_exp', 'Join for unlimited access to premium journalism'),
(290, 1, 'create_ad_exp', 'If you don\'t have an ad code, you can create an ad code by selecting an image and adding an URL'),
(291, 1, 'cron_job_token', 'Cron Job Token'),
(292, 1, 'cron_job_token_delete_warning', 'Are you sure? Deleting this token will immediately STOP all automated cron jobs.'),
(293, 1, 'cron_job_token_exp', 'This secure token is required to execute automated tasks. Any changes (Generate or Revoke) are saved immediately.'),
(294, 1, 'csv_file', 'CSV File'),
(295, 1, 'currency_settings', 'Currency Settings'),
(296, 1, 'currency_settings_exp', 'Manage your site\'s default currency and formatting options'),
(297, 1, 'currency_symbol', 'Currency Symbol'),
(298, 1, 'current_billing_cycle', 'Current Billing Cycle'),
(299, 1, 'custom', 'Custom'),
(300, 1, 'customer_details', 'Customer Details'),
(301, 1, 'customer_ip_address', 'Customer IP Address'),
(302, 1, 'customize', 'Customize'),
(303, 1, 'custom_code_insertion', 'Custom Code Insertion'),
(304, 1, 'custom_code_insertion_exp', 'Add custom HTML, CSS, or JavaScript snippets to your website’s header or footer. Useful for analytics, tracking, or third-party integrations.'),
(305, 1, 'custom_footer_codes', 'Custom Footer Codes'),
(306, 1, 'custom_footer_codes_exp', 'These codes will be added to the footer of the site.'),
(307, 1, 'custom_form_fields', 'Custom Form Fields'),
(308, 1, 'custom_form_fields_exp', 'Full Name and Email fields are always required by default. Add any additional questions you want to ask attendees.'),
(309, 1, 'custom_header_codes', 'Custom Header Codes'),
(310, 1, 'custom_header_codes_exp', 'These codes will be added to the header of the site.'),
(311, 1, 'daily', 'Daily'),
(312, 1, 'dark', 'Dark'),
(313, 1, 'dark_mode', 'Dark Mode'),
(314, 1, 'dashboard', 'Dashboard'),
(315, 1, 'database_backup', 'Database Backup'),
(316, 1, 'data_type', 'Data Type'),
(317, 1, 'date', 'Date'),
(318, 1, 'date_added', 'Date Added'),
(319, 1, 'date_publish', 'Date Published'),
(320, 1, 'date_range', 'Date Range'),
(321, 1, 'day', 'day'),
(322, 1, 'days', 'days'),
(323, 1, 'days_remaining', 'Days Remaining'),
(324, 1, 'December', 'Dec'),
(325, 1, 'decimal_separator', 'Decimal Separator'),
(326, 1, 'default', 'Default'),
(327, 1, 'default_content_price', 'Default Content Price'),
(328, 1, 'default_content_price_exp', 'This will be the default price when you mark a post as \'Exclusive Paid Post\'. You can override this price on the post creation page.'),
(329, 1, 'default_currency', 'Default Currency'),
(330, 1, 'default_language', 'Default Language'),
(331, 1, 'default_price', 'Default Price'),
(332, 1, 'default_quantity', 'Default Quantity'),
(333, 1, 'default_theme_mode', 'Default Theme Mode'),
(334, 1, 'delete', 'Delete'),
(335, 1, 'delete_account', 'Delete Account'),
(336, 1, 'delete_account_confirm', 'Deleting your account is permanent and will remove all content including comments, avatars and profile settings. Are you sure you want to delete your account?'),
(337, 1, 'delete_account_exp', 'Permanently remove your account, personal data, and active subscriptions from the platform.'),
(338, 1, 'delete_all_posts', 'Delete All Posts'),
(339, 1, 'delete_images_with_post', 'Delete Images Along with Post'),
(340, 1, 'delete_only_rss_posts', 'Delete only RSS Posts'),
(341, 1, 'delete_reading_list', 'Remove from Reading List'),
(342, 1, 'delete_selected', 'Delete Selected'),
(343, 1, 'description', 'Description'),
(344, 1, 'detailed_report', 'Detailed Report'),
(345, 1, 'details', 'Details'),
(346, 1, 'difficulty', 'Difficulty'),
(347, 1, 'directions', 'Directions'),
(348, 1, 'disable', 'Disable'),
(349, 1, 'disable_reward_system', 'Disable Reward System'),
(350, 1, 'discord', 'Discord'),
(351, 1, 'discover', 'Discover'),
(352, 1, 'dislike', 'Dislike'),
(353, 1, 'display_duration', 'Display Duration'),
(354, 1, 'display_limit', 'Display Limit'),
(355, 1, 'display_type', 'Display Type'),
(356, 1, 'documentation', 'Documentation'),
(357, 1, 'domain', 'Domain'),
(358, 1, 'done', 'Done'),
(359, 1, 'dont_add_menu', 'Don\'t Add to Menu'),
(360, 1, 'dont_have_account', 'Don\'t have an account?'),
(361, 1, 'dont_want_receive_emails', 'Don\'t want receive these emails?'),
(362, 1, 'dot', 'Dot'),
(363, 1, 'download', 'Download'),
(364, 1, 'downloading_rss_images_exp', 'Some images may be protected by copyright. Before downloading and hosting images on your own server, please make sure you have the appropriate usage rights.\r\nTo reduce potential legal risks, using images via their remote URL is often a safer approach.'),
(365, 1, 'downloads', 'Downloads'),
(366, 1, 'download_button', 'Download Button'),
(367, 1, 'download_csv_example', 'Download CSV Example'),
(368, 1, 'download_csv_template', 'Download CSV Template'),
(369, 1, 'download_images_my_server', 'Download Images to My Server'),
(370, 1, 'drafts', 'Drafts'),
(371, 1, 'dribbble', 'Dribbble'),
(372, 1, 'dropdown', 'Dropdown'),
(373, 1, 'drop_csv_file_here_or_upload', 'Drop CSV file here or click to upload'),
(374, 1, 'drop_files_or_click_upload', 'Drop files here or click to upload'),
(375, 1, 'duration', 'Duration'),
(376, 1, 'earnings', 'Earnings'),
(377, 1, 'earnings_and_stats', 'Earnings & Stats'),
(378, 1, 'earnings_overview', 'Earnings Overview'),
(379, 1, 'earnings_overview_exp', 'Summary of your earnings & stats'),
(380, 1, 'easy', 'Easy'),
(381, 1, 'edit', 'Edit'),
(382, 1, 'edited', 'Edited'),
(383, 1, 'edit_badge', 'Edit Badge'),
(384, 1, 'edit_plan', 'Edit Plan'),
(385, 1, 'edit_profile', 'Edit Profile'),
(386, 1, 'edit_profile_exp', 'Update your personal information, avatar, and how you appear to the community.'),
(387, 1, 'edit_role', 'Edit Role'),
(388, 1, 'edit_translations', 'Edit Translations'),
(389, 1, 'email', 'Email'),
(390, 1, 'emails_sent_successfully', 'All emails have been sent successfully!'),
(391, 1, 'email_address', 'Email Address'),
(392, 1, 'email_blacklist', 'Email Blacklist'),
(393, 1, 'email_content_purchase_body', 'Thank you for your purchase! You now have lifetime access to your premium content. Dive in and enjoy it anytime.'),
(394, 1, 'email_content_purchase_btn', 'Access Content'),
(395, 1, 'email_content_purchase_subject', 'Your Purchase is Ready'),
(396, 1, 'email_content_purchase_title', 'Purchase Successful'),
(397, 1, 'email_expiring_soon_body', 'Your subscription is set to expire soon. Renew now to continue enjoying premium features without interruption.'),
(398, 1, 'email_expiring_soon_btn', 'Renew Subscription'),
(399, 1, 'email_expiring_soon_subject', 'Your Premium Subscription is Expiring Soon'),
(400, 1, 'email_expiring_soon_title', 'Expiring Soon'),
(401, 1, 'email_expiring_today_body', 'Your premium access ends today. Renew now to continue enjoying all premium benefits without interruption.'),
(402, 1, 'email_expiring_today_btn', 'Renew Now'),
(403, 1, 'email_expiring_today_subject', 'Your Subscription Expires Today'),
(404, 1, 'email_expiring_today_title', 'Expiring Today'),
(405, 1, 'email_new_subscription_body', 'Thanks for subscribing! Your subscription has been successfully activated. You now have full access to all premium features and exclusive content.'),
(406, 1, 'email_new_subscription_btn', 'Start Exploring'),
(407, 1, 'email_new_subscription_subject', 'Your Premium Subscription is Now Active'),
(408, 1, 'email_new_subscription_title', 'Welcome to Premium!'),
(409, 1, 'email_not_found_message', 'We could not find an account associated with that email address.'),
(410, 1, 'email_payment_failed_body', 'We were unable to process your payment. Please update your payment method to avoid any interruption to your premium access.'),
(411, 1, 'email_payment_failed_btn', 'Update Payment Method'),
(412, 1, 'email_payment_failed_subject', 'Payment Failed – Action Required'),
(413, 1, 'email_payment_failed_title', 'Payment Failed'),
(414, 1, 'email_settings', 'Email Settings'),
(415, 1, 'email_status', 'Email Status'),
(416, 1, 'email_subscription_cancelled_body', 'Your subscription has been successfully cancelled. You will not be charged again, and you can continue using premium features until the end of your current billing period.'),
(417, 1, 'email_subscription_cancelled_btn', 'Account Settings'),
(418, 1, 'email_subscription_cancelled_subject', 'Your Subscription Has Been Cancelled'),
(419, 1, 'email_subscription_cancelled_title', 'Subscription Cancelled'),
(420, 1, 'email_subscription_expired_body', 'Your premium subscription has expired and your account is now on the standard plan. Upgrade anytime to regain full access to premium features.'),
(421, 1, 'email_subscription_expired_btn', 'Pricing Plans'),
(422, 1, 'email_subscription_expired_subject', 'Your Premium Access Has Ended'),
(423, 1, 'email_subscription_expired_title', 'Subscription Expired'),
(424, 1, 'email_subscription_renewed_body', 'Your payment was successful and your subscription has been renewed. Your premium access continues without interruption.'),
(425, 1, 'email_subscription_renewed_btn', 'View Billing History'),
(426, 1, 'email_subscription_renewed_subject', 'Your Subscription Has Been Renewed'),
(427, 1, 'email_subscription_renewed_title', 'Subscription Renewed'),
(428, 1, 'email_templates', 'Email Templates'),
(429, 1, 'email_verification', 'Email Verification'),
(430, 1, 'email_verification_body1', 'Thanks for signing up! We are excited to have you on board.'),
(431, 1, 'email_verification_body2', 'To get started, please verify your email address. This ensures that we can keep your account secure.'),
(432, 1, 'email_verification_button', 'Verify Email'),
(433, 1, 'email_verification_required', 'Please verify your email address'),
(434, 1, 'email_verification_sent_message', 'A verification email has been sent to your email address. Please follow the instructions in the email to activate your account.'),
(435, 1, 'email_verification_subject', 'Verify your email address'),
(436, 1, 'email_verification_title', 'Verify Your Email'),
(437, 1, 'email_verify_success_message', 'Your email address has been successfully verified. You can now access all the features of our platform.'),
(438, 1, 'email_verify_success_title', 'Email Verified!'),
(439, 1, 'embed_code', 'Embed Code'),
(440, 1, 'embed_media', 'Embed Media'),
(441, 1, 'emoji_reactions', 'Emoji Reactions'),
(442, 1, 'enable', 'Enable'),
(443, 1, 'enabled', 'Enabled'),
(444, 1, 'enable_reward_system', 'Enable Reward System'),
(445, 1, 'encryption', 'Encryption'),
(446, 1, 'end', 'End'),
(447, 1, 'endpoint_url', 'Endpoint URL'),
(448, 1, 'end_date', 'End Date'),
(449, 1, 'enter_2_characters', 'Enter at least 2 characters'),
(450, 1, 'enter_email_address', 'Enter your email address'),
(451, 1, 'enter_url', 'Enter URL'),
(452, 1, 'environment_mode', 'Environment (Mode)'),
(453, 1, 'error', 'Error'),
(454, 1, 'error_gateway_cancellation_failed', 'We couldn\'t reach the payment provider to cancel your subscription right now. Please try again later.'),
(455, 1, 'error_invalid_csv_file', 'Invalid CSV file or processing error. Please check your file.'),
(456, 1, 'event', 'Event'),
(457, 1, 'events', 'Events'),
(458, 1, 'events_calendar', 'Events Calendar'),
(459, 1, 'event_already_registered_email', 'You have already registered for this event with this email address.'),
(460, 1, 'event_details', 'Event Details'),
(461, 1, 'event_exp', 'Scheduled events with location and map details'),
(462, 1, 'event_external_link_label', 'External Link (e.g., Eventbrite, Google Forms)'),
(463, 1, 'event_highlights', 'Event Highlights'),
(464, 1, 'event_highlight_ex', 'Example: Age limit - 15+ required'),
(465, 1, 'event_images', 'Event Images'),
(466, 1, 'event_registration', 'Event Registration'),
(467, 1, 'event_registration_and_tickets', 'Event Registration & Tickets'),
(468, 1, 'event_registration_exp', 'Please fill out the form below to secure your spot.'),
(469, 1, 'event_schedule', 'Event Schedule'),
(470, 1, 'event_schedule_ex', 'Example: 09:00-09:30 - Opening & Registration - Opening speech'),
(471, 1, 'event_you_are_registered', 'You are registered!'),
(472, 1, 'example', 'Example'),
(473, 1, 'exclude_slider_posts', 'Exclude Slider Posts'),
(474, 1, 'exclude_slider_posts_exp', 'Enable this option to prevent posts that are already in the slider from appearing in the featured posts area.'),
(475, 1, 'exclusive', 'Exclusive'),
(476, 1, 'exclusive_category', 'Exclusive Category'),
(477, 1, 'exclusive_category_exp', 'If enabled, all posts under this category will require a separate one-time purchase.'),
(478, 1, 'exclusive_content', 'Exclusive Content'),
(479, 1, 'exclusive_content_exp', 'Requires a separate one-time purchase.'),
(480, 1, 'expired', 'Expired'),
(481, 1, 'expired_on', 'Expired On'),
(482, 1, 'expires_on', 'Expires On'),
(483, 1, 'explore_subscription_plans', 'Explore Subscription Plans'),
(484, 1, 'export', 'Export'),
(485, 1, 'external_image_url', 'External Image URL'),
(486, 1, 'facebook', 'Facebook'),
(487, 1, 'fade_out_effect', 'Fade Out Effect'),
(488, 1, 'fade_out_effect_exp', 'Shows the beginning of the text clearly, then smoothly fades out towards the bottom.'),
(489, 1, 'featured', 'Featured'),
(490, 1, 'featured_content_settings', 'Featured Content Settings'),
(491, 1, 'featured_order', 'Featured Order'),
(492, 1, 'featured_posts', 'Featured Posts'),
(493, 1, 'feature_detail', 'Feature detail'),
(494, 1, 'February', 'Feb'),
(495, 1, 'feed', 'Feed'),
(496, 1, 'feed_link_generator', 'Feed Link Generator'),
(497, 1, 'feed_name', 'Feed Name'),
(498, 1, 'feed_post_limit', 'Feed Post Limit'),
(499, 1, 'feed_post_limit_exp', 'The number of posts to be shown in the RSS feed. (Default: 50, Max: 100)'),
(500, 1, 'feed_url', 'Feed URL'),
(501, 1, 'field', 'Field'),
(502, 1, 'field_label', 'Field Label'),
(503, 1, 'files', 'Files'),
(504, 1, 'files_exp', 'Downloadable additional files (.pdf, .docx, .zip etc..)'),
(505, 1, 'file_extensions', 'File Extensions'),
(506, 1, 'file_manager', 'File Manager'),
(507, 1, 'file_name', 'File Name'),
(508, 1, 'file_upload', 'File Upload'),
(509, 1, 'fill_all_required_fields', 'Please fill in all required fields.'),
(510, 1, 'filter', 'Filter'),
(511, 1, 'filter_options', 'Filter Options'),
(512, 1, 'folder_name', 'Folder Name'),
(513, 1, 'follow', 'Follow'),
(514, 1, 'followers', 'Followers'),
(515, 1, 'following', 'Following'),
(516, 1, 'fonts', 'Fonts'),
(517, 1, 'font_family', 'Font Family'),
(518, 1, 'font_file', 'Font File'),
(519, 1, 'font_settings', 'Font Settings'),
(520, 1, 'font_size', 'Font Size'),
(521, 1, 'font_source', 'Font Source'),
(522, 1, 'font_type', 'Font Type'),
(523, 1, 'footer', 'Footer'),
(524, 1, 'footer_about_section', 'Footer About Section'),
(525, 1, 'forgot_password', 'Forgot Password'),
(526, 1, 'form_validation_alpha_dash', 'The {field} field may only contain alphanumeric characters, underscores, and dashes.'),
(527, 1, 'form_validation_alpha_numeric_space', 'The {field} field may only contain alphanumeric characters and spaces.'),
(528, 1, 'form_validation_is_unique', 'This {field} is already in use. Please try another.'),
(529, 1, 'form_validation_matches', 'The {field} field does not match the {param} field.'),
(530, 1, 'form_validation_max_length', 'The {field} field cannot exceed {param} characters in length.'),
(531, 1, 'form_validation_min_length', 'The {field} field must be at least {param} characters in length.'),
(532, 1, 'form_validation_required', 'The {field} field is required.'),
(533, 1, 'form_validation_valid_email', 'The {field} field must contain a valid email address.'),
(534, 1, 'free', 'Free'),
(535, 1, 'frequency', 'Frequency'),
(536, 1, 'frequency_exp', 'This value indicates how frequently the content at a particular URL is likely to change'),
(537, 1, 'frequently_asked_questions', 'Frequently Asked Questions'),
(538, 1, 'friday', 'Friday'),
(539, 1, 'full_name', 'Full Name'),
(540, 1, 'full_width_post', 'Full-Width Post'),
(541, 1, 'funny', 'Funny'),
(542, 1, 'gallery', 'Gallery'),
(543, 1, 'gallery_albums', 'Gallery Albums'),
(544, 1, 'gallery_categories', 'Gallery Categories'),
(545, 1, 'gallery_items', 'Gallery Items'),
(546, 1, 'gallery_post', 'Gallery Post'),
(547, 1, 'gallery_post_exp', 'A collection of images'),
(548, 1, 'gateway_transaction_id', 'Gateway Transaction ID'),
(549, 1, 'general', 'General'),
(550, 1, 'generate', 'Generate'),
(551, 1, 'generated_feed_url', 'Generated Feed URL'),
(552, 1, 'generated_sitemaps', 'Generated Sitemaps'),
(553, 1, 'generated_text', 'Generated Text'),
(554, 1, 'generate_content', 'Generate Content'),
(555, 1, 'generate_feed_url', 'Generate Feed URL'),
(556, 1, 'generate_keywords_from_title', 'Generate Keywords from Title'),
(557, 1, 'generate_new_token_warning', 'Generating a new token will invalidate the existing one. Continue?'),
(558, 1, 'generate_sitemap', 'Generate Sitemap'),
(559, 1, 'generate_with_ai', 'Generate with AI'),
(560, 1, 'generating_content_dots', 'Generating Content...'),
(561, 1, 'generation_type', 'Generation Type'),
(562, 1, 'generation_type_1', 'Complete Article (Title, SEO & Content)'),
(563, 1, 'generation_type_2', 'Content Only (Body Text)'),
(564, 1, 'generation_type_3', 'Content and Title'),
(565, 1, 'get_video', 'Get Video'),
(566, 1, 'get_video_from_url', 'Get Video from URL'),
(567, 1, 'github', 'Github'),
(568, 1, 'gitlab', 'GitLab'),
(569, 1, 'global_settings', 'Global Settings'),
(570, 1, 'google', 'Google'),
(571, 1, 'google_analytics', 'Google Analytics'),
(572, 1, 'google_analytics_code', 'Google Analytics Code'),
(573, 1, 'google_fonts', 'Google Fonts'),
(574, 1, 'google_maps', 'Google Maps'),
(575, 1, 'google_maps_api_key', 'Google Maps API Key'),
(576, 1, 'google_news', 'Google News'),
(577, 1, 'google_news_cache_exp', 'This system uses cache system. So the records in your XML file will be automatically updated every 15 minutes.'),
(578, 1, 'google_news_exp', 'According to Google News rules, there can be a maximum of 1000 publications in an XML file. Therefore, it is not recommended to increase this limit.'),
(579, 1, 'google_news_publication_name', 'Google News Publication Name'),
(580, 1, 'google_news_rss_content_exp', 'Determines how your news appears inside the Google News App. '),
(581, 1, 'google_play', 'Google Play'),
(582, 1, 'google_recaptcha', 'Google reCAPTCHA'),
(583, 1, 'go_back_to_content', 'Go Back to Content'),
(584, 1, 'go_to_homepage', 'Go to the Homepage'),
(585, 1, 'guest', 'Guest'),
(586, 1, 'hard_paywall', 'Hard Paywall'),
(587, 1, 'hard_paywall_exp', 'Hides the content completely immediately after the post title and image.'),
(588, 1, 'height', 'Height'),
(589, 1, 'help_documents', 'Help Documents'),
(590, 1, 'help_documents_exp', 'You can use these documents to generate your CSV file'),
(591, 1, 'hide', 'Hide'),
(592, 1, 'highlights', 'Highlights'),
(593, 1, 'hit', 'Hit'),
(594, 1, 'home', 'Home'),
(595, 1, 'homepage', 'Homepage'),
(596, 1, 'home_page_link', 'Home Page Link'),
(597, 1, 'home_title', 'Home Title'),
(598, 1, 'horizontal', 'Horizontal'),
(599, 1, 'hour', 'hour'),
(600, 1, 'hourly', 'Hourly'),
(601, 1, 'hours', 'hours'),
(602, 1, 'human_verification', 'Human Verification'),
(603, 1, 'human_verification_exp', 'Validate user activity through mouse movements, scrolling, and time spent on the page to ensure genuine interaction and prevent bots.'),
(604, 1, 'iban', 'IBAN'),
(605, 1, 'iban_long', 'International Bank Account Number'),
(606, 1, 'icon', 'Icon'),
(607, 1, 'id', 'ID'),
(608, 1, 'image', 'Image'),
(609, 1, 'images', 'Images'),
(610, 1, 'image_description', 'Image Description'),
(611, 1, 'image_file_format', 'Image File Format'),
(612, 1, 'image_for_video', 'Image for video'),
(613, 1, 'import', 'Import'),
(614, 1, 'importing_posts', 'Importing posts...'),
(615, 1, 'import_completed', 'Import completed!'),
(616, 1, 'import_completed_skipped_rows', 'Import completed. Skipped rows due to errors:'),
(617, 1, 'import_language', 'Import Language'),
(618, 1, 'import_posts', 'Import Posts'),
(619, 1, 'inactive', 'Inactive'),
(620, 1, 'index', 'Index'),
(621, 1, 'info', 'Info'),
(622, 1, 'ingredient', 'Ingredient'),
(623, 1, 'ingredients', 'Ingredients'),
(624, 1, 'ingredient_ex', 'Example: 1 tablespoon olive oil'),
(625, 1, 'instagram', 'Instagram'),
(626, 1, 'insufficient_balance', 'Insufficient balance!'),
(627, 1, 'integration_endpoints', 'Integration Endpoints'),
(628, 1, 'integration_endpoints_exp', 'The following links are generated for you to submit to Google services.'),
(629, 1, 'intermediate', 'Intermediate'),
(630, 1, 'internal_registration', 'Internal Registration'),
(631, 1, 'invalid', 'Invalid!'),
(632, 1, 'invalid_attempt', 'Invalid attempt!'),
(633, 1, 'invalid_feed_url', 'Invalid feed URL!'),
(634, 1, 'invalid_file_type', 'Invalid file type!'),
(635, 1, 'invalid_url', 'Invalid URL!'),
(636, 1, 'invalid_withdrawal_amount', 'Invalid withdrawal amount!'),
(637, 1, 'invoice', 'Invoice'),
(638, 1, 'invoice_footer_note', 'Invoice Footer Note'),
(639, 1, 'invoice_prefix', 'Invoice Prefix'),
(640, 1, 'ip_address', 'IP Address'),
(641, 1, 'item_description', 'Item Description'),
(642, 1, 'item_id', 'Item ID'),
(643, 1, 'item_order', 'Item Order'),
(644, 1, 'item_type', 'Item Type'),
(645, 1, 'iyzico', 'Iyzico'),
(646, 1, 'January', 'Jan'),
(647, 1, 'join', 'Join'),
(648, 1, 'join_newsletter', 'Join Our Newsletter'),
(649, 1, 'join_this_event', 'Join This Event'),
(650, 1, 'join_this_event_exp', 'Secure your spot before tickets run out.'),
(651, 1, 'join_to_newsletter', 'Join to our newsletter to stay updated.'),
(652, 1, 'json_language_file', 'JSON Language File'),
(653, 1, 'July', 'Jul'),
(654, 1, 'June', 'Jun'),
(655, 1, 'just_now', 'Just Now'),
(656, 1, 'keep_original_file_format', 'Keep Original File Format'),
(657, 1, 'keywords', 'Keywords'),
(658, 1, 'key_id', 'Key ID'),
(659, 1, 'label', 'Label'),
(660, 1, 'language', 'Language'),
(661, 1, 'languages', 'Languages'),
(662, 1, 'language_code', 'Language Code'),
(663, 1, 'language_name', 'Language Name'),
(664, 1, 'language_settings', 'Language Settings'),
(665, 1, 'last_comments', 'Latest Comments'),
(666, 1, 'last_contact_messages', 'Latest Contact Messages'),
(667, 1, 'last_modification', 'Last Modification'),
(668, 1, 'last_modification_exp', 'The time the URL was last modified'),
(669, 1, 'last_seen_user', 'Last seen'),
(670, 1, 'latest_posts', 'Latest Posts'),
(671, 1, 'latest_transactions', 'Latest Transactions'),
(672, 1, 'latest_transactions_exp', 'Overview of recent financial activities'),
(673, 1, 'latest_users', 'Latest Users'),
(674, 1, 'latitude', 'Latitude'),
(675, 1, 'layout_special', 'Layout & Special'),
(676, 1, 'leave_message', 'Send a Message'),
(677, 1, 'left', 'Left'),
(678, 1, 'left_to_right', 'Left to Right'),
(679, 1, 'length_of_text', 'Length of Text'),
(680, 1, 'level_1', 'Level 1'),
(681, 1, 'level_2', 'Level 2'),
(682, 1, 'level_3', 'Level 3'),
(683, 1, 'lifetime', 'Lifetime'),
(684, 1, 'lifetime_access', 'Lifetime Access'),
(685, 1, 'lifetime_plan', 'Lifetime plan'),
(686, 1, 'light', 'Light'),
(687, 1, 'like', 'Like'),
(688, 1, 'limit', 'Limit'),
(689, 1, 'link', 'Link'),
(690, 1, 'linkedin', 'Linkedin'),
(691, 1, 'link_list_style', 'Link List Style'),
(692, 1, 'link_type', 'Link Type'),
(693, 1, 'list', 'List'),
(694, 1, 'list_items', 'List Items'),
(695, 1, 'loading', 'Loading...'),
(696, 1, 'load_map', 'Load Map'),
(697, 1, 'load_more', 'Load More'),
(698, 1, 'load_more_comments', 'Load More Comments'),
(699, 1, 'local', 'Local'),
(700, 1, 'localized_settings', 'Localized Settings'),
(701, 1, 'local_storage', 'Local Storage'),
(702, 1, 'location', 'Location'),
(703, 1, 'location_map_hidden', 'Location Map Hidden'),
(704, 1, 'location_map_hidden_exp', 'To protect your privacy, Google Maps is hidden. By loading the map, you consent to sharing your IP address and agree to the Google Privacy Policy:'),
(705, 1, 'lockout_time', 'Lockout Time'),
(706, 1, 'lockout_time_exp', 'Wait time after exceeding login limits.'),
(707, 1, 'login_admin_exp', 'Your central hub for managing news and magazine content, all in one place.'),
(708, 1, 'login_security', 'Login Security'),
(709, 1, 'logo', 'Logo'),
(710, 1, 'logo_email', 'Logo Email'),
(711, 1, 'logo_footer', 'Logo Footer'),
(712, 1, 'logo_size', 'Logo Size'),
(713, 1, 'log_in', 'Log In'),
(714, 1, 'log_in_admin_exp', 'Please log in to access the admin panel'),
(715, 1, 'log_in_error', 'Wrong username or password!'),
(716, 1, 'log_in_exp', 'Welcome back! Please enter your details.'),
(717, 1, 'log_out', 'Log Out'),
(718, 1, 'long', 'Long'),
(719, 1, 'longitude', 'Longitude'),
(720, 1, 'love', 'Love'),
(721, 1, 'mail', 'Mail'),
(722, 1, 'mail_host', 'Mail Host'),
(723, 1, 'mail_password', 'Mail Password'),
(724, 1, 'mail_port', 'Mail Port'),
(725, 1, 'mail_protocol', 'Mail Protocol'),
(726, 1, 'mail_service', 'Mail Service'),
(727, 1, 'mail_title', 'Mail Title'),
(728, 1, 'mail_username', 'Mail Username'),
(729, 1, 'main', 'Main'),
(730, 1, 'maintenance_mode', 'Maintenance Mode'),
(731, 1, 'main_image', 'Main Image'),
(732, 1, 'main_images', 'Main Images'),
(733, 1, 'main_menu', 'Main Menu'),
(734, 1, 'main_slider', 'Main Slider'),
(735, 1, 'manage_all_posts', 'Manage All Posts'),
(736, 1, 'manage_my_subscription', 'Manage My Subscription'),
(737, 1, 'manage_subscription', 'Manage Subscription'),
(738, 1, 'manage_subscription_exp', 'Control your premium membership, manage renewals, or adjust your current plan.'),
(739, 1, 'manual_selection_only', 'Manual Selection Only'),
(740, 1, 'map_drag_maker_exp', 'Drag the marker or enter coordinates to update.'),
(741, 1, 'March', 'Mar'),
(742, 1, 'mastodon', 'Mastodon'),
(743, 1, 'max', 'Max'),
(744, 1, 'max_audio_file_size', 'Maximum Audio File Size'),
(745, 1, 'max_file_size', 'Maximum File Size'),
(746, 1, 'max_image_file_size', 'Maximum Image File Size'),
(747, 1, 'max_login_attempts', 'Max Login Attempts'),
(748, 1, 'max_login_attempts_exp', 'Lock account after this many failed attempts.'),
(749, 1, 'max_posts_per_feed', 'Max Posts per Feed'),
(750, 1, 'max_posts_per_feed_exp', 'Number of posts to display in an RSS feed.'),
(751, 1, 'max_video_file_size', 'Maximum Video File Size'),
(752, 1, 'May', 'May'),
(753, 1, 'measurement_id', 'Measurement ID'),
(754, 1, 'media', 'Media'),
(755, 1, 'medium', 'Medium'),
(756, 1, 'mega_menu_color', 'Mega Menu Color'),
(757, 1, 'member', 'Member'),
(758, 1, 'membership', 'Membership'),
(759, 1, 'membership_plan_details', 'Membership Plan Details'),
(760, 1, 'membership_plan_details_exp', 'Configure pricing, duration, and user perks.'),
(761, 1, 'member_since', 'Member since'),
(762, 1, 'menu_limit', 'Menu Limit '),
(763, 1, 'mercado_pago', 'Mercado Pago'),
(764, 1, 'message', 'Message'),
(765, 1, 'message_ban_error', 'Your account has been banned!'),
(766, 1, 'message_change_password_error', 'There was a problem changing your password!'),
(767, 1, 'message_change_password_success', 'Your password has been successfully changed!'),
(768, 1, 'message_contact_error', 'There was a problem sending your message!'),
(769, 1, 'message_contact_success', 'Your message has been successfully sent!'),
(770, 1, 'message_invalid_email', 'Invalid email address!'),
(771, 1, 'message_page_auth', 'You must be logged in to view this page!'),
(772, 1, 'message_post_auth', 'You must be logged in to view this post!'),
(773, 1, 'message_profile_success', 'Your profile has been successfully updated!'),
(774, 1, 'meta_description', 'Meta Description'),
(775, 1, 'meta_keywords', 'Meta Keywords'),
(776, 1, 'meta_keywords_exp', 'Enter keywords separated by commas (e.g., technology, ai, future)'),
(777, 1, 'meta_options', 'Meta Options'),
(778, 1, 'meta_tag', 'Meta Tag'),
(779, 1, 'meta_title', 'Meta Title'),
(780, 1, 'min', 'Min'),
(781, 1, 'minute', 'minute'),
(782, 1, 'minutes', 'minutes'),
(783, 1, 'minute_short', 'min'),
(784, 1, 'min_mouse_movements', 'Minimum Mouse Movements'),
(785, 1, 'min_password_length', 'Min Password Length'),
(786, 1, 'min_password_length_exp', 'Minimum characters required for new passwords.'),
(787, 1, 'min_poyout_amount', 'Minimum payout amount'),
(788, 1, 'min_poyout_amounts', 'Minimum Payout Amounts'),
(789, 1, 'min_scroll_movements', 'Minimum Scroll Movements'),
(790, 1, 'min_time_spent_on_page', 'Minimum Time Spent on the Page (Seconds)'),
(791, 1, 'model', 'Model'),
(792, 1, 'monday', 'Monday'),
(793, 1, 'monetization', 'Monetization'),
(794, 1, 'month', 'month'),
(795, 1, 'monthly', 'Monthly'),
(796, 1, 'months', 'months'),
(797, 1, 'more', 'More'),
(798, 1, 'more_info', 'More info'),
(799, 1, 'more_main_images', 'More main images (slider will be active)'),
(800, 1, 'most_popular', 'Most Popular'),
(801, 1, 'most_viewed_posts', 'Most Viewed Posts'),
(802, 1, 'msg_added', 'Item successfully added!'),
(803, 1, 'msg_beforeunload', 'You have unsaved changes! Are you sure you want to leave this page?'),
(804, 1, 'msg_bot_verification_failed', 'Bot verification failed. Please try again.'),
(805, 1, 'msg_bulk_approve', 'Are you sure you want to approve the selected items?'),
(806, 1, 'msg_bulk_delete', 'Are you sure you want to delete the selected items?'),
(807, 1, 'msg_cancel_email_sending', 'Are you sure you want to cancel the sending process?'),
(808, 1, 'msg_comment_sent_successfully', 'Your comment has been sent. It will be published after being reviewed by the site management.'),
(809, 1, 'msg_content_generated', 'Content generated successfully!'),
(810, 1, 'msg_cron_feed', 'With this URL you can automatically update your feeds.'),
(811, 1, 'msg_cron_sitemap', 'With this URL you can automatically update your sitemap.'),
(812, 1, 'msg_deleted', 'Item successfully deleted!'),
(813, 1, 'msg_delete_album', 'Please delete categories belonging to this album first!'),
(814, 1, 'msg_delete_images', 'Please delete images belonging to this category first!'),
(815, 1, 'msg_delete_posts', 'Please delete posts belonging to this category first!'),
(816, 1, 'msg_delete_subcategories', 'Please delete subcategories belonging to this category first!'),
(817, 1, 'msg_delete_subpages', 'Please delete subpages/sublinks first!'),
(818, 1, 'msg_email_sent', 'Email successfully sent!'),
(819, 1, 'msg_error', 'An error occurred please try again!'),
(820, 1, 'msg_error_email_blacklisted', 'Your request could not be processed at this time. Please try again later.'),
(821, 1, 'msg_language_delete', 'Default language cannot be deleted!'),
(822, 1, 'msg_newsletter_error', 'Your email address is already registered!'),
(823, 1, 'msg_newsletter_remove', 'We’ve successfully removed your email from our mailing list. You will no longer receive our news, updates, or special offers. We’re sad to see you go, but you can join us again whenever you like!'),
(824, 1, 'msg_newsletter_success', 'You have successfully joined!'),
(825, 1, 'msg_not_authorized', 'You are not authorized to perform this operation!'),
(826, 1, 'msg_no_purchase_yet', 'You haven\'t made a purchase yet.'),
(827, 1, 'msg_page_delete', 'Default pages cannot be deleted!'),
(828, 1, 'msg_payout_added', 'Payout has been successfully added!'),
(829, 1, 'msg_request_sent', 'The request has been sent successfully!'),
(830, 1, 'msg_reset_cache', 'All cache files have been deleted!'),
(831, 1, 'msg_rss_warning', 'If you chose to download the images to your server, adding posts will take more time and will use more resources. If you see any problems, increase \'max_execution_time\' and \'memory_limit\' values from your server settings.'),
(832, 1, 'msg_select_category', 'Please select a category.'),
(833, 1, 'msg_select_csv_file', 'Please select a CSV file.'),
(834, 1, 'msg_select_date', 'Please select a date.'),
(835, 1, 'msg_sign_up_success', 'Your account has been successfully created!'),
(836, 1, 'msg_sign_up_success_email_verify', 'Your account has been successfully created! Please check your email to verify your account.'),
(837, 1, 'msg_sitemap_cron_job', 'By creating a cron job on your server, you can automatically update your sitemap using this URL'),
(838, 1, 'msg_site_under_construction', 'Site under construction! Please try again later.'),
(839, 1, 'msg_slug_used', 'The slug you entered is being used by another user!'),
(840, 1, 'msg_tag_exists', 'This tag already exists!'),
(841, 1, 'msg_topic_empty', 'Please enter a topic to generate content.'),
(842, 1, 'msg_updated', 'Changes successfully saved!'),
(843, 1, 'msg_upload_file_size_error', 'The selected file exceeds the allowed file size. Allowed size:'),
(844, 1, 'msg_user_added', 'User successfully added!'),
(845, 1, 'msg_widget_delete', 'Default widgets cannot be deleted!'),
(846, 1, 'msg_wrong_password', 'Wrong Password!'),
(847, 1, 'multilingual_system', 'Multilingual System'),
(848, 1, 'musician', 'Musician'),
(849, 1, 'must_agree_to_continue', 'You must agree to continue.'),
(850, 1, 'my_earnings', 'My Earnings'),
(851, 1, 'name', 'Name'),
(852, 1, 'navigation', 'Navigation'),
(853, 1, 'navigation_exp', 'You can manage the navigation by dragging and dropping menu items.'),
(854, 1, 'nav_drag_warning', 'You cannot drag a category below a page or a page below a category link!'),
(855, 1, 'never', 'Never'),
(856, 1, 'never_expires', 'Never expires'),
(857, 1, 'newest_first', 'Newest First'),
(858, 1, 'newsletter', 'Newsletter'),
(859, 1, 'newsletter_email_error', 'Select email addresses that you want to send mail!'),
(860, 1, 'newsletter_join_desc', 'Get the latest news and curated updates straight to your inbox. Sign up for our newsletter.'),
(861, 1, 'newsletter_popup', 'Newsletter Popup'),
(862, 1, 'newsletter_remove_successful', 'You’ve left our mailing list.'),
(863, 1, 'news_sitemap_for_seo', 'News Sitemap (for SEO & Bots)'),
(864, 1, 'news_sitemap_for_seo_exp', 'This file includes only articles from the last 48 hours. Copy this URL and submit it once via Google Search Console > Sitemaps.'),
(865, 1, 'new_password', 'New Password'),
(866, 1, 'new_payout_request', 'New Payout Request'),
(867, 1, 'new_question', 'New Question'),
(868, 1, 'next', 'Next'),
(869, 1, 'next_article', 'Next Article'),
(870, 1, 'next_payment', 'Next Payment'),
(871, 1, 'next_video', 'Next Video'),
(872, 1, 'no', 'No'),
(873, 1, 'none', 'None'),
(874, 1, 'November', 'Nov'),
(875, 1, 'no_action_allow', 'No Action (Allow)'),
(876, 1, 'no_active_subscription', 'No Active Subscription'),
(877, 1, 'no_active_subscription_exp', 'You are not currently subscribed to any premium plan. Upgrade your account today to unlock exclusive features, remove limitations, and enjoy our premium services.'),
(878, 1, 'no_description', 'No description'),
(879, 1, 'no_files_found', 'No files found'),
(880, 1, 'no_files_found_exp', 'Upload your first file to get started.'),
(881, 1, 'no_records_found', 'No records found.'),
(882, 1, 'no_registration_required', 'No Registration Required'),
(883, 1, 'no_results_found', 'No results found.'),
(884, 1, 'number', 'Number'),
(885, 1, 'number_of_correct_answers', 'Number of Correct Answers'),
(886, 1, 'number_of_correct_answers_range', 'The range of correct answers to show this result'),
(887, 1, 'number_of_days', 'Number of Days'),
(888, 1, 'number_of_days_exp', 'If you add 30 here, the system will delete posts older than 30 days'),
(889, 1, 'number_of_images', 'Number of Images'),
(890, 1, 'number_of_links_in_menu', 'The number of links that appear in the menu'),
(891, 1, 'number_of_posts', 'Number of Posts'),
(892, 1, 'number_of_posts_import', 'Number of Posts to Import'),
(893, 1, 'number_short_billion', 'b'),
(894, 1, 'number_short_million', 'm'),
(895, 1, 'number_short_thousand', 'k'),
(896, 1, 'nutritional_ex', 'Example: Protein 34g'),
(897, 1, 'nutritional_information', 'Nutritional Information\r\n'),
(898, 1, 'occupancy_rate', 'Occupancy Rate'),
(899, 1, 'October', 'Oct'),
(900, 1, 'ok', 'OK'),
(901, 1, 'oldest_first', 'Oldest First'),
(902, 1, 'old_password', 'Old Password'),
(903, 1, 'one_time_payment', 'One time payment'),
(904, 1, 'online', 'online'),
(905, 1, 'only_csv_files_allowed', 'Only .csv files are allowed'),
(906, 1, 'only_registered', 'Only Registered'),
(907, 1, 'operation_completed', 'Operation completed successfully.'),
(908, 1, 'option', 'Option'),
(909, 1, 'optional', 'Optional'),
(910, 1, 'optional_url', 'Optional URL'),
(911, 1, 'optional_url_exp', 'External link for a button on the post page.');
INSERT INTO `language_translations` (`id`, `lang_id`, `label`, `translation`) VALUES
(912, 1, 'optional_url_name', 'Post Optional URL Button Name'),
(913, 1, 'options', 'Options'),
(914, 1, 'or', 'or'),
(915, 1, 'order', 'Menu Order'),
(916, 1, 'order_1', 'Order'),
(917, 1, 'order_id', 'Order ID'),
(918, 1, 'order_summary', 'Order Summary'),
(919, 1, 'order_token', 'Order Token'),
(920, 1, 'organizer', 'Organizer'),
(921, 1, 'original', 'Original'),
(922, 1, 'or_log_in_with_email', 'Or log in with email'),
(923, 1, 'or_sign_up_with_email', 'Or sign up with email'),
(924, 1, 'page', 'Page'),
(925, 1, 'pages', 'Pages'),
(926, 1, 'pageviews', 'Pageviews'),
(927, 1, 'page_not_found', 'Page not found'),
(928, 1, 'page_not_found_sub', 'The page you are looking for doesn\'t exist.'),
(929, 1, 'page_settings', 'Page Settings'),
(930, 1, 'page_type', 'Page Type'),
(931, 1, 'pagination_per_page', 'Pagination (Number of posts per page)'),
(932, 1, 'paid', 'Paid'),
(933, 1, 'panel', 'Panel'),
(934, 1, 'paragraph', 'Paragraph'),
(935, 1, 'parent_category', 'Parent Category'),
(936, 1, 'parent_link', 'Parent Link'),
(937, 1, 'participants', 'Participants'),
(938, 1, 'password', 'Password'),
(939, 1, 'password_complexity_special_error', 'Password must contain at least one number and one special character.'),
(940, 1, 'password_reset_body', 'To set a new password, please click the button below. For your security, this link will expire in 2 hours.'),
(941, 1, 'password_reset_button', 'Reset Password'),
(942, 1, 'password_reset_description', 'Create your new password'),
(943, 1, 'password_reset_sent_message', 'A password reset link has been sent to your email address. Please follow the instructions in the email to reset your password.'),
(944, 1, 'password_reset_subject', 'Reset your password'),
(945, 1, 'password_reset_success', 'Your password has been successfully updated. You can now log in with your new credentials.'),
(946, 1, 'password_reset_title', 'Reset Password'),
(947, 1, 'password_security', 'Password Security'),
(948, 1, 'paste_ad_code', 'Ad Code'),
(949, 1, 'paste_ad_url', 'Ad URL'),
(950, 1, 'patreon', 'Patreon'),
(951, 1, 'payment', 'Payment'),
(952, 1, 'payment_details', 'Payment Details'),
(953, 1, 'payment_history', 'Payment History'),
(954, 1, 'payment_history_exp', 'Review your past transactions, track your spending, and download your invoices.'),
(955, 1, 'payment_method', 'Payment Method'),
(956, 1, 'payment_method_exp', 'Select your preferred payment provider. You will finalize the payment in the next step.'),
(957, 1, 'payment_option_load_error', 'The selected payment method is currently unavailable. Please choose a different method or try again later.'),
(958, 1, 'payment_redirect_exp', 'You will be redirected securely to complete the payment.'),
(959, 1, 'payment_settings', 'Payment Settings'),
(960, 1, 'payment_successful', 'Payment Successful!'),
(961, 1, 'payouts', 'Payouts'),
(962, 1, 'payout_method', 'Payout Method'),
(963, 1, 'payout_methods', 'Payout Methods'),
(964, 1, 'paypal', 'PayPal'),
(965, 1, 'paypal_email_address', 'PayPal Email Address'),
(966, 1, 'paytabs', 'Paytabs'),
(967, 1, 'paywall_ad_free', 'Ad-free, uninterrupted browsing experience'),
(968, 1, 'paywall_already_member_log_in', 'Already a member? Log in'),
(969, 1, 'paywall_already_purchased_log_in', 'Already purchased? Log in'),
(970, 1, 'paywall_appearance', 'Paywall Appearance'),
(971, 1, 'paywall_elevate_your_experience', 'Elevate Your Experience'),
(972, 1, 'paywall_exclusive_content', 'Exclusive Content'),
(973, 1, 'paywall_exclusive_desc', 'This is exclusive content. Purchase access to unlock full access and explore the complete experience.'),
(974, 1, 'paywall_interactive_features', 'Interactive features and community access'),
(975, 1, 'paywall_lifetime_access', 'Lifetime access to this specific content'),
(976, 1, 'paywall_one_time_payment', 'One-time payment, no subscription required'),
(977, 1, 'paywall_premium_desc', 'This is premium content. Upgrade your account to unlock full access and explore the complete experience.'),
(978, 1, 'paywall_premium_subscription', 'Premium Subscription'),
(979, 1, 'paywall_subscribe_now', 'Subscribe Now'),
(980, 1, 'paywall_unlimited_access_to_contents', 'Unlimited access to all premium contents'),
(981, 1, 'paywall_unlock_access', 'Unlock Access'),
(982, 1, 'paywall_unlock_full_content_premium', 'Unlock the Full Content with Premium'),
(983, 1, 'paywall_unlock_this_exclusive_content', 'Unlock This Exclusive Content'),
(984, 1, 'pay_per_content', 'Pay-per-content'),
(985, 1, 'pending', 'Pending'),
(986, 1, 'pending_comments', 'Pending Comments'),
(987, 1, 'pending_comments_exp', 'Comments awaiting administrative approval.'),
(988, 1, 'pending_payouts', 'Pending Payouts'),
(989, 1, 'pending_posts', 'Pending Posts'),
(990, 1, 'pending_posts_exp', 'Posts awaiting administrative approval.'),
(991, 1, 'permalink', 'Permalink'),
(992, 1, 'permissions', 'Permissions'),
(993, 1, 'permission_denied', 'Permission denied. Your account doesn’t have the required privileges!'),
(994, 1, 'personality_quiz', 'Personality Quiz'),
(995, 1, 'personality_quiz_exp', 'Quizzes with custom results'),
(996, 1, 'personal_website_url', 'Personal Website URL'),
(997, 1, 'phone', 'Phone'),
(998, 1, 'photo_gallery', 'Photo Gallery'),
(999, 1, 'photo_gallery_exp', 'Explore our complete collection of visual stories, capturing moments of elegance, style, and raw emotion.'),
(1000, 1, 'pinterest', 'Pinterest'),
(1001, 1, 'placeholder_search', 'Search...'),
(1002, 1, 'plans', 'Plans'),
(1003, 1, 'plan_features', 'Plan Features'),
(1004, 1, 'plan_name', 'Plan Name'),
(1005, 1, 'play_again', 'Play Again'),
(1006, 1, 'play_list_empty', 'Playlist is empty.'),
(1007, 1, 'please_select_option', 'Please select an option!'),
(1008, 1, 'png_format', 'PNG Format'),
(1009, 1, 'png_logo_format_exp', 'PNG logo version required for email clients and social media sharing.'),
(1010, 1, 'poll', 'Poll'),
(1011, 1, 'polls', 'Polls'),
(1012, 1, 'poll_exp', 'Get user opinions about something'),
(1013, 1, 'popular', 'Popular'),
(1014, 1, 'popular_posts', 'Popular Posts'),
(1015, 1, 'popular_posts_limit', 'Popular Posts Limit'),
(1016, 1, 'post', 'Post'),
(1017, 1, 'postcode', 'Postcode'),
(1018, 1, 'posts', 'Posts'),
(1019, 1, 'post_comment', 'Post Comment'),
(1020, 1, 'post_content', 'Post Content'),
(1021, 1, 'post_details', 'Post Details'),
(1022, 1, 'post_format', 'Post Format'),
(1023, 1, 'post_formats', 'Post Formats'),
(1024, 1, 'post_options', 'Post Options'),
(1025, 1, 'post_owner', 'Post Owner'),
(1026, 1, 'post_reply', 'Post Reply'),
(1027, 1, 'post_tags', 'Tags:'),
(1028, 1, 'post_url_structure', 'Post URL Structure'),
(1029, 1, 'post_url_structure_exp', 'Changing the URL structure will not affect old records.'),
(1030, 1, 'post_url_structure_slug', 'Use Slug in URLs'),
(1031, 1, 'post_url_structur_id', 'Use ID Numbers in URLs'),
(1032, 1, 'preferences', 'Preferences'),
(1033, 1, 'premium', 'Premium'),
(1034, 1, 'premium_category', 'Premium Category'),
(1035, 1, 'premium_category_exp', 'If enabled, all posts under this category will require a premium membership to be viewed.'),
(1036, 1, 'premium_content', 'Premium Content'),
(1037, 1, 'premium_content_exp', 'Restrict access to premium members only.'),
(1038, 1, 'premium_content_mode', 'Premium Content Mode'),
(1039, 1, 'premium_membership', 'Premium Membership'),
(1040, 1, 'premium_users', 'Premium Users'),
(1041, 1, 'prep_time', 'Prep Time'),
(1042, 1, 'preview', 'Preview'),
(1043, 1, 'previous', 'Previous'),
(1044, 1, 'previous_article', 'Previous Article'),
(1045, 1, 'previous_video', 'Previous Video'),
(1046, 1, 'price', 'Price'),
(1047, 1, 'primary_font', 'Primary Font (Main)'),
(1048, 1, 'print', 'Print'),
(1049, 1, 'priority', 'Priority'),
(1050, 1, 'priority_exp', 'The priority of a particular URL relative to other pages on the same site'),
(1051, 1, 'privacy_policy_url', 'Privacy Policy URL'),
(1052, 1, 'privacy_preference_center', 'Privacy Preference Center'),
(1053, 1, 'privacy_preference_center_exp', 'Manage your consent preferences.'),
(1054, 1, 'proceed_to_payment', 'Proceed to Payment'),
(1055, 1, 'proceed_to_payment_exp', 'You can verify your order and taxes on the next page before any payment is processed.'),
(1056, 1, 'processing', 'Processing...'),
(1057, 1, 'production', 'Production'),
(1058, 1, 'profile', 'Profile'),
(1059, 1, 'profile_id', 'Profile ID'),
(1060, 1, 'profile_url', 'Profile URL'),
(1061, 1, 'progressive_web_app', 'Progressive Web App (PWA)'),
(1062, 1, 'publication_date', 'Publication Date'),
(1063, 1, 'public_interaction_content', 'Public Interaction Content (Comments, user profiles)'),
(1064, 1, 'public_key', 'Public Key'),
(1065, 1, 'public_open_to_everyone', 'Public (Open to everyone)'),
(1066, 1, 'public_url', 'Public URL'),
(1067, 1, 'publish', 'Publish'),
(1068, 1, 'publishable_key', 'Publishable Key'),
(1069, 1, 'publisher_center_feed_app', 'Publisher Center Feed (for App)'),
(1070, 1, 'publisher_center_feed_app_exp', 'This is your primary feed containing all latest news. Use this URL in Google Publisher Center. To create separate sections (e.g., specific Categories or Languages), please use the Feed Link Generator tool above.'),
(1071, 1, 'publish_directly', 'Publish Directly'),
(1072, 1, 'publish_status', 'Publish Status'),
(1073, 1, 'purchased', 'Purchased'),
(1074, 1, 'purchased_content', 'Purchased Content'),
(1075, 1, 'purchased_content_exp', 'Access and explore all the premium content you have purchased.'),
(1076, 1, 'pwa_logo', 'PWA Logo'),
(1077, 1, 'question', 'Question'),
(1078, 1, 'questions', 'Questions'),
(1079, 1, 'quiz_images', 'Quiz Images'),
(1080, 1, 'quora', 'Quora'),
(1081, 1, 'randomly', 'Randomly'),
(1082, 1, 'random_posts', 'Random Posts'),
(1083, 1, 'razorpay', 'Razorpay'),
(1084, 1, 'reading_list', 'Reading List'),
(1085, 1, 'reading_list_feature', 'Reading List Feature'),
(1086, 1, 'read_more_button_text', 'Read More Button Text'),
(1087, 1, 'recently_added_comments', 'Recently added comments'),
(1088, 1, 'recently_added_contact_messages', 'Recently added contact messages'),
(1089, 1, 'recently_added_unapproved_comments', 'Recently added unapproved comments'),
(1090, 1, 'recently_registered_users', 'Recently registered users'),
(1091, 1, 'recipe', 'Recipe'),
(1092, 1, 'recipe_exp', 'A list of ingredients and directions for cooking'),
(1093, 1, 'recipe_images', 'Recipe Images'),
(1094, 1, 'recipe_video', 'Recipe video'),
(1095, 1, 'recipe_video_title', 'Watch How to Make It'),
(1096, 1, 'recipient', 'Recipient'),
(1097, 1, 'recipients', 'Recipients'),
(1098, 1, 'recommended', 'Recommended'),
(1099, 1, 'recommended_posts', 'Recommended Posts'),
(1100, 1, 'reddit', 'Reddit'),
(1101, 1, 'redirect_rss_posts_to_original', 'Redirect RSS Posts to the Original Site'),
(1102, 1, 'redirect_url', 'Redirect URL'),
(1103, 1, 'reference', 'Reference'),
(1104, 1, 'refresh', 'Refresh'),
(1105, 1, 'refresh_cache_database_changes', 'Refresh Cache Files When the Database Changes'),
(1106, 1, 'regenerate', 'Regenerate'),
(1107, 1, 'region', 'Region'),
(1108, 1, 'registered_emails', 'Registered Emails'),
(1109, 1, 'registered_users_can_vote', 'Only Registered Users Can Vote'),
(1110, 1, 'registered_users_only', 'Registered Users Only'),
(1111, 1, 'register_now', 'Register Now'),
(1112, 1, 'registration_closed', 'Registration Closed'),
(1113, 1, 'registration_deadline', 'Registration Deadline'),
(1114, 1, 'registration_rate', 'Registration Date'),
(1115, 1, 'registration_rules', 'Registration Rules'),
(1116, 1, 'registration_successful', 'Your registration has been successfully completed!'),
(1117, 1, 'registration_system', 'Registration System'),
(1118, 1, 'registration_type', 'Registration Type'),
(1119, 1, 'reject', 'Reject'),
(1120, 1, 'related_posts', 'Related Posts'),
(1121, 1, 'related_posts_limit', 'Related Posts Limit'),
(1122, 1, 'related_videos', 'Related Videos'),
(1123, 1, 'remove_ban', 'Remove Ban'),
(1124, 1, 'remove_breaking_news', 'Remove from Breaking News'),
(1125, 1, 'remove_featured', 'Remove from Featured'),
(1126, 1, 'remove_links_keep_text', 'Remove Links (Keep Text)'),
(1127, 1, 'remove_recommended', 'Remove from Recommended'),
(1128, 1, 'remove_slider', 'Remove from Slider'),
(1129, 1, 'reply', 'Reply'),
(1130, 1, 'reply_to', 'Reply-To'),
(1131, 1, 'required', 'Required'),
(1132, 1, 'require_admin_approval_edited_posts', 'Require Admin Approval for Edited Posts'),
(1133, 1, 'require_admin_approval_new_posts', 'Require Admin Approval for New Posts'),
(1134, 1, 'require_numbers_special_characters', 'Require numbers and special characters'),
(1135, 1, 'require_numbers_special_characters_exp', 'Users must use at least one number and special character if enabled.'),
(1136, 1, 'reset', 'Reset'),
(1137, 1, 'reset_cache', 'Reset Cache'),
(1138, 1, 'result', 'Result'),
(1139, 1, 'results', 'Results'),
(1140, 1, 'reward_amount', 'Reward Amount for 1000 Pageviews'),
(1141, 1, 'reward_system', 'Reward System'),
(1142, 1, 'right', 'Right'),
(1143, 1, 'right_to_left', 'Right to Left'),
(1144, 1, 'role', 'Role'),
(1145, 1, 'roles', 'Roles'),
(1146, 1, 'roles_permissions', 'Roles & Permissions'),
(1147, 1, 'role_name', 'Role Name'),
(1148, 1, 'routes', 'Routes'),
(1149, 1, 'route_settings_warning', 'You cannot use special characters in routes. If your language contains special characters, please be careful when editing routes. If you enter an invalid route, you will not be able to access the related page.'),
(1150, 1, 'rss', 'RSS'),
(1151, 1, 'rss_content', 'RSS Content'),
(1152, 1, 'rss_feeds', 'RSS Feeds'),
(1153, 1, 'sad', 'Sad'),
(1154, 1, 'sandbox', 'Sandbox'),
(1155, 1, 'saturday', 'Saturday'),
(1156, 1, 'save', 'Save'),
(1157, 1, 'save_changes', 'Save Changes'),
(1158, 1, 'save_draft', 'Save as Draft'),
(1159, 1, 'save_preferences', 'Save Preferences'),
(1160, 1, 'scheduled', 'Scheduled'),
(1161, 1, 'scheduled_post', 'Scheduled Post'),
(1162, 1, 'scheduled_posts', 'Scheduled Posts'),
(1163, 1, 'search', 'Search'),
(1164, 1, 'searching', 'Searching...'),
(1165, 1, 'search_in_post_content', 'Search in Post Content'),
(1166, 1, 'secondary_font', 'Secondary Font (Titles)'),
(1167, 1, 'secret_key', 'Secret Key'),
(1168, 1, 'section_title', 'Section Title'),
(1169, 1, 'secure_checkout', 'Secure Checkout'),
(1170, 1, 'secure_key', 'Secure Key'),
(1171, 1, 'security', 'Security'),
(1172, 1, 'security_check', 'Security Check'),
(1173, 1, 'security_check_exp', 'Please complete the captcha to verify you are human.'),
(1174, 1, 'select', 'Select'),
(1175, 1, 'selected_content_only', 'Selected Content Only'),
(1176, 1, 'selected_content_only_exp', 'Apply premium rules only to specifically selected categories or individual posts.'),
(1177, 1, 'selected_file_s', 'Selected File(s):'),
(1178, 1, 'select_ad_spaces', 'Select Ad Space'),
(1179, 1, 'select_an_option', 'Select an option'),
(1180, 1, 'select_at_least_one_recipient', 'Please select at least one recipient to send the email.'),
(1181, 1, 'select_audio', 'Select Audio'),
(1182, 1, 'select_a_result', 'Select a result'),
(1183, 1, 'select_category', 'Select a category'),
(1184, 1, 'select_date', 'Select date'),
(1185, 1, 'select_date_range', 'Select date range'),
(1186, 1, 'select_file', 'Select File'),
(1187, 1, 'select_image', 'Select Image'),
(1188, 1, 'select_multiple_images', 'You can select multiple images.'),
(1189, 1, 'select_plan', 'Select Plan'),
(1190, 1, 'select_video', 'Select Video'),
(1191, 1, 'select_your_subscription_plan', 'Select Your Subscription Plan'),
(1192, 1, 'select_your_subscription_plan_exp', 'Gain full access to all premium content. Choose the plan that suits you best to enjoy an uninterrupted, ad-free reading experience across all your devices.'),
(1193, 1, 'sender_email_address', 'Sender Email Address'),
(1194, 1, 'sending', 'Sending'),
(1195, 1, 'send_contact_to_mail', 'Send Contact Messages to Email Address'),
(1196, 1, 'send_email', 'Send Email'),
(1197, 1, 'send_email_registered', 'Send Email to Registered Emails'),
(1198, 1, 'send_test_email', 'Send Test Email'),
(1199, 1, 'send_test_email_exp', 'You can send a test mail to check if your mail server is working.'),
(1200, 1, 'send_verification_email', 'Send Verification Email'),
(1201, 1, 'seo_options', 'Seo options'),
(1202, 1, 'seo_tools', 'SEO Tools'),
(1203, 1, 'September', 'Sep'),
(1204, 1, 'serving', 'Serving'),
(1205, 1, 'settings', 'Settings'),
(1206, 1, 'settings_language', 'Settings Language'),
(1207, 1, 'set_as_album_cover', 'Set as Album Cover'),
(1208, 1, 'set_as_default', 'Set as Default'),
(1209, 1, 'set_payout_account', 'Set Payout Account'),
(1210, 1, 'share', 'Share'),
(1211, 1, 'shared', 'Shared'),
(1212, 1, 'short', 'Short'),
(1213, 1, 'short_form', 'Short Form'),
(1214, 1, 'show', 'Show'),
(1215, 1, 'show_all_files', 'Show all Files'),
(1216, 1, 'show_breadcrumb', 'Show Breadcrumb'),
(1217, 1, 'show_email_on_profile', 'Show Email on Profile Page'),
(1218, 1, 'show_featured_section', 'Show Featured Section'),
(1219, 1, 'show_full_content', 'Show Full Content'),
(1220, 1, 'show_images_from_original_source', 'Show Images from Original Source'),
(1221, 1, 'show_item_numbers', 'Show Item Numbers in Post Details Page'),
(1222, 1, 'show_latest_posts_homepage', 'Show Latest Posts on Homepage'),
(1223, 1, 'show_list_style_post_text', 'Show List Style in Post Text'),
(1224, 1, 'show_only_own_files', 'Show Only Users Own Files'),
(1225, 1, 'show_only_registered', 'Show Only to Registered Users'),
(1226, 1, 'show_on_homepage', 'Show on Homepage'),
(1227, 1, 'show_on_menu', 'Show on Menu'),
(1228, 1, 'show_post_author', 'Show Post Author'),
(1229, 1, 'show_post_dates', 'Show Post Date'),
(1230, 1, 'show_post_view_counts', 'Show Post View Count'),
(1231, 1, 'show_read_more_button', 'Show Read More Button'),
(1232, 1, 'show_sidebar', 'Show Sidebar'),
(1233, 1, 'show_summary_only', 'Show Summary Only'),
(1234, 1, 'show_title', 'Show Title'),
(1235, 1, 'show_user_email_profile', 'Show User\'s Email on Profile'),
(1236, 1, 'sidebar', 'Sidebar'),
(1237, 1, 'sign_up', 'Sign Up'),
(1238, 1, 'single_content_sales', 'Single Content Sales'),
(1239, 1, 'single_content_sales_exp', 'Allows you to sell specific posts individually, even to users who are not subscribed.'),
(1240, 1, 'sitemap', 'Sitemap'),
(1241, 1, 'sitemap_generate_exp', 'If your site has more than 49,000 links, the sitemap.xml file will be created in parts.'),
(1242, 1, 'site_color', 'Site Color'),
(1243, 1, 'site_description', 'Site Description'),
(1244, 1, 'site_font', 'Site Font'),
(1245, 1, 'site_key', 'Site Key'),
(1246, 1, 'site_title', 'Site Title'),
(1247, 1, 'slider', 'Slider'),
(1248, 1, 'slider_order', 'Slider Order'),
(1249, 1, 'slider_posts', 'Slider Posts'),
(1250, 1, 'slug', 'Slug'),
(1251, 1, 'slug_exp', 'If you leave it blank, it will be generated automatically.'),
(1252, 1, 'smtp', 'SMTP'),
(1253, 1, 'snapchat', 'Snapchat'),
(1254, 1, 'social_accounts', 'Social Accounts'),
(1255, 1, 'social_accounts_exp', 'Link your social media profiles to display them publicly.'),
(1256, 1, 'social_login', 'Social Login'),
(1257, 1, 'social_media', 'Social Media'),
(1258, 1, 'sorted_list', 'Sorted List'),
(1259, 1, 'sorted_list_exp', 'A list based article'),
(1260, 1, 'sorting_logic', 'Sorting Logic'),
(1261, 1, 'sort_order', 'Sort Order'),
(1262, 1, 'soundcloud', 'SoundCloud'),
(1263, 1, 'space_separator', 'Space'),
(1264, 1, 'spam_protection', 'Spam Protection'),
(1265, 1, 'spam_protection_exp', 'Manage how external links are processed to protect your SEO score and prevent spam.'),
(1266, 1, 'speakers_guests', 'Speakers & Guests'),
(1267, 1, 'start', 'Start'),
(1268, 1, 'start_date', 'Start Date'),
(1269, 1, 'start_exploring', 'Start Exploring'),
(1270, 1, 'start_import', 'Start Import'),
(1271, 1, 'state', 'State'),
(1272, 1, 'status', 'Status'),
(1273, 1, 'stay_updated', 'Stay Updated'),
(1274, 1, 'steam', 'Steam'),
(1275, 1, 'sticky_sidebar', 'Sticky Sidebar'),
(1276, 1, 'storage', 'Storage'),
(1277, 1, 'strictly_necessary', 'Strictly Necessary'),
(1278, 1, 'strictly_necessary_exp', 'Required for security and basic functions. Cannot be disabled.'),
(1279, 1, 'strict_paywall', 'Strict Paywall'),
(1280, 1, 'strict_paywall_exp', 'Blocks access to the post page completely. Not recommended for SEO.'),
(1281, 1, 'stripe', 'Stripe'),
(1282, 1, 'style', 'Style'),
(1283, 1, 'subcategory', 'Subcategory'),
(1284, 1, 'subject', 'Subject'),
(1285, 1, 'submit', 'Submit'),
(1286, 1, 'submitting', 'Submitting...'),
(1287, 1, 'subscribe', 'Subscribe'),
(1288, 1, 'subscribers', 'Subscribers'),
(1289, 1, 'subscribe_button', 'Subscribe Button'),
(1290, 1, 'subscription', 'Subscription'),
(1291, 1, 'subscription_already_subscribed', 'You are already subscribed to this plan.'),
(1292, 1, 'subscription_badges', 'Subscription Badges'),
(1293, 1, 'subscription_button_text_change', 'The button text can be changed in the language settings section by translating the \"btn_subscribe\" key.'),
(1294, 1, 'subscription_cancelled_successfully', 'Your subscription has been successfully cancelled. You will continue to have access to all premium features until the end of your current billing period.'),
(1295, 1, 'subscription_free_plan_activated', 'Great! Your free plan has been successfully activated. You can view your subscription details below and start exploring the platform right away.'),
(1296, 1, 'subscription_free_plan_used', 'Free plan used'),
(1297, 1, 'subscription_plan', 'Subscription Plan'),
(1298, 1, 'subscription_plans', 'Subscription Plans'),
(1299, 1, 'subscription_system_status_exp', 'Turn on to enable subscription plans and paywalls across your website.'),
(1300, 1, 'subtotal', 'Subtotal'),
(1301, 1, 'succeeded', 'Succeeded'),
(1302, 1, 'success', 'Success'),
(1303, 1, 'success_message', 'Success Message'),
(1304, 1, 'success_message_exp', 'Message shown after submit'),
(1305, 1, 'summary', 'Summary'),
(1306, 1, 'sunday', 'Sunday'),
(1307, 1, 'swift', 'SWIFT'),
(1308, 1, 'swift_code', 'SWIFT Code'),
(1309, 1, 'swift_iban', 'Bank Account Number/IBAN'),
(1310, 1, 'symbol_direction', 'Symbol Direction'),
(1311, 1, 'system', 'System'),
(1312, 1, 'system_tools', 'System Tools'),
(1313, 1, 'table_of_contents', 'Table of Contents'),
(1314, 1, 'table_of_contents_exp', 'List of links based on the headings'),
(1315, 1, 'table_of_contents_items', 'Table Of Contents Items'),
(1316, 1, 'tag', 'Tag'),
(1317, 1, 'tags', 'Tags'),
(1318, 1, 'tax', 'Tax'),
(1319, 1, 'tax_configuration', 'Tax Configuration'),
(1320, 1, 'tax_configuration_exp', 'Manage global VAT and country-specific overrides.'),
(1321, 1, 'tax_vat_number', 'Tax / VAT Number'),
(1322, 1, 'telegram', 'Telegram'),
(1323, 1, 'terms_conditions', 'Terms & Conditions'),
(1324, 1, 'terms_conditions_exp', 'I have read and agree to the'),
(1325, 1, 'test_api', 'Test API'),
(1326, 1, 'text_direction', 'Text Direction'),
(1327, 1, 'text_editor_language', 'Text Editor Language'),
(1328, 1, 'text_input', 'Text Input'),
(1329, 1, 'text_list_empty', 'Your reading list is empty.'),
(1330, 1, 'themes', 'Themes'),
(1331, 1, 'theme_color', 'Theme Color'),
(1332, 1, 'theme_settings', 'Theme Settings'),
(1333, 1, 'this_month', 'This Month'),
(1334, 1, 'this_week', 'This Week'),
(1335, 1, 'thousand_separator', 'Thousand Separator'),
(1336, 1, 'threads', 'Threads'),
(1337, 1, 'thursday', 'Thursday'),
(1338, 1, 'tiktok', 'Tiktok'),
(1339, 1, 'time', 'Time'),
(1340, 1, 'timezone', 'Timezone'),
(1341, 1, 'title', 'Title'),
(1342, 1, 'titles', 'Titles'),
(1343, 1, 'title_multiline_error', 'Please correct the following errors:'),
(1344, 1, 'to', 'To:'),
(1345, 1, 'today', 'Today'),
(1346, 1, 'token', 'Token'),
(1347, 1, 'tone_academic', 'Academic'),
(1348, 1, 'tone_casual', 'Casual'),
(1349, 1, 'tone_critical', 'Critical'),
(1350, 1, 'tone_formal', 'Formal'),
(1351, 1, 'tone_humorous', 'Humorous'),
(1352, 1, 'tone_inspirational', 'Inspirational'),
(1353, 1, 'tone_neutral', 'Neutral'),
(1354, 1, 'tone_persuasive', 'Persuasive'),
(1355, 1, 'tone_professional', 'Professional'),
(1356, 1, 'tone_style', 'Tone/Style'),
(1357, 1, 'too_many_login_attempts', 'Too many login attempts. Please try again after the wait time.'),
(1358, 1, 'topic', 'Topic'),
(1359, 1, 'topic_ai_exp', 'Briefly describe what you want the article to be about.'),
(1360, 1, 'topic_ai_placeholder', 'e.g. Benefits of drinking green tea...'),
(1361, 1, 'top_headlines', 'Top Headlines'),
(1362, 1, 'top_menu', 'Top Menu'),
(1363, 1, 'total', 'Total'),
(1364, 1, 'total_earnings', 'Total Earnings'),
(1365, 1, 'total_pageviews', 'Total Pageviews'),
(1366, 1, 'total_paid', 'Total Paid'),
(1367, 1, 'total_payouts', 'Total Payouts'),
(1368, 1, 'total_registered_users', 'Total registered users'),
(1369, 1, 'total_submitted_posts', 'Total Submitted Posts'),
(1370, 1, 'total_vote', 'Total Vote:'),
(1371, 1, 'total_votes', 'Total Votes'),
(1372, 1, 'transactions', 'Transactions'),
(1373, 1, 'transaction_details', 'Transaction Details'),
(1374, 1, 'transaction_fee', 'Transaction Fee'),
(1375, 1, 'transaction_fee_rate', 'Transaction Fee Rate'),
(1376, 1, 'transaction_id', 'Transaction ID'),
(1377, 1, 'transaction_type', 'Transaction Type'),
(1378, 1, 'translation', 'Translation'),
(1379, 1, 'trending_posts', 'Trending Posts'),
(1380, 1, 'trivia_quiz', 'Trivia Quiz'),
(1381, 1, 'trivia_quiz_exp', 'Quizzes with right and wrong answers'),
(1382, 1, 'tuesday', 'Tuesday'),
(1383, 1, 'tumblr', 'Tumblr'),
(1384, 1, 'twitch', 'Twitch'),
(1385, 1, 'type', 'Type'),
(1386, 1, 'type_location_name', 'Type a location name...'),
(1387, 1, 'type_tag', 'Type tag and hit enter'),
(1388, 1, 'unfollow', 'Unfollow'),
(1389, 1, 'unique_pageviews', 'Unique Pageviews'),
(1390, 1, 'unsubscribe', 'Unsubscribe'),
(1391, 1, 'unverified', 'Unverified'),
(1392, 1, 'upcoming_events', 'Upcoming Events'),
(1393, 1, 'upcoming_events_exp', 'Overview of the upcoming schedule'),
(1394, 1, 'update', 'Update'),
(1395, 1, 'updated', 'Updated'),
(1396, 1, 'update_album', 'Update Album'),
(1397, 1, 'update_article', 'Update Article'),
(1398, 1, 'update_audio', 'Update Audio'),
(1399, 1, 'update_category', 'Update Category'),
(1400, 1, 'update_event', 'Update Event'),
(1401, 1, 'update_feed', 'Update Feed'),
(1402, 1, 'update_font', 'Update Font'),
(1403, 1, 'update_gallery', 'Update Gallery'),
(1404, 1, 'update_image', 'Update Image'),
(1405, 1, 'update_language', 'Update Language'),
(1406, 1, 'update_link', 'Update Menu Link'),
(1407, 1, 'update_page', 'Update Page'),
(1408, 1, 'update_personality_quiz', 'Update Personality Quiz'),
(1409, 1, 'update_poll', 'Update Poll'),
(1410, 1, 'update_post', 'Update Post'),
(1411, 1, 'update_profile', 'Update Profile'),
(1412, 1, 'update_recipe', 'Update Recipe'),
(1413, 1, 'update_sorted_list', 'Update Sorted List'),
(1414, 1, 'update_subcategory', 'Update Subcategory'),
(1415, 1, 'update_table_of_contents', 'Update Table of Contents'),
(1416, 1, 'update_trivia_quiz', 'Update Trivia Quiz'),
(1417, 1, 'update_video', 'Update Video'),
(1418, 1, 'update_widget', 'Update Widget'),
(1419, 1, 'upload', 'Upload'),
(1420, 1, 'uploading', 'Uploading...'),
(1421, 1, 'upload_csv_file', 'Upload CSV File'),
(1422, 1, 'upload_image', 'Upload Image'),
(1423, 1, 'upload_up_to_10_files', 'Upload up to 10 files'),
(1424, 1, 'upload_video', 'Upload Video'),
(1425, 1, 'upload_your_banner', 'Create Ad Code'),
(1426, 1, 'url', 'URL'),
(1427, 1, 'user', 'User'),
(1428, 1, 'username', 'Username'),
(1429, 1, 'users', 'Users'),
(1430, 1, 'users_and_permissions', 'Users & Permissions'),
(1431, 1, 'users_permissions', 'Users & Permissions'),
(1432, 1, 'user_agent', 'User-Agent'),
(1433, 1, 'user_agreement', 'User Agreement'),
(1434, 1, 'user_details', 'User Details'),
(1435, 1, 'user_id', 'User ID'),
(1436, 1, 'user_profile_badge', 'User Profile Badge'),
(1437, 1, 'user_profile_options', 'User Profile Options'),
(1438, 1, 'user_profile_options_exp', 'Select platforms allowed for users.'),
(1439, 1, 'use_content', 'Use Content'),
(1440, 1, 'value', 'Value'),
(1441, 1, 'vat', 'VAT'),
(1442, 1, 'venue_name', 'Venue name'),
(1443, 1, 'verified', 'Verified'),
(1444, 1, 'verify_post_comment', 'Verify & Post Comment'),
(1445, 1, 'version', 'Version'),
(1446, 1, 'vertical', 'Vertical'),
(1447, 1, 'very_long', 'Very Long'),
(1448, 1, 'very_short', 'Very Short'),
(1449, 1, 'via_url', 'Via URL'),
(1450, 1, 'video', 'Video'),
(1451, 1, 'videos', 'Videos'),
(1452, 1, 'video_embed_code', 'Video Embed Code'),
(1453, 1, 'video_file', 'Video File'),
(1454, 1, 'video_name', 'Video Name'),
(1455, 1, 'video_post_exp', 'Upload or embed videos'),
(1456, 1, 'video_thumbnails', 'Video Thumbnail'),
(1457, 1, 'video_url', 'Video URL'),
(1458, 1, 'view', 'View'),
(1459, 1, 'view_all', 'View All'),
(1460, 1, 'view_all_posts', 'View All Posts'),
(1461, 1, 'view_content', 'View Content'),
(1462, 1, 'view_details', 'View Details'),
(1463, 1, 'view_google_privacy_policy', 'View Google Privacy Policy'),
(1464, 1, 'view_more_replies', 'View more replies'),
(1465, 1, 'view_my_purchases', 'View My Purchases'),
(1466, 1, 'view_options', 'View Options'),
(1467, 1, 'view_post', 'View Post'),
(1468, 1, 'view_results', 'View Results'),
(1469, 1, 'view_site', 'View Site'),
(1470, 1, 'vimeo', 'Vimeo'),
(1471, 1, 'visibility', 'Visibility'),
(1472, 1, 'visit_hash', 'Visit Hash'),
(1473, 1, 'vk', 'VKontakte'),
(1474, 1, 'vote', 'Vote'),
(1475, 1, 'voted_message', 'You already voted this poll before.'),
(1476, 1, 'vote_permission', 'Vote Permission'),
(1477, 1, 'wait_time', 'Wait time'),
(1478, 1, 'warning', 'Warning'),
(1479, 1, 'warning_documentation', 'Read the documentation before enabling this option'),
(1480, 1, 'warning_edit_profile_image', 'Click on the save changes button after selecting your image'),
(1481, 1, 'warning_email_button_click', 'Having trouble clicking the button? Copy and paste the URL below into your web browser:'),
(1482, 1, 'warning_invalid_email_request', 'If you didn\'t make this request, you can safely ignore this email. The link will expire in 24 hours.'),
(1483, 1, 'webhook_secret_id', 'Webhook Secret/ID'),
(1484, 1, 'website', 'Website'),
(1485, 1, 'website_social_links', 'Website Social Links'),
(1486, 1, 'website_social_links_exp', 'Enter URL to activate. Leave empty to disable.'),
(1487, 1, 'wednesday', 'Wednesday'),
(1488, 1, 'week', 'Week'),
(1489, 1, 'weekly', 'Weekly'),
(1490, 1, 'welcome', 'Welcome'),
(1491, 1, 'welcome_exp_mobile', 'Unlock your personalized experience.'),
(1492, 1, 'whatsapp', 'WhatsApp'),
(1493, 1, 'whats_your_reaction', 'What\'s Your Reaction?'),
(1494, 1, 'where_to_display', 'Where To Display'),
(1495, 1, 'where_to_display_exp', 'Only categories with Block Style 2, 3, or 4 can be selected. Other categories will not be shown as options.'),
(1496, 1, 'widget', 'Widget'),
(1497, 1, 'widgets', 'Widgets'),
(1498, 1, 'width', 'Width'),
(1499, 1, 'withdraw_amount', 'Withdrawal Amount'),
(1500, 1, 'withdraw_method', 'Withdrawal Method'),
(1501, 1, 'wow', 'Wow'),
(1502, 1, 'write_a_comment', 'Write a comment...'),
(1503, 1, 'write_a_reply', 'Write a reply...'),
(1504, 1, 'wrong_answer', 'Wrong Answer'),
(1505, 1, 'wrong_password_error', 'Wrong old password!'),
(1506, 1, 'x_twitter', 'X (Twitter)'),
(1507, 1, 'year', 'year'),
(1508, 1, 'yearly', 'Yearly'),
(1509, 1, 'years', 'years'),
(1510, 1, 'yes', 'Yes'),
(1511, 1, 'yesterday', 'Yesterday'),
(1512, 1, 'you', 'You'),
(1513, 1, 'your_balance', 'Your Balance'),
(1514, 1, 'your_subscription_currently_active', 'Your subscription is currently active.'),
(1515, 1, 'youtube', 'YouTube'),
(1516, 1, 'you_dont_have_active_subscription', 'You don\'t have an active subscription at the moment.'),
(1517, 1, 'zip_postal_code', 'Zip / Postal Code');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attempts` int DEFAULT '1',
  `last_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `media_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT NULL,
  `is_downloadable` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `item_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `item_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'monthly',
  `payment_method_id` int DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `tax` decimal(15,2) DEFAULT NULL,
  `transaction_fee_rate` decimal(5,2) DEFAULT NULL,
  `transaction_fee` decimal(15,2) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `billing_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `billing_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_zip_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billing_tax_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `gateway_plan_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT '1',
  `page_default_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `page_content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `page_order` smallint DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  `title_active` tinyint(1) DEFAULT '1',
  `breadcrumb_active` tinyint(1) DEFAULT '1',
  `right_column_active` tinyint(1) DEFAULT '1',
  `need_auth` tinyint(1) DEFAULT '0',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'top',
  `link` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent_id` int DEFAULT '0',
  `page_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'page',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `lang_id`, `title`, `slug`, `meta_title`, `description`, `keywords`, `is_custom`, `page_default_name`, `page_content`, `page_order`, `status`, `title_active`, `breadcrumb_active`, `right_column_active`, `need_auth`, `location`, `link`, `parent_id`, `page_type`, `updated_at`, `created_at`) VALUES
(1, 1, 'Contact', 'contact', 'Contact', 'Contact Page', 'contact, page', 0, 'contact', NULL, 1, 1, 1, 1, 0, 0, 'top', NULL, 0, 'page', NULL, '2026-04-14 21:33:39'),
(2, 1, 'Gallery', 'gallery', 'Gallery', 'Gallery Page', 'gallery, page', 0, 'gallery', NULL, 1, 1, 1, 1, 0, 0, 'main', NULL, 0, 'page', NULL, '2026-04-14 21:34:59'),
(3, 1, 'Terms & Conditions', 'terms-conditions', 'Terms & Conditions', 'Terms & Conditions Page', 'terms, conditions', 0, 'terms_conditions', NULL, 1, 1, 1, 1, 0, 0, 'footer', NULL, 0, 'page', NULL, '2026-04-14 21:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `public_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secret_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `webhook_secret` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `environment` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'production',
  `transaction_fee_rate` decimal(5,2) DEFAULT '0.00',
  `base_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `logos` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `name_key`, `public_key`, `secret_key`, `webhook_secret`, `environment`, `transaction_fee_rate`, `base_product_id`, `status`, `logos`) VALUES
(1, 'PayPal', 'paypal', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'paypal,visa,mastercard,amex,discover'),
(2, 'Stripe', 'stripe', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'stripe,visa,mastercard,amex,discover'),
(3, 'Razorpay', 'razorpay', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'razorpay,visa,mastercard,amex,maestro,rupay'),
(4, 'Iyzico', 'iyzico', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'iyzico,visa,mastercard,amex,troy'),
(5, 'Mercado Pago', 'mercado_pago', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'mercado_pago,visa,mastercard,amex,discover,boleto'),
(6, 'PayTabs', 'paytabs', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'paytabs,visa,mastercard,amex,discover');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` double NOT NULL,
  `payout_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` tinyint(1) DEFAULT '1',
  `vote_permission` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poll_options`
--

CREATE TABLE `poll_options` (
  `id` int NOT NULL,
  `poll_id` int DEFAULT NULL,
  `option_text` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `votes` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poll_votes`
--

CREATE TABLE `poll_votes` (
  `id` int NOT NULL,
  `poll_id` int DEFAULT NULL,
  `option_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `guid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `summary` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `category_id` int DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `optional_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pageviews` int DEFAULT '0',
  `comment_count` int DEFAULT '0',
  `need_auth` tinyint(1) DEFAULT '0',
  `slider_order` int DEFAULT '1',
  `featured_order` int DEFAULT '1',
  `visibility` tinyint(1) DEFAULT '1',
  `full_width_post` tinyint(1) DEFAULT '0',
  `post_format` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'article',
  `image_url` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_id` int DEFAULT NULL,
  `video_url` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_embed_code` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `feed_id` int DEFAULT NULL,
  `post_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_post_url` tinyint(1) DEFAULT '1',
  `image_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_item_numbers` tinyint(1) DEFAULT '1',
  `is_poll_public` tinyint(1) DEFAULT '0',
  `link_list_style` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `post_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `extra_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `meta_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `faq` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_scheduled` tinyint(1) DEFAULT '0',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT '0',
  `is_exclusive` tinyint(1) DEFAULT '0',
  `exclusive_price` decimal(15,2) DEFAULT NULL,
  `event_start_date` timestamp NULL DEFAULT NULL,
  `event_end_date` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_additional_images`
--

CREATE TABLE `post_additional_images` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `image_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_items`
--

CREATE TABLE `post_items` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image_id` int DEFAULT NULL,
  `image_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_order` smallint DEFAULT NULL,
  `parent_link_num` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_item_images`
--

CREATE TABLE `post_item_images` (
  `id` int NOT NULL,
  `item_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'quiz',
  `image_default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_extension` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'jpg',
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `media_id` int DEFAULT NULL,
  `selection_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_pageviews_month`
--

CREATE TABLE `post_pageviews_month` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `post_user_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `reward_amount` double NOT NULL DEFAULT '0',
  `visit_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_poll_votes`
--

CREATE TABLE `post_poll_votes` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `answer_id` int DEFAULT NULL,
  `user_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_selections`
--

CREATE TABLE `post_selections` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `selection_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_tags`
--

CREATE TABLE `post_tags` (
  `id` bigint NOT NULL,
  `tag_id` int DEFAULT NULL,
  `post_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int NOT NULL,
  `question_id` int DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `answer_text` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `assigned_result_id` int DEFAULT '0',
  `total_votes` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `question` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `question_order` int DEFAULT '1',
  `answer_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'small_image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `result_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `min_correct_count` mediumint DEFAULT NULL,
  `max_correct_count` mediumint DEFAULT NULL,
  `result_order` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `reaction` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reading_lists`
--

CREATE TABLE `reading_lists` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_default` tinyint(1) DEFAULT '0',
  `is_super_admin` tinyint(1) DEFAULT '0',
  `badge_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `permissions`, `is_default`, `is_super_admin`, `badge_id`) VALUES
(1, '[{\"lang_id\":\"1\",\"name\":\"Super Admin\"}]', '', 1, 1, 0),
(2, '[{\"lang_id\":\"1\",\"name\":\"Author\"}]', 'add_post,admin_panel', 1, 0, 0),
(3, '[{\"lang_id\":\"1\",\"name\":\"Member\"}]', '', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rss_feeds`
--

CREATE TABLE `rss_feeds` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `feed_name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `feed_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `post_limit` smallint DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `image_saving_method` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'url',
  `auto_update` tinyint(1) DEFAULT '1',
  `read_more_button` tinyint(1) DEFAULT '1',
  `read_more_button_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Read More',
  `add_posts_as_draft` tinyint(1) DEFAULT '0',
  `generate_keywords_from_title` tinyint(1) DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `lang_id` int NOT NULL DEFAULT '1',
  `site_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Index',
  `site_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `application_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `primary_font` smallint DEFAULT '13',
  `secondary_font` smallint DEFAULT '14',
  `content_font` smallint DEFAULT '7',
  `font_size` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `social_media_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `site_social_media` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `profile_social_media` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `optional_url_button_name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Click Here To See More',
  `about_footer` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `contact_address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `copyright` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cookies_warning` tinyint(1) DEFAULT '0',
  `cookies_warning_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `lang_id`, `site_title`, `home_title`, `site_description`, `keywords`, `application_name`, `primary_font`, `secondary_font`, `content_font`, `font_size`, `social_media_data`, `site_social_media`, `profile_social_media`, `optional_url_button_name`, `about_footer`, `contact_text`, `contact_address`, `contact_email`, `contact_phone`, `invoice_details`, `copyright`, `cookies_warning`, `cookies_warning_data`) VALUES
(1, 1, NULL, 'Index', NULL, NULL, NULL, 13, 14, 7, '{\"fs_base\":15,\"fs_post-title\":36,\"fs_post-summary\":18,\"fs_content\":17,\"fs_main-nav\":15,\"fs_tiny\":11,\"fs_xs\":12,\"fs_sm\":13,\"fs_md\":14,\"fs_lg\":16,\"fs_xl\":18,\"fs_title-xs\":15,\"fs_title-sm\":16,\"fs_title-md\":18,\"fs_title-lg\":20,\"fs_title-xl\":22,\"fs_title-2xl\":24,\"fs_title-3xl\":26,\"fs_title-4xl\":30,\"fs_title-5xl\":32}', NULL, NULL, NULL, 'Click Here To See More', NULL, '', '', '', '', NULL, 'Copyright 2026 Varient - All Rights Reserved.', 1, '{\"title\":\"We Value Your Privacy\",\"desc\":\"We use strictly necessary cookies to ensure our website functions correctly and securely. With your consent, we also use Google Analytics to analyze traffic and improve your user experience. For more information, please read our\",\"url_title\":\"Privacy Policy\",\"url\":\"\"}');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int NOT NULL,
  `plan_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) DEFAULT '0.00',
  `billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'monthly',
  `sort_order` int DEFAULT '1',
  `is_popular` tinyint(1) DEFAULT '0',
  `is_lifetime` tinyint(1) DEFAULT '0',
  `is_free_plan` tinyint(1) DEFAULT '0',
  `is_ad_free` tinyint(1) DEFAULT '0',
  `badge_id` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `features` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `stripe_price_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tag_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lang_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int NOT NULL,
  `theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `theme_folder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `theme_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `theme_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'light',
  `theme_color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `block_color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mega_menu_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `theme`, `theme_folder`, `theme_name`, `theme_mode`, `theme_color`, `block_color`, `mega_menu_color`, `is_active`) VALUES
(1, 'magazine', 'magazine', 'Magazine', 'light', '#19bc9c', '#18181b', '#ec4040', 1),
(2, 'news', 'magazine', 'News', 'light', '#2d65fe', '#1c1c1c', '#1c1c1c', 0),
(3, 'classic', 'classic', 'Classic', 'light', '#19bc9c', '#18181b', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'succeeded',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''name@domain.com''',
  `email_status` tinyint(1) DEFAULT '0',
  `auth_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role_id` int DEFAULT '3',
  `oauth_provider` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `oauth_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage_avatar` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage_cover` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `social_media_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `about_me` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_email_on_profile` tinyint(1) DEFAULT '0',
  `show_rss_feeds` tinyint(1) DEFAULT '0',
  `reward_system` tinyint(1) DEFAULT '0',
  `balance` double DEFAULT '0',
  `total_pageviews` int DEFAULT '0',
  `payout_methods` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `billing_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `has_used_free_trial` tinyint(1) DEFAULT '0',
  `reset_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_created_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int NOT NULL,
  `badge_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_purchases`
--

CREATE TABLE `user_purchases` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `post_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `gateway_name_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gateway_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `reminder_1_day_sent` tinyint(1) DEFAULT '0',
  `reminder_3_days_sent` tinyint(1) DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `widget_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `widget_order` int DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  `is_custom` tinyint(1) DEFAULT '1',
  `display_category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `widgets`
--

INSERT INTO `widgets` (`id`, `lang_id`, `title`, `content`, `widget_type`, `widget_order`, `status`, `is_custom`, `display_category_id`, `created_at`) VALUES
(1, 1, 'Popular Posts', NULL, 'popular-posts', 1, 1, 0, NULL, '2026-04-14 23:59:36'),
(2, 1, 'Follow Us', NULL, 'follow-us', 2, 1, 0, NULL, '2026-04-14 23:59:36'),
(3, 1, 'Recommended Posts', NULL, 'recommended-posts', 3, 1, 0, NULL, '2026-04-15 00:00:23'),
(4, 1, 'Popular Tags', NULL, 'tags', 4, 1, 0, NULL, '2026-04-15 00:00:23'),
(5, 1, 'Voting Poll', NULL, 'poll', 5, 1, 0, NULL, '2026-04-15 00:00:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ad_spaces`
--
ALTER TABLE `ad_spaces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_path` (`path`),
  ADD KEY `idx_depth` (`depth`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`),
  ADD KEY `idx_id` (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status_id` (`status`,`id`),
  ADD KEY `idx_post_comments` (`post_id`,`status`,`parent_id`,`created_at`,`id`),
  ADD KEY `idx_latest_comments` (`created_at`,`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_blacklist`
--
ALTER TABLE `email_blacklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_following_id` (`following_id`),
  ADD KEY `idx_follower_id` (`follower_id`);

--
-- Indexes for table `fonts`
--
ALTER TABLE `fonts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_album_id` (`album_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `language_translations`
--
ALTER TABLE `language_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lang_id` (`lang_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_media_type` (`media_type`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_token` (`order_token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_item_type` (`item_type`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_gateway_plan_id` (`gateway_plan_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_orders_status_created` (`status`,`created_at`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll_options`
--
ALTER TABLE `poll_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_poll_id` (`poll_id`);

--
-- Indexes for table `poll_votes`
--
ALTER TABLE `poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_poll_vote` (`user_id`,`poll_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_is_scheduled` (`is_scheduled`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_post_format` (`post_format`),
  ADD KEY `idx_feed_id` (`feed_id`),
  ADD KEY `idx_image_id` (`image_id`),
  ADD KEY `idx_guid` (`guid`),
  ADD KEY `idx_is_premium` (`is_premium`),
  ADD KEY `idx_is_exclusive` (`is_exclusive`),
  ADD KEY `idx_latest_posts` (`visibility`,`status`,`is_scheduled`,`created_at`),
  ADD KEY `idx_latest_language_posts` (`lang_id`,`visibility`,`status`,`is_scheduled`,`created_at`),
  ADD KEY `idx_category_posts` (`lang_id`,`visibility`,`status`,`is_scheduled`,`created_at`,`category_id`),
  ADD KEY `idx_author_posts` (`user_id`,`lang_id`,`visibility`,`status`,`is_scheduled`,`created_at`),
  ADD KEY `idx_author_panel_posts` (`user_id`,`visibility`,`status`,`created_at`),
  ADD KEY `idx_event_dates` (`event_start_date`,`event_end_date`);
ALTER TABLE `posts` ADD FULLTEXT KEY `idx_fulltext` (`title`,`summary`,`content`);

--
-- Indexes for table `post_additional_images`
--
ALTER TABLE `post_additional_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_images` (`post_id`,`image_id`);

--
-- Indexes for table `post_items`
--
ALTER TABLE `post_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_item_order` (`post_id`,`item_order`);

--
-- Indexes for table `post_item_images`
--
ALTER TABLE `post_item_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_item_type` (`item_type`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_media` (`post_id`,`selection_type`,`id`),
  ADD KEY `idx_selection_type_id` (`selection_type`,`id`);

--
-- Indexes for table `post_pageviews_month`
--
ALTER TABLE `post_pageviews_month`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_visit_hash` (`visit_hash`),
  ADD KEY `idx_lang_post_id` (`lang_id`,`post_id`),
  ADD KEY `idx_report` (`created_at`,`reward_amount`),
  ADD KEY `idx_report_user` (`post_user_id`,`created_at`,`reward_amount`);

--
-- Indexes for table `post_poll_votes`
--
ALTER TABLE `post_poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_answer_id` (`answer_id`),
  ADD KEY `idx_post_id_user_id` (`post_id`,`user_id`);

--
-- Indexes for table `post_selections`
--
ALTER TABLE `post_selections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_lang_type_id_post` (`lang_id`,`selection_type`,`id`,`post_id`);

--
-- Indexes for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_tag_id` (`tag_id`),
  ADD KEY `idx_lang_tag_post` (`lang_id`,`tag_id`,`post_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id_id` (`question_id`,`id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id_question_order` (`post_id`,`question_order`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id_result_order` (`post_id`,`result_order`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`);

--
-- Indexes for table `reading_lists`
--
ALTER TABLE `reading_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_post_unique` (`user_id`,`post_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rss_feeds`
--
ALTER TABLE `rss_feeds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tag_slug` (`tag_slug`),
  ADD KEY `idx_lang_id` (`lang_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_gateway_transaction_id` (`gateway_transaction_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reward_system_enabled` (`reward_system`),
  ADD KEY `idx_reward_balance` (`balance`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_purchases`
--
ALTER TABLE `user_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_post` (`user_id`,`post_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_gateway_subscription_id` (`gateway_subscription_id`);

--
-- Indexes for table `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ad_spaces`
--
ALTER TABLE `ad_spaces`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `email_blacklist`
--
ALTER TABLE `email_blacklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fonts`
--
ALTER TABLE `fonts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `language_translations`
--
ALTER TABLE `language_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1518;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poll_options`
--
ALTER TABLE `poll_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poll_votes`
--
ALTER TABLE `poll_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_additional_images`
--
ALTER TABLE `post_additional_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_items`
--
ALTER TABLE `post_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_item_images`
--
ALTER TABLE `post_item_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_pageviews_month`
--
ALTER TABLE `post_pageviews_month`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_poll_votes`
--
ALTER TABLE `post_poll_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_selections`
--
ALTER TABLE `post_selections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_tags`
--
ALTER TABLE `post_tags`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reading_lists`
--
ALTER TABLE `reading_lists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rss_feeds`
--
ALTER TABLE `rss_feeds`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_purchases`
--
ALTER TABLE `user_purchases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
