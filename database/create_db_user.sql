-- Create a dedicated DB user for Monicomlab (optional)
-- Run this in phpMyAdmin -> SQL tab, or in the MySQL/MariaDB client.
--
-- If you're using XAMPP defaults (user: root, password: empty), you can skip this.

-- Change this password before running in any real environment.
CREATE USER IF NOT EXISTS 'monicomlab_user'@'localhost' IDENTIFIED BY 'change_me_strong_password';

-- Ensure the database exists before granting (database name: monicomlab)
GRANT ALL PRIVILEGES ON `monicomlab`.* TO 'monicomlab_user'@'localhost';

FLUSH PRIVILEGES;
