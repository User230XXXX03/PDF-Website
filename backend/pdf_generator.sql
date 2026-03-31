-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 01:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pdf_generator`
--

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL ,
  `user_id` int(11) NOT NULL ,
  `template_id` int(11) NOT NULL ,
  `name` varchar(100) NOT NULL ,
  `status` enum('pending','processing','completed','failed') NOT NULL ,
  `total_count` int(11) DEFAULT 0 ,
  `generated_count` int(11) DEFAULT 0 ,
  `sent_count` int(11) DEFAULT 0 ,
  `email_recipients` text DEFAULT NULL ,
  `email_subject` varchar(255) DEFAULT NULL ,
  `email_body` text DEFAULT NULL ,
  `email_cc` varchar(500) DEFAULT NULL ,
  `email_bcc` varchar(500) DEFAULT NULL ,
  `created_at` timestamp NULL DEFAULT current_timestamp() ,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `user_id`, `template_id`, `name`, `status`, `total_count`, `generated_count`, `sent_count`, `email_recipients`, `email_subject`, `email_body`, `email_cc`, `email_bcc`, `created_at`, `updated_at`) VALUES
(6, 1, 4, 'STUDENT MARK', 'completed', 10, 2, 2, 'gblfy007@163.com', '{{STUDENT_NAME}} {{SEMESTER}} Transcript', 'Dear {{STUDENT_NAME}}:\n\nHello!\n\nPlease find your transcript for {{SEMESTER}} attached.\n\n📚 Basic Info\n- Name: {{STUDENT_NAME}}\n- ID: {{STUDENT_ID}}\n- Class: {{CLASS_NAME}}\n- Semester: {{SEMESTER}}\n\n📊 Score Details\n- Chinese: {{CHINESE}}\n- Math: {{MATH}}\n- English: {{ENGLISH}}\n- Total: {{TOTAL}}\n- Class Rank: {{RANK}}\n\nPlease find the full transcript PDF attached.\n\nIf you have any questions, please contact the Academic Affairs Office.\n\nBest regards!\n\nIssue Date: {{ISSUE_DATE}}', NULL, NULL, '2025-11-08 23:20:04', '2026-01-25 20:01:01');

-- --------------------------------------------------------

--
-- Table structure for table `batch_records`
--

CREATE TABLE `batch_records` (
  `id` int(11) NOT NULL ,
  `batch_id` int(11) NOT NULL ,
  `student_name` varchar(100) DEFAULT NULL ,
  `student_email` varchar(100) DEFAULT NULL ,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL  CHECK (json_valid(`data`)),
  `pdf_path` varchar(255) DEFAULT NULL ,
  `pdf_generated` tinyint(1) DEFAULT 0 ,
  `email_sent` tinyint(1) DEFAULT 0 ,
  `created_at` timestamp NULL DEFAULT current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `batch_records`
--

INSERT INTO `batch_records` (`id`, `batch_id`, `student_name`, `student_email`, `data`, `pdf_path`, `pdf_generated`, `email_sent`, `created_at`) VALUES
(164, 6, '', '', '{\"STUDENT_ID\":\"2025010\",\"STUDENT_NAME\":\"Julia Li\",\"STUDENT_EMAIL\":\"student10@example.com\",\"CHINESE\":89,\"MATH\":97,\"ENGLISH\":99,\"TOTAL\":285,\"RANK\":1,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":154}', 'doc_1769373428_69767ef45b9b4.pdf', 1, 0, '2026-01-25 20:16:12'),
(165, 6, '', '', '{\"STUDENT_ID\":\"2025009\",\"STUDENT_NAME\":\"Ivan Zhang\",\"STUDENT_EMAIL\":\"student9@example.com\",\"CHINESE\":86,\"MATH\":76,\"ENGLISH\":91,\"TOTAL\":253,\"RANK\":2,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":155}', NULL, 0, 0, '2026-01-25 20:16:12'),
(166, 6, '', '', '{\"STUDENT_ID\":\"2025008\",\"STUDENT_NAME\":\"Hannah Wang\",\"STUDENT_EMAIL\":\"student8@example.com\",\"CHINESE\":93,\"MATH\":76,\"ENGLISH\":83,\"TOTAL\":252,\"RANK\":3,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":156}', NULL, 0, 0, '2026-01-25 20:16:12'),
(167, 6, '', '', '{\"STUDENT_ID\":\"2025007\",\"STUDENT_NAME\":\"George Chen\",\"STUDENT_EMAIL\":\"student7@example.com\",\"CHINESE\":93,\"MATH\":78,\"ENGLISH\":72,\"TOTAL\":243,\"RANK\":4,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":157}', NULL, 0, 0, '2026-01-25 20:16:12'),
(168, 6, '', '', '{\"STUDENT_ID\":\"2025002\",\"STUDENT_NAME\":\"Bob Smith\",\"STUDENT_EMAIL\":\"student2@example.com\",\"CHINESE\":89,\"MATH\":72,\"ENGLISH\":80,\"TOTAL\":241,\"RANK\":5,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":158}', NULL, 0, 0, '2026-01-25 20:16:12'),
(169, 6, '', '', '{\"STUDENT_ID\":\"2025005\",\"STUDENT_NAME\":\"Edward Wilson\",\"STUDENT_EMAIL\":\"student5@example.com\",\"CHINESE\":67,\"MATH\":87,\"ENGLISH\":87,\"TOTAL\":241,\"RANK\":6,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":159}', NULL, 0, 0, '2026-01-25 20:16:12'),
(170, 6, '', '', '{\"STUDENT_ID\":\"2025006\",\"STUDENT_NAME\":\"Fiona Davis\",\"STUDENT_EMAIL\":\"student6@example.com\",\"CHINESE\":83,\"MATH\":64,\"ENGLISH\":93,\"TOTAL\":240,\"RANK\":7,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":160}', NULL, 0, 0, '2026-01-25 20:16:12'),
(171, 6, '', '', '{\"STUDENT_ID\":\"2025001\",\"STUDENT_NAME\":\"Alice Johnson\",\"STUDENT_EMAIL\":\"student1@example.com\",\"CHINESE\":68,\"MATH\":64,\"ENGLISH\":79,\"TOTAL\":211,\"RANK\":8,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":161}', NULL, 0, 0, '2026-01-25 20:16:12'),
(172, 6, '', '', '{\"STUDENT_ID\":\"2025003\",\"STUDENT_NAME\":\"Charlie Brown\",\"STUDENT_EMAIL\":\"student3@example.com\",\"CHINESE\":61,\"MATH\":64,\"ENGLISH\":71,\"TOTAL\":196,\"RANK\":9,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":162}', NULL, 0, 0, '2026-01-25 20:16:12'),
(173, 6, '', '', '{\"STUDENT_ID\":\"2025004\",\"STUDENT_NAME\":\"Diana Lee\",\"STUDENT_EMAIL\":\"student4@example.com\",\"CHINESE\":62,\"MATH\":60,\"ENGLISH\":60,\"TOTAL\":182,\"RANK\":10,\"CLASS_NAME\":\"Computer Science Class 1\",\"SEMESTER\":\"Fall 2024\",\"ISSUE_DATE\":\"2026-01-25\",\"id\":163}', NULL, 0, 0, '2026-01-25 20:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL ,
  `user_id` int(11) NOT NULL ,
  `smtp_host` varchar(100) DEFAULT NULL ,
  `smtp_port` int(11) DEFAULT NULL ,
  `smtp_secure` tinyint(1) DEFAULT 1 ,
  `smtp_username` varchar(100) DEFAULT NULL ,
  `smtp_password` varchar(255) DEFAULT NULL ,
  `from_email` varchar(100) DEFAULT NULL ,
  `from_name` varchar(100) DEFAULT NULL ,
  `created_at` timestamp NULL DEFAULT current_timestamp() ,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `email_settings`
--

INSERT INTO `email_settings` (`id`, `user_id`, `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_username`, `smtp_password`, `from_email`, `from_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'smtp.163.com', 465, 1, 'gblfy007@163.com', 'HKu9ScRKcF9ebcVK', 'gblfy007@163.com', 'PDF Generator System', '2025-11-08 15:02:23', '2025-11-08 23:46:59'),
(2, 4, 'smtp.163.com', 465, 1, 'gblfy007@163.com', 'HKu9ScRKcF9ebcVK', 'gblfy007@163.com', 'Education Bureau', '2025-11-09 02:07:39', '2025-11-09 02:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL ,
  `user_id` int(11) DEFAULT NULL ,
  `batch_id` int(11) DEFAULT NULL ,
  `record_id` int(11) DEFAULT NULL ,
  `type` enum('generation','email','error') NOT NULL ,
  `message` text NOT NULL ,
  `recipient_name` varchar(100) DEFAULT NULL ,
  `recipient_email` varchar(255) DEFAULT NULL ,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL  CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `batch_id`, `record_id`, `type`, `message`, `recipient_name`, `recipient_email`, `details`, `created_at`) VALUES
(81, 1, 6, NULL, 'generation', 'Batch created: Test Student Transcript', NULL, NULL, '{\"batch_id\": \"6\", \"records_count\": 10}', '2025-11-08 23:20:04'),
(82, 1, 6, NULL, 'generation', 'PDF generated for record 30', NULL, NULL, '[]', '2025-11-08 23:20:19'),
(83, 1, 6, NULL, 'generation', 'PDF generated for record 31', NULL, NULL, '[]', '2025-11-08 23:20:20'),
(84, 1, 6, NULL, 'generation', 'PDF generated for record 32', NULL, NULL, '[]', '2025-11-08 23:20:21'),
(85, 1, 6, NULL, 'generation', 'PDF generated for record 33', NULL, NULL, '[]', '2025-11-08 23:20:22'),
(86, 1, 6, NULL, 'generation', 'PDF generated for record 34', NULL, NULL, '[]', '2025-11-08 23:20:23'),
(87, 1, 6, NULL, 'generation', 'PDF generated for record 35', NULL, NULL, '[]', '2025-11-08 23:20:25'),
(88, 1, 6, NULL, 'generation', 'PDF generated for record 36', NULL, NULL, '[]', '2025-11-08 23:20:26'),
(89, 1, 6, NULL, 'generation', 'PDF generated for record 37', NULL, NULL, '[]', '2025-11-08 23:20:27'),
(90, 1, 6, NULL, 'generation', 'PDF generated for record 38', NULL, NULL, '[]', '2025-11-08 23:20:28'),
(91, 1, 6, NULL, 'generation', 'PDF generated for record 39', NULL, NULL, '[]', '2025-11-08 23:20:29'),
(92, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-08 23:37:52'),
(93, 1, 6, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 10}', '2025-11-08 23:42:29'),
(94, 1, 6, NULL, 'error', 'Email send failed', NULL, NULL, '{\"email\": \"754263923@qq.com\", \"error\": \"SMTP Error: Could not connect to SMTP host.\"}', '2025-11-08 23:42:55'),
(95, 1, 6, NULL, 'email', 'Email sent to 754263923@qq.com', NULL, NULL, '[]', '2025-11-08 23:48:51'),
(96, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-08 23:49:57'),
(97, 1, 6, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 10}', '2025-11-08 23:50:05'),
(98, 1, 6, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 10}', '2025-11-08 23:50:24'),
(99, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-08 23:56:11'),
(100, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-08 23:56:20'),
(101, 1, 6, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-08 23:56:23'),
(102, 1, 6, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-08 23:56:28'),
(109, 1, NULL, NULL, 'generation', 'Batch created: Employee Payroll Test', NULL, NULL, '{\"batch_id\": \"8\", \"records_count\": 2}', '2025-11-09 00:21:19'),
(110, 1, NULL, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:21:33'),
(111, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 2, \"success\": 0}', '2025-11-09 00:21:34'),
(112, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 2, \"success\": 0}', '2025-11-09 00:22:19'),
(113, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:25:44'),
(114, 1, NULL, NULL, 'generation', 'Batch updated: Employee Payroll Test', NULL, NULL, '[]', '2025-11-09 00:26:50'),
(115, 1, NULL, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:26:54'),
(116, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:27:01'),
(117, 1, NULL, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:31:48'),
(118, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 00:31:53'),
(121, 1, NULL, NULL, 'generation', 'Template created: payroll_template_test', NULL, NULL, '{\"template_id\": \"11\"}', '2025-11-09 01:13:56'),
(122, 1, NULL, NULL, 'generation', 'Batch created: Test Payroll Upload', NULL, NULL, '{\"batch_id\": \"10\", \"records_count\": 2}', '2025-11-09 01:14:53'),
(123, 1, NULL, NULL, 'generation', 'Batch deleted: Test Payroll Upload', NULL, NULL, '{\"batch_id\": \"10\"}', '2025-11-09 01:19:52'),
(124, 1, NULL, NULL, 'generation', 'Template deleted: payroll_template_test', NULL, NULL, '{\"template_id\": \"11\"}', '2025-11-09 01:19:57'),
(125, 1, NULL, NULL, 'generation', 'Template created: payroll_template_test (1)', NULL, NULL, '{\"template_id\": \"12\"}', '2025-11-09 01:20:26'),
(126, 1, NULL, NULL, 'generation', 'Batch created: Test Manual Upload Payroll Template', NULL, NULL, '{\"batch_id\": \"11\", \"records_count\": 2}', '2025-11-09 01:21:33'),
(127, 1, NULL, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 01:21:39'),
(128, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 01:21:42'),
(129, 1, NULL, 91, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762623337_690f7f6930f6f.pdf\", \"record_id\": 91}', '2025-11-09 01:35:37'),
(130, 1, NULL, 92, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762623337_690f7f695c23c.pdf\", \"record_id\": 92}', '2025-11-09 01:35:37'),
(131, 1, NULL, NULL, 'generation', 'Batch PDFs generated', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 01:35:37'),
(132, 1, NULL, 91, 'email', 'Email sent to Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"record_id\": 91}', '2025-11-09 01:35:41'),
(133, 1, NULL, 92, 'email', 'Email sent to Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"record_id\": 92}', '2025-11-09 01:35:42'),
(134, 1, NULL, NULL, 'email', 'Batch emails sent', NULL, NULL, '{\"failed\": 0, \"success\": 2}', '2025-11-09 01:35:42'),
(135, 1, NULL, 91, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762623527_690f8027be898.pdf\", \"record_id\": 91}', '2025-11-09 01:38:47'),
(136, 1, NULL, 92, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762623527_690f8027d9bc9.pdf\", \"record_id\": 92}', '2025-11-09 01:38:47'),
(137, 1, NULL, 91, 'email', 'Email sent to Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"record_id\": 91}', '2025-11-09 01:38:50'),
(138, 1, NULL, 92, 'email', 'Email sent to Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"record_id\": 92}', '2025-11-09 01:38:51'),
(139, 4, NULL, NULL, 'generation', 'Template created: payroll_template_test (1)', NULL, NULL, '{\"template_id\": \"13\"}', '2025-11-09 01:52:08'),
(140, 4, NULL, NULL, 'generation', 'Batch created: Test', NULL, NULL, '{\"batch_id\": \"12\", \"records_count\": 2}', '2025-11-09 01:58:32'),
(141, 4, NULL, 93, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', 'employee1@example.com', '{\"pdf_path\": \"doc_1762624717_690f84cd219d6.pdf\", \"record_id\": 93}', '2025-11-09 01:58:37'),
(142, 4, NULL, 94, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', 'employee2@example.com', '{\"pdf_path\": \"doc_1762624717_690f84cd3cdd8.pdf\", \"record_id\": 94}', '2025-11-09 01:58:37'),
(143, 4, NULL, 93, 'error', 'Email send failed for Zhang Wei', 'Zhang Wei', 'employee1@example.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 93}', '2025-11-09 02:07:48'),
(144, 4, NULL, 94, 'error', 'Email send failed for Wang Fang', 'Wang Fang', 'employee2@example.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 94}', '2025-11-09 02:07:49'),
(145, 4, NULL, NULL, 'generation', 'Batch updated: Test', NULL, NULL, '[]', '2025-11-09 02:08:08'),
(146, 4, NULL, 95, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625292_690f870c0543c.pdf\", \"record_id\": 95}', '2025-11-09 02:08:12'),
(147, 4, NULL, 96, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625292_690f870c2dba6.pdf\", \"record_id\": 96}', '2025-11-09 02:08:12'),
(148, 4, NULL, 95, 'error', 'Email send failed for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not connect to SMTP host.\", \"record_id\": 95}', '2025-11-09 02:08:14'),
(149, 4, NULL, 96, 'error', 'Email send failed for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 96}', '2025-11-09 02:08:16'),
(150, 4, NULL, 95, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625330_690f87321cab2.pdf\", \"record_id\": 95}', '2025-11-09 02:08:50'),
(151, 4, NULL, 96, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625330_690f87324442f.pdf\", \"record_id\": 96}', '2025-11-09 02:08:50'),
(152, 4, NULL, 95, 'error', 'Email send failed for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 95}', '2025-11-09 02:08:52'),
(153, 4, NULL, 96, 'error', 'Email send failed for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 96}', '2025-11-09 02:08:54'),
(154, 4, NULL, 95, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625402_690f877a4ce0b.pdf\", \"record_id\": 95}', '2025-11-09 02:10:02'),
(155, 4, NULL, 96, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625402_690f877a76166.pdf\", \"record_id\": 96}', '2025-11-09 02:10:02'),
(156, 4, NULL, 95, 'error', 'Email send failed for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 95}', '2025-11-09 02:10:05'),
(157, 4, NULL, 96, 'error', 'Email send failed for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 96}', '2025-11-09 02:10:07'),
(158, 4, NULL, 95, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625430_690f87962ecb1.pdf\", \"record_id\": 95}', '2025-11-09 02:10:30'),
(159, 4, NULL, 96, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625430_690f87965757a.pdf\", \"record_id\": 96}', '2025-11-09 02:10:30'),
(160, 4, NULL, 95, 'error', 'Email send failed for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not authenticate.\", \"record_id\": 95}', '2025-11-09 02:10:32'),
(161, 4, NULL, 96, 'error', 'Email send failed for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"error\": \"Email send failed: SMTP Error: Could not connect to SMTP host.\", \"record_id\": 96}', '2025-11-09 02:10:33'),
(162, 4, NULL, 95, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625463_690f87b76e103.pdf\", \"record_id\": 95}', '2025-11-09 02:11:03'),
(163, 4, NULL, 96, 'generation', 'PDF generated for Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762625463_690f87b79839b.pdf\", \"record_id\": 96}', '2025-11-09 02:11:03'),
(164, 4, NULL, 95, 'email', 'Email sent to Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"record_id\": 95}', '2025-11-09 02:11:05'),
(165, 4, NULL, 96, 'email', 'Email sent to Wang Fang', 'Wang Fang', '754263923@qq.com', '{\"record_id\": 96}', '2025-11-09 02:11:06'),
(166, 1, NULL, NULL, 'generation', 'Batch created: 111', NULL, NULL, '{\"batch_id\": \"13\", \"records_count\": 2}', '2025-11-09 12:41:42'),
(167, 1, NULL, 97, 'generation', 'PDF generated for Liu Qiang', 'Liu Qiang', 'employee4@example.com', '{\"pdf_path\": \"doc_1762663317_69101b95cc9a7.pdf\", \"record_id\": \"97\"}', '2025-11-09 12:41:57'),
(168, 1, NULL, NULL, 'generation', 'Batch updated: 111', NULL, NULL, '[]', '2025-11-09 12:42:20'),
(169, 1, NULL, 99, 'generation', 'PDF generated for Liu Qiang', 'Liu Qiang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762663358_69101bbee4199.pdf\", \"record_id\": \"99\"}', '2025-11-09 12:42:39'),
(170, 1, NULL, NULL, 'generation', 'Batch updated: 111', NULL, NULL, '[]', '2025-11-09 12:46:23'),
(171, 1, NULL, 101, 'generation', 'PDF generated for Liu Qiang', 'Liu Qiang', '754263923@qq.com', '{\"pdf_path\": \"doc_1762663620_69101cc40f388.pdf\", \"record_id\": 101}', '2025-11-09 12:47:00'),
(172, 1, NULL, 102, 'generation', 'PDF generated for Chen Jing', 'Chen Jing', 'zch4869@gmail.com', '{\"pdf_path\": \"doc_1762663620_69101cc43a291.pdf\", \"record_id\": 102}', '2025-11-09 12:47:00'),
(173, 1, NULL, 101, 'email', 'Email sent to Liu Qiang', 'Liu Qiang', '754263923@qq.com', '{\"record_id\": 101}', '2025-11-09 12:47:03'),
(174, 1, NULL, 102, 'email', 'Email sent to Chen Jing', 'Chen Jing', 'zch4869@gmail.com', '{\"record_id\": 102}', '2025-11-09 12:47:04'),
(175, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-09 12:48:23'),
(176, 1, 6, 103, 'generation', 'PDF generated for Zheng Shiyi', 'Zheng Shiyi', '754263923@qq.com', '{\"pdf_path\": \"doc_1762663731_69101d33bd862.pdf\", \"record_id\": 103}', '2025-11-09 12:48:51'),
(177, 1, 6, 104, 'generation', 'PDF generated for Wu Shi', 'Wu Shi', 'zch4869@gmail.com', '{\"pdf_path\": \"doc_1762663731_69101d33e6609.pdf\", \"record_id\": 104}', '2025-11-09 12:48:52'),
(178, 1, 6, 103, 'email', 'Email sent to Zheng Shiyi', 'Zheng Shiyi', '754263923@qq.com', '{\"record_id\": 103}', '2025-11-09 12:48:54'),
(179, 1, 6, 104, 'email', 'Email sent to Wu Shi', 'Wu Shi', 'zch4869@gmail.com', '{\"record_id\": 104}', '2025-11-09 12:48:55'),
(180, 1, NULL, NULL, 'generation', 'Batch updated: 111', NULL, NULL, '[]', '2025-11-10 13:10:51'),
(181, 1, NULL, NULL, 'generation', 'Batch updated: 111', NULL, NULL, '[]', '2025-11-10 17:48:56'),
(182, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-10 17:50:29'),
(183, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2025-11-10 17:51:40'),
(184, 1, NULL, NULL, 'generation', 'Template deleted: Course Feedback', NULL, NULL, '{\"template_id\": \"8\"}', '2025-11-10 17:52:28'),
(185, 1, NULL, NULL, 'generation', 'Template deleted: Offer Letter', NULL, NULL, '{\"template_id\": \"7\"}', '2025-11-10 17:52:30'),
(186, 1, NULL, NULL, 'generation', 'Template deleted: Course Certificate', NULL, NULL, '{\"template_id\": \"6\"}', '2025-11-10 17:52:38'),
(187, 1, NULL, NULL, 'generation', 'Template updated: payroll_template_test', NULL, NULL, '{\"template_id\": \"12\"}', '2025-11-10 17:53:30'),
(188, 1, NULL, NULL, 'generation', 'Template deleted: Test Transcript', NULL, NULL, '{\"template_id\": \"9\"}', '2025-11-10 17:54:36'),
(189, 1, NULL, NULL, 'generation', 'Template created: payroll_template_test', NULL, NULL, '{\"template_id\":\"14\"}', '2025-11-11 08:13:28'),
(190, 1, NULL, 114, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":\"114\",\"pdf_path\":\"doc_1769362027_6976526b3d7e0.pdf\"}', '2026-01-25 17:27:07'),
(191, 1, NULL, 114, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":114,\"pdf_path\":\"doc_1769362037_6976527556c45.pdf\"}', '2026-01-25 17:27:17'),
(192, 1, NULL, 115, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":115,\"pdf_path\":\"doc_1769362037_697652756bea2.pdf\"}', '2026-01-25 17:27:17'),
(193, 1, NULL, 116, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":116,\"pdf_path\":\"doc_1769362037_6976527580d1d.pdf\"}', '2026-01-25 17:27:17'),
(194, 1, NULL, 117, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":117,\"pdf_path\":\"doc_1769362037_6976527596128.pdf\"}', '2026-01-25 17:27:17'),
(195, 1, NULL, 118, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":118,\"pdf_path\":\"doc_1769362037_69765275aac59.pdf\"}', '2026-01-25 17:27:17'),
(196, 1, NULL, 119, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":119,\"pdf_path\":\"doc_1769362037_69765275bf975.pdf\"}', '2026-01-25 17:27:17'),
(197, 1, NULL, 120, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":120,\"pdf_path\":\"doc_1769362037_69765275d49fe.pdf\"}', '2026-01-25 17:27:17'),
(198, 1, NULL, 121, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":121,\"pdf_path\":\"doc_1769362037_69765275e9d97.pdf\"}', '2026-01-25 17:27:18'),
(199, 1, NULL, 122, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":122,\"pdf_path\":\"doc_1769362038_697652760ac14.pdf\"}', '2026-01-25 17:27:18'),
(200, 1, NULL, 123, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":123,\"pdf_path\":\"doc_1769362038_697652761fec5.pdf\"}', '2026-01-25 17:27:18'),
(201, 1, NULL, 124, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":124,\"pdf_path\":\"doc_1769362038_6976527634dac.pdf\"}', '2026-01-25 17:27:18'),
(202, 1, NULL, 125, 'generation', 'PDF generated for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"record_id\":125,\"pdf_path\":\"doc_1769362038_6976527649dee.pdf\"}', '2026-01-25 17:27:18'),
(203, 1, NULL, 114, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":114}', '2026-01-25 17:27:52'),
(204, 1, NULL, 115, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":115}', '2026-01-25 17:27:56'),
(205, 1, NULL, 116, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":116}', '2026-01-25 17:27:59'),
(206, 1, NULL, 117, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":117}', '2026-01-25 17:28:02'),
(207, 1, NULL, 118, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":118}', '2026-01-25 17:28:05'),
(208, 1, NULL, 119, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":119}', '2026-01-25 17:28:07'),
(209, 1, NULL, 120, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":120}', '2026-01-25 17:28:10'),
(210, 1, NULL, 121, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":121}', '2026-01-25 17:28:13'),
(211, 1, NULL, 122, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":122}', '2026-01-25 17:28:16'),
(212, 1, NULL, 123, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":123}', '2026-01-25 17:28:20'),
(213, 1, NULL, 124, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":124}', '2026-01-25 17:28:23'),
(214, 1, NULL, 125, 'error', 'Email send failed for Zhang San', 'Zhang San', 'zhangsan@example.com', '{\"error\":\"Email send failed: SMTP Error: Could not authenticate.\",\"record_id\":125}', '2026-01-25 17:28:25'),
(215, 1, 6, NULL, 'generation', 'Batch updated: Test Student Transcript', NULL, NULL, '[]', '2026-01-25 17:45:09'),
(216, 1, 6, 144, 'generation', 'PDF generated for Julia Li', 'Julia Li', 'student10@example.com', '{\"record_id\":\"144\",\"pdf_path\":\"doc_1769365429_69765fb58f08a.pdf\"}', '2026-01-25 18:23:49'),
(217, 1, 6, 145, 'generation', 'PDF generated for Ivan Zhang', 'Ivan Zhang', 'student9@example.com', '{\"record_id\":\"145\",\"pdf_path\":\"doc_1769365476_69765fe4a50cc.pdf\"}', '2026-01-25 18:24:36'),
(218, 1, NULL, 76, 'generation', 'PDF generated for Zhang Wei', 'Zhang Wei', '754263923@qq.com', '{\"record_id\":\"76\",\"pdf_path\":\"doc_1769365500_69765ffc28a1d.pdf\"}', '2026-01-25 18:25:00'),
(219, 1, 6, 146, 'generation', 'PDF generated for Hannah Wang', 'Hannah Wang', 'student8@example.com', '{\"record_id\":\"146\",\"pdf_path\":\"doc_1769370834_697674d2148bc.pdf\"}', '2026-01-25 19:53:54'),
(220, 1, NULL, NULL, 'generation', 'Batch deleted: Employee Payroll Test', NULL, NULL, '{\"batch_id\":\"8\"}', '2026-01-25 19:58:26'),
(221, 1, NULL, NULL, 'generation', 'Batch deleted: Test Manual Upload Payroll Template', NULL, NULL, '{\"batch_id\":\"11\"}', '2026-01-25 19:58:28'),
(222, 1, NULL, NULL, 'generation', 'Batch deleted: 111', NULL, NULL, '{\"batch_id\":\"13\"}', '2026-01-25 19:58:31'),
(223, 1, NULL, NULL, 'generation', 'Template deleted: payroll_template_test', NULL, NULL, '{\"template_id\":\"14\"}', '2026-01-25 19:58:35'),
(224, 1, NULL, NULL, 'generation', 'Template deleted: payroll_template_test', NULL, NULL, '{\"template_id\":\"12\"}', '2026-01-25 19:58:40'),
(225, 1, NULL, NULL, 'generation', 'Template deleted: Employee Payroll', NULL, NULL, '{\"template_id\":\"10\"}', '2026-01-25 19:58:43'),
(226, 1, NULL, NULL, 'generation', 'Template updated: STUDENT TRANSCRIPT', NULL, NULL, '{\"template_id\":\"4\"}', '2026-01-25 20:00:24'),
(227, 1, 6, NULL, 'generation', 'Batch updated: STUDENT MARK', NULL, NULL, '[]', '2026-01-25 20:01:01'),
(228, 1, 6, 154, 'generation', 'PDF generated for Julia Li', 'Julia Li', 'student10@example.com', '{\"record_id\":\"154\",\"pdf_path\":\"doc_1769371271_69767687e4a34.pdf\"}', '2026-01-25 20:01:11'),
(229, 1, 6, 155, 'generation', 'PDF generated for Ivan Zhang', 'Ivan Zhang', 'student9@example.com', '{\"record_id\":\"155\",\"pdf_path\":\"doc_1769371273_6976768945c8a.pdf\"}', '2026-01-25 20:01:13'),
(230, 1, 6, NULL, 'generation', 'Batch updated: STUDENT MARK', NULL, NULL, '[]', '2026-01-25 20:16:12'),
(231, 1, NULL, NULL, 'generation', 'Template updated: STUDENT TRANSCRIPT', NULL, NULL, '{\"template_id\":\"4\"}', '2026-01-25 20:36:53'),
(232, 1, 6, 164, 'generation', 'PDF generated for Julia Li', 'Julia Li', 'student10@example.com', '{\"record_id\":\"164\",\"pdf_path\":\"doc_1769373428_69767ef45b9b4.pdf\"}', '2026-01-25 20:37:08'),
(233, 1, NULL, NULL, 'generation', 'Template updated: STUDENT TRANSCRIPT', NULL, NULL, '{\"template_id\":\"4\"}', '2026-01-25 20:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL ,
  `user_id` int(11) NOT NULL ,
  `name` varchar(100) NOT NULL ,
  `content` text NOT NULL ,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL  CHECK (json_valid(`variables`)),
  `set_type` enum('Course Feedback','Certificate','Transcript','Payroll') NOT NULL ,
  `created_at` timestamp NULL DEFAULT current_timestamp() ,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `user_id`, `name`, `content`, `variables`, `set_type`, `created_at`, `updated_at`) VALUES
(4, 1, 'STUDENT TRANSCRIPT', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Student Transcript</title>\n    <style>\n        * { font-family: \"simhei\", \"Microsoft YaHei\", sans-serif !important; }\n        body { margin: 20px; }\n        .header { text-align: center; margin-bottom: 30px; }\n        .header h1 { font-size: 24px; margin: 10px 0; }\n        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }\n        .info-table td { padding: 8px; border: 1px solid #333; }\n        .info-table td.label { background: #f0f0f0; width: 25%; font-weight: bold; }\n        .score-table { width: 100%; border-collapse: collapse; table-layout: fixed; }\n        .score-table th, .score-table td { padding: 10px; border: 1px solid #333; text-align: center; width: 20%; }\n        .score-table th { background: #e0e0e0; font-weight: bold; }\n    </style>\n</head>\n<body>\n    <div class=\"header\">\n        <h1>Student Transcript</h1>\n        <p>{{CLASS_NAME}} - {{SEMESTER}}</p>\n    </div>\n    \n    <table class=\"info-table\">\n        <tr>\n            <td class=\"label\">Student ID</td>\n            <td>{{STUDENT_ID}}</td>\n            <td class=\"label\">Student Name</td>\n            <td>{{STUDENT_NAME}}</td>\n        </tr>\n        <tr>\n            <td class=\"label\">Class</td>\n            <td>{{CLASS_NAME}}</td>\n            <td class=\"label\">Semester</td>\n            <td>{{SEMESTER}}</td>\n        </tr>\n    </table>\n    \n    <table class=\"score-table\">\n        <thead>\n            <tr>\n                <th>Chinese</th>\n                <th>Math</th>\n                <th>English</th>\n                <th>Total Score</th>\n                <th>Rank</th>\n            </tr>\n        </thead>\n        <tbody>\n            <tr>\n                <td>{{CHINESE}}</td>\n                <td>{{MATH}}</td>\n                <td>{{ENGLISH}}</td>\n                <td>{{TOTAL}}</td>\n                <td>{{RANK}}</td>\n            </tr>\n        </tbody>\n    </table>\n    \n    <p style=\"margin-top: 30px; text-align: right;\">Issue Date: {{ISSUE_DATE}}</p>\n</body>\n</html>', '[\"STUDENT_ID\",\"STUDENT_NAME\",\"STUDENT_EMAIL\",\"CHINESE\",\"MATH\",\"ENGLISH\",\"TOTAL\",\"RANK\",\"CLASS_NAME\",\"SEMESTER\",\"ISSUE_DATE\"]', 'Transcript', '2025-11-08 15:35:08', '2026-01-25 20:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL ,
  `username` varchar(50) NOT NULL ,
  `email` varchar(100) NOT NULL ,
  `password` varchar(255) NOT NULL ,
  `created_at` timestamp NULL DEFAULT current_timestamp() 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$vQvVgH/oiIYOpCH57l/7BeRzT3wUm5y1XrEz3zDlyFl/jibFQeskC', '2025-11-08 15:02:23'),
(2, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-11-08 15:02:23'),
(3, 'jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-11-08 15:02:23'),
(4, 'admin1', '1@qqq.com', '$2y$12$t1pLJBFlBIu4DWnCRguQA.KxogXgL6aksTdTUb.Uo99cLEuHaVT7y', '2025-11-09 01:50:36'),
(5, '123', 'john.doe@example.com', '$2y$10$krbheyrPiWACP/AY41zSv.tPrv6NPbvev2Lzy0Ou0TgBTjIduWDpi', '2025-12-10 10:48:45'),
(6, '1234546', '123456@123.com', '$2y$10$vQvVgH/oiIYOpCH57l/7BeRzT3wUm5y1XrEz3zDlyFl/jibFQeskC', '2026-01-25 17:15:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE,
  ADD KEY `idx_template_id` (`template_id`) USING BTREE,
  ADD KEY `idx_status` (`status`) USING BTREE;

--
-- Indexes for table `batch_records`
--
ALTER TABLE `batch_records`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_batch_id` (`batch_id`) USING BTREE,
  ADD KEY `idx_pdf_generated` (`pdf_generated`) USING BTREE,
  ADD KEY `idx_email_sent` (`email_sent`) USING BTREE;

--
-- Indexes for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE,
  ADD KEY `idx_batch_id` (`batch_id`) USING BTREE,
  ADD KEY `idx_type` (`type`) USING BTREE,
  ADD KEY `idx_created_at` (`created_at`) USING BTREE;

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE,
  ADD KEY `idx_set_type` (`set_type`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `username` (`username`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`) USING BTREE,
  ADD KEY `idx_username` (`username`) USING BTREE,
  ADD KEY `idx_email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `batch_records`
--
ALTER TABLE `batch_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT for table `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `batch_records`
--
ALTER TABLE `batch_records`
  ADD CONSTRAINT `batch_records_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD CONSTRAINT `email_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
