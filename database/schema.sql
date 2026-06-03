-- Secure Programming College Project
-- Database Schema

CREATE DATABASE IF NOT EXISTS secure_programming_college_project
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE secure_programming_college_project;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    PRIMARY KEY AUTO_INCREMENT,
    username      VARCHAR(50)     NOT NULL UNIQUE,
    email         VARCHAR(100)    NOT NULL UNIQUE,
    password      VARCHAR(255)    NOT NULL,   -- bcrypt hash
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts table (for rate limiting / brute-force protection)
CREATE TABLE IF NOT EXISTS login_attempts (
    id            INT UNSIGNED    PRIMARY KEY AUTO_INCREMENT,
    identifier    VARCHAR(100)    NOT NULL,
    ip_address    VARCHAR(45)     NOT NULL,
    attempted_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier_time (identifier, attempted_at),
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contacts (
    id          INT UNSIGNED    PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,
    email       VARCHAR(100)    NOT NULL,
    message     TEXT            NOT NULL,
    ip_address  VARCHAR(45)     NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
