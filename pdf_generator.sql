SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `pdf_generator` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pdf_generator`;

DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `email_settings`;
DROP TABLE IF EXISTS `batch_records`;
DROP TABLE IF EXISTS `batches`;
DROP TABLE IF EXISTS `templates`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `variables` longtext DEFAULT NULL,
  `set_type` enum('Course Feedback','Certificate','Transcript','Payroll') NOT NULL DEFAULT 'Course Feedback',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_set_type` (`set_type`),
  CONSTRAINT `templates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `total_count` int(11) DEFAULT 0,
  `generated_count` int(11) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `email_subject` varchar(255) DEFAULT NULL,
  `email_body` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `batch_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `student_email` varchar(100) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `pdf_generated` tinyint(1) DEFAULT 0,
  `email_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batch_id` (`batch_id`),
  KEY `idx_pdf_generated` (`pdf_generated`),
  KEY `idx_email_sent` (`email_sent`),
  CONSTRAINT `batch_records_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_secure` tinyint(1) DEFAULT 1,
  `smtp_username` varchar(100) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `from_email` varchar(100) DEFAULT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `email_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `type` enum('generation','email','error','system') NOT NULL,
  `message` varchar(255) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `recipient_email` varchar(100) DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_batch_id` (`batch_id`),
  KEY `idx_record_id` (`record_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logs_ibfk_3` FOREIGN KEY (`record_id`) REFERENCES `batch_records` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$rDMzsZpYjAb8Sj2GgswXXOOBlVauRcj9WPCvAiFE3h/CLIIrJzJgC', '2026-04-01 09:00:00'),
(2, 'user', 'user@example.com', '$2y$10$vfh5j7PxrpKldOCgOVOf3.J6QxI5Bx20ayXxxx/a6ATwOlmwPoLSy', '2026-04-01 09:05:00');

INSERT INTO `templates` (`id`, `user_id`, `name`, `content`, `variables`, `set_type`, `created_at`, `updated_at`) VALUES
(1, 2, 'Student Transcript Demo', '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Student Transcript</title><style>body{font-family:Arial,sans-serif;margin:24px;color:#222}.header{text-align:center;margin-bottom:24px}.header h1{font-size:24px;margin:0 0 8px}.meta{margin-bottom:18px}.info-table,.score-table{width:100%;border-collapse:collapse;margin-bottom:18px}.info-table td,.score-table th,.score-table td{border:1px solid #333;padding:8px}.label{background:#f0f0f0;font-weight:bold;width:22%}.score-table th{background:#e5e5e5}.footer{text-align:right;margin-top:28px}</style></head><body><div class="header"><h1>Student Transcript</h1><p>{{CLASS_NAME}} - {{SEMESTER}}</p></div><table class="info-table"><tr><td class="label">Student ID</td><td>{{STUDENT_ID}}</td><td class="label">Student Name</td><td>{{STUDENT_NAME}}</td></tr><tr><td class="label">Class</td><td>{{CLASS_NAME}}</td><td class="label">Semester</td><td>{{SEMESTER}}</td></tr></table><table class="score-table"><thead><tr><th>Chinese</th><th>Math</th><th>English</th><th>Total</th><th>Rank</th></tr></thead><tbody><tr><td>{{CHINESE}}</td><td>{{MATH}}</td><td>{{ENGLISH}}</td><td>{{TOTAL}}</td><td>{{RANK}}</td></tr></tbody></table><p class="footer">Issue Date: {{ISSUE_DATE}}</p></body></html>', '["STUDENT_ID","STUDENT_NAME","STUDENT_EMAIL","CHINESE","MATH","ENGLISH","TOTAL","RANK","CLASS_NAME","SEMESTER","ISSUE_DATE"]', 'Transcript', '2026-04-01 09:10:00', '2026-04-01 09:10:00');

INSERT INTO `batches` (`id`, `user_id`, `template_id`, `name`, `status`, `total_count`, `generated_count`, `sent_count`, `email_subject`, `email_body`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Demo Student Transcript Batch', 'pending', 3, 0, 0, '{{STUDENT_NAME}} {{SEMESTER}} Transcript', 'Dear {{STUDENT_NAME}},\n\nPlease find your {{SEMESTER}} transcript attached after PDF generation.\n\nStudent ID: {{STUDENT_ID}}\nClass: {{CLASS_NAME}}\nTotal Score: {{TOTAL}}\nRank: {{RANK}}\n\nRegards,\nPDF Generator System', '2026-04-01 09:20:00', '2026-04-01 09:20:00');

INSERT INTO `batch_records` (`id`, `batch_id`, `student_name`, `student_email`, `data`, `pdf_path`, `pdf_generated`, `email_sent`, `created_at`) VALUES
(1, 1, 'Alice Johnson', 'alice.student@example.com', '{"STUDENT_ID":"S1001","STUDENT_NAME":"Alice Johnson","STUDENT_EMAIL":"alice.student@example.com","CHINESE":88,"MATH":92,"ENGLISH":85,"TOTAL":265,"RANK":1,"CLASS_NAME":"Computer Science Class 1","SEMESTER":"Spring 2026","ISSUE_DATE":"2026-04-01"}', NULL, 0, 0, '2026-04-01 09:25:00'),
(2, 1, 'Ben Carter', 'ben.student@example.com', '{"STUDENT_ID":"S1002","STUDENT_NAME":"Ben Carter","STUDENT_EMAIL":"ben.student@example.com","CHINESE":82,"MATH":89,"ENGLISH":91,"TOTAL":262,"RANK":2,"CLASS_NAME":"Computer Science Class 1","SEMESTER":"Spring 2026","ISSUE_DATE":"2026-04-01"}', NULL, 0, 0, '2026-04-01 09:26:00'),
(3, 1, 'Chloe Smith', 'chloe.student@example.com', '{"STUDENT_ID":"S1003","STUDENT_NAME":"Chloe Smith","STUDENT_EMAIL":"chloe.student@example.com","CHINESE":79,"MATH":86,"ENGLISH":88,"TOTAL":253,"RANK":3,"CLASS_NAME":"Computer Science Class 1","SEMESTER":"Spring 2026","ISSUE_DATE":"2026-04-01"}', NULL, 0, 0, '2026-04-01 09:27:00');

SET FOREIGN_KEY_CHECKS = 1;
