-- Database: user_management_system
CREATE DATABASE IF NOT EXISTS `user_management_system`;
USE `user_management_system`;

-- Table: users (for login/registration)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table: user_profiles (for donor management/CRUD)
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `user_id` int,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) UNIQUE,
  `phone` varchar(20) UNIQUE,
  `address` text,
  `blood_type` varchar(10),
  `role` varchar(50) DEFAULT 'donor',
  `status` varchar(20) DEFAULT 'active',
  `blood_donation_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: user_sessions (for remember me functionality)
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `session_token` varchar(255) NOT NULL UNIQUE,
  `remember_token` varchar(255) UNIQUE,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_session_token (session_token),
  INDEX idx_remember_token (remember_token),
  INDEX idx_expires_at (expires_at)
);

-- Insert sample users
INSERT INTO `users` (`username`, `password`, `email`) VALUES
('admin', 'admin123', 'admin@example.com'),
('user1', 'user123', 'user1@example.com'),
('user2', 'user123', 'user2@example.com');

-- Insert sample donor profiles
INSERT INTO `user_profiles` (`user_id`, `full_name`, `email`, `phone`, `address`, `blood_type`, `role`, `status`, `blood_donation_date`) VALUES
(1, 'Admin User', 'admin@example.com', '0123456789', 'Ha Noi, Vietnam', 'O+', 'admin', 'active', NOW()),
(2, 'John Doe', 'user1@example.com', '0987654321', 'Ho Chi Minh, Vietnam', 'A+', 'donor', 'active', NOW()),
(3, 'Jane Smith', 'user2@example.com', '0912345678', 'Da Nang, Vietnam', 'B+', 'donor', 'active', NOW());
