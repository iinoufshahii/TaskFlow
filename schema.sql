-- TaskFlow database schema
-- Import with: mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS `taskflow` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taskflow`;

-- Drop existing tables (safe to re-run)
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `archives`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `users`;

-- Users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tasks
CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_name` VARCHAR(255),
  `category` VARCHAR(100),
  `due_date` DATE,
  `priority` VARCHAR(20),
  `status` VARCHAR(50),
  `description` TEXT,
  `users_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Archives (same structure as tasks)
CREATE TABLE `archives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_name` VARCHAR(255),
  `category` VARCHAR(100),
  `due_date` DATE,
  `priority` VARCHAR(20),
  `status` VARCHAR(50),
  `description` TEXT,
  `users_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes
CREATE TABLE `notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `note_text` TEXT,
  `users_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
