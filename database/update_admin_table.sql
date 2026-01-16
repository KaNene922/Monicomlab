-- SQL Update to add department and location tracking to admin table
-- This allows tracking where reports are coming from

-- Add new columns to admin table
ALTER TABLE `admin` 
ADD COLUMN `department` VARCHAR(255) DEFAULT NULL AFTER `name`,
ADD COLUMN `location` VARCHAR(255) DEFAULT NULL AFTER `department`,
ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `image`,
ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

-- Update existing records with default department
UPDATE `admin` SET `department` = 'IT Department' WHERE `department` IS NULL;

-- Create an index on department for faster queries
CREATE INDEX idx_department ON admin(department);

-- Optional: Create a table to track report origins
CREATE TABLE IF NOT EXISTS `report_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `report_type` VARCHAR(100) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `device_name` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_department` (`department`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `admin`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create view for department statistics
CREATE OR REPLACE VIEW `department_report_stats` AS
SELECT 
    a.department,
    COUNT(DISTINCT a.user_id) as total_users,
    COUNT(t.id) as total_reports,
    SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) as pending_reports,
    SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_reports,
    SUM(CASE WHEN t.status = 'Resolved' THEN 1 ELSE 0 END) as resolved_reports
FROM 
    admin a
LEFT JOIN 
    ticket t ON DATE(t.created_at) >= DATE(a.created_at)
GROUP BY 
    a.department;
