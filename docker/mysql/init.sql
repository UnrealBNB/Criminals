-- Criminals Game Database Initialization

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS criminals CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE criminals;

-- Grant privileges to user
GRANT ALL PRIVILEGES ON criminals.* TO 'criminals'@'%';
FLUSH PRIVILEGES;