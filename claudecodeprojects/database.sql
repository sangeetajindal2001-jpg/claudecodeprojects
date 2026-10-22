-- Import this in phpMyAdmin (Import tab) or paste it into the SQL tab.
CREATE DATABASE IF NOT EXISTS claudecode_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE claudecode_login;

-- password_hash is NULL for users who only sign in with Google or Facebook
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(30)  NOT NULL UNIQUE,
  email         VARCHAR(150) NULL UNIQUE,
  full_name     VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- One row per linked Google or Facebook account
CREATE TABLE IF NOT EXISTS social_accounts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  provider    ENUM('google', 'facebook') NOT NULL,
  provider_id VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY provider_user (provider, provider_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
