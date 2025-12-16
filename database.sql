-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: sql100.infinityfree.com
-- Thời gian đã tạo: Th12 15, 2025 lúc 10:28 PM
-- Phiên bản máy phục vụ: 10.6.22-MariaDB
-- Phiên bản PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `if0_40633698_sms_demo`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `bloodType` varchar(10) DEFAULT NULL,
  `appointmentDate` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Confirmed','Rejected','ReadyToCheck','ReadyToDonate','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `healthCheckStatus` enum('Pending','Passed','Failed') NOT NULL DEFAULT 'Pending',
  `actualVolume` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `appointments`
--

INSERT INTO `appointments` (`id`, `userId`, `name`, `email`, `phone`, `dob`, `bloodType`, `appointmentDate`, `location`, `notes`, `status`, `healthCheckStatus`, `actualVolume`, `createdAt`, `updatedAt`) VALUES
(1, 5, 'Donor 1', 'donor1@example.com', '0900000101', '1990-01-01', 'A', '2025-12-15 13:53:56', 'Center A', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(2, 6, 'Donor 2', 'donor2@example.com', '0900000102', '1990-01-01', 'B', '2025-12-16 13:53:56', 'Center B', 'Auto-generated', 'Confirmed', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(4, 8, 'Donor 4', 'donor4@example.com', '0900000104', '1990-01-01', 'O', '2025-12-18 13:53:56', 'Center D', 'Auto-generated', 'ReadyToCheck', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(5, 9, 'Donor 5', 'donor5@example.com', '0900000105', '1990-01-01', 'A', '2025-12-19 13:53:56', 'Center E', 'Auto-generated', 'Completed', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-15 08:27:51'),
(6, 10, 'Donor 6', 'donor6@example.com', '0900000106', '1990-01-01', 'B', '2025-12-20 13:53:56', 'Center A', 'Auto-generated', 'Completed', 'Passed', 450, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(8, 12, 'Donor 8', 'donor8@example.com', '0900000108', '1990-01-01', 'O', '2025-12-22 13:53:56', 'Center C', 'Auto-generated', 'Pending', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(9, 13, 'Donor 9', 'donor9@example.com', '0900000109', '1990-01-01', 'A', '2025-12-23 13:53:56', 'Center D', 'Auto-generated', 'Confirmed', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(10, 14, 'Donor 10', 'donor10@example.com', '0900000110', '1990-01-01', 'B', '2025-12-24 13:53:56', 'Center E', 'Auto-generated', 'Rejected', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(11, 15, 'Donor 11', 'donor11@example.com', '0900000111', '1990-01-01', 'AB', '2025-12-25 13:53:56', 'Center A', 'Auto-generated', 'ReadyToCheck', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(12, 16, 'Donor 12', 'donor12@example.com', '0900000112', '1990-01-01', 'O', '2025-12-26 13:53:56', 'Center B', 'Auto-generated', 'ReadyToDonate', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(13, 17, 'Donor 13', 'donor13@example.com', '0900000113', '1990-01-01', 'A', '2025-12-27 13:53:56', 'Center C', 'Auto-generated', 'Completed', 'Passed', 250, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(14, 18, 'Donor 14', 'donor14@example.com', '0900000114', '1990-01-01', 'B', '2025-12-28 13:53:56', 'Center D', 'Auto-generated', 'Cancelled', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(15, 19, 'Donor 15', 'donor15@example.com', '0900000115', '1990-01-01', 'AB', '2025-12-29 13:53:56', 'Center E', 'Auto-generated', 'Pending', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(16, 20, 'Donor 16', 'donor16@example.com', '0900000116', '1990-01-01', 'O', '2025-12-30 13:53:56', 'Center A', 'Auto-generated', 'Confirmed', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(17, 21, 'Donor 17', 'donor17@example.com', '0900000117', '1990-01-01', 'A', '2025-12-31 13:53:56', 'Center B', 'Auto-generated', 'Rejected', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(18, 22, 'Donor 18', 'donor18@example.com', '0900000118', '1990-01-01', 'B', '2026-01-01 13:53:56', 'Center C', 'Auto-generated', 'ReadyToCheck', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(21, 5, 'Donor 1', 'donor1@example.com', '0900000121', '1990-01-01', 'A', '2026-01-04 13:53:56', 'Center A', 'Auto-generated', 'Cancelled', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(22, 6, 'Donor 2', 'donor2@example.com', '0900000122', '1990-01-01', 'B', '2026-01-05 13:53:56', 'Center B', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(24, 8, 'Donor 4', 'donor4@example.com', '0900000124', '1990-01-01', 'O', '2026-01-07 13:53:56', 'Center D', 'Auto-generated', 'Rejected', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(25, 9, 'Donor 5', 'donor5@example.com', '0900000125', '1990-01-01', 'A', '2026-01-08 13:53:56', 'Center E', 'Auto-generated', 'ReadyToCheck', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(26, 10, 'Donor 6', 'donor6@example.com', '0900000126', '1990-01-01', 'B', '2026-01-09 13:53:56', 'Center A', 'Auto-generated', 'ReadyToDonate', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(28, 12, 'Donor 8', 'donor8@example.com', '0900000128', '1990-01-01', 'O', '2026-01-11 13:53:56', 'Center C', 'Auto-generated', 'Cancelled', 'Pending', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(29, 13, 'Donor 9', 'donor9@example.com', '0900000129', '1990-01-01', 'A', '2026-01-12 13:53:56', 'Center D', 'Auto-generated', 'Pending', 'Passed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(30, 14, 'Donor 10', 'donor10@example.com', '0900000130', '1990-01-01', 'B', '2026-01-13 13:53:56', 'Center E', 'Auto-generated', 'Confirmed', 'Failed', NULL, '2025-12-14 06:51:25', '2025-12-14 06:51:25'),
(46, 37, 'Nov Donor A', 'nov1@test.com', '0901234567', NULL, 'A', '2025-11-05 10:00:00', 'Main Center', 'Auto-generated', 'Confirmed', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-15 01:17:27'),
(47, 38, 'Nov Donor B', 'nov2@test.com', '0901234567', NULL, 'A', '2025-11-12 14:00:00', 'Main Center', 'Auto-generated', 'Rejected', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-15 01:17:22'),
(48, 39, 'Nov Donor C', 'nov3@test.com', '0901234567', NULL, 'A', '2025-11-20 09:30:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(49, 40, 'Nov Donor D', 'nov4@test.com', '0901234567', NULL, 'A', '2025-11-28 16:15:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(50, 41, 'Dec Donor A', 'dec1@test.com', '0901234567', NULL, 'A', '2025-12-02 08:00:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(51, 42, 'Dec Donor B', 'dec2@test.com', '0901234567', NULL, 'A', '2025-12-05 11:00:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(52, 43, 'Dec Donor C', 'dec3@test.com', '0901234567', NULL, 'A', '2025-12-10 13:45:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(53, 44, 'Dec Donor D', 'dec4@test.com', '0901234567', NULL, 'A', '2025-12-14 09:00:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(54, 45, 'Dec Donor E', 'dec5@test.com', '0901234567', NULL, 'A', '2025-12-14 15:30:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(55, 46, 'Dec Donor F', 'dec6@test.com', '0901234567', NULL, 'A', '2025-12-18 10:20:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(56, 47, 'Dec Donor G', 'dec7@test.com', '0901234567', NULL, 'A', '2025-12-25 14:00:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(57, 48, 'Jan Donor A', 'jan1@test.com', '0901234567', NULL, 'A', '2026-01-05 09:00:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(58, 49, 'Jan Donor B', 'jan2@test.com', '0901234567', NULL, 'A', '2026-01-10 10:30:00', 'Main Center', 'Auto-generated', 'Pending', 'Pending', NULL, '2025-12-14 09:51:33', '2025-12-14 09:51:33'),
(61, 4, 'Donor 1', 'donor1@example.com', '0900000101', NULL, 'A+', '2026-01-11 08:00:00', 'Main Center', '1', 'Pending', 'Pending', NULL, '2025-12-14 16:20:43', '2025-12-14 16:20:43'),
(62, 4, 'Donor 1', 'donor1@example.com', '0900000101', NULL, 'A+', '2026-01-17 08:00:00', 'Main Center', '1 | Health Check: OK. 1', 'Completed', 'Pending', NULL, '2025-12-15 03:41:21', '2025-12-15 07:09:44'),
(63, 4, 'Donor 1', 'donor1@example.com', '0900000101', NULL, 'A+', '2026-01-11 08:00:00', 'Main Center', '1', 'Confirmed', 'Pending', NULL, '2025-12-15 07:01:38', '2025-12-15 08:27:22'),
(64, 4, 'Donor 1', 'donor1@example.com', '0900000101', NULL, 'A+', '2026-01-03 08:00:00', 'Main Center', '1', 'Pending', 'Pending', NULL, '2025-12-15 08:26:10', '2025-12-15 08:26:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bloodunit`
--

CREATE TABLE `bloodunit` (
  `id` int(11) NOT NULL,
  `appointmentId` int(11) DEFAULT NULL,
  `volume` int(11) NOT NULL,
  `bloodType` varchar(10) NOT NULL,
  `rhType` varchar(10) NOT NULL,
  `collectionDate` datetime NOT NULL,
  `expiryDate` datetime NOT NULL,
  `storageLocation` varchar(255) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bloodunit`
--

INSERT INTO `bloodunit` (`id`, `appointmentId`, `volume`, `bloodType`, `rhType`, `collectionDate`, `expiryDate`, `storageLocation`, `createdAt`, `status`) VALUES
(1, 30, 250, 'B', 'Positive', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 0', '2025-12-14 06:51:25', 'Available'),
(2, NULL, 450, 'AB', 'Negative', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 0', '2025-12-14 06:51:25', 'Available'),
(3, 25, 450, 'A', 'Negative', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 2', '2025-12-14 06:51:25', 'Available'),
(4, NULL, 350, 'O', 'Positive', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 1', '2025-12-14 06:51:25', 'Available'),
(5, 15, 250, 'AB', 'Negative', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 0', '2025-12-14 06:51:25', 'Available'),
(6, 13, 250, 'A', 'Negative', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 2', '2025-12-14 06:51:25', 'Available'),
(7, 10, 450, 'B', 'Positive', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 2', '2025-12-14 06:51:25', 'Available'),
(8, 6, 450, 'B', 'Positive', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 0', '2025-12-14 06:51:25', 'Available'),
(9, 5, 350, 'A', 'Negative', '2025-12-14 06:51:25', '2026-01-18 06:51:25', 'Fridge 1', '2025-12-14 06:51:25', 'Available'),
(10, NULL, 350, 'A', 'Positive', '2025-12-14 08:25:22', '2026-01-18 08:25:22', 'Central Bank', '2025-12-14 08:25:22', 'Available'),
(11, NULL, 250, 'A', 'Positive', '2024-02-14 00:00:00', '2025-12-31 00:00:00', 'Fridge B', '2025-12-14 08:49:04', 'Available'),
(12, NULL, 400, 'A', 'Positive', '2024-12-01 00:00:00', '2025-12-31 00:00:00', 'Fridge A', '2025-12-14 09:25:30', 'Available'),
(13, NULL, 350, 'A', 'Positive', '2025-12-15 00:00:00', '2026-01-19 00:00:00', 'Central Fridge', '2025-12-15 07:09:44', 'Available'),
(14, NULL, 350, 'A', 'Positive', '2025-12-15 00:00:00', '2026-01-19 00:00:00', 'Central Fridge', '2025-12-15 08:27:51', 'Available');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `id` int(11) NOT NULL,
  `blood_group` varchar(10) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `blood_inventory`
--

INSERT INTO `blood_inventory` (`id`, `blood_group`, `quantity`) VALUES
(1, 'A+', 15),
(2, 'A-', 5),
(3, 'B+', 10),
(4, 'B-', 2),
(5, 'AB+', 4),
(6, 'AB-', 1),
(7, 'O+', 25),
(8, 'O-', 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donor_profiles`
--

CREATE TABLE `donor_profiles` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `dateOfBirth` datetime DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `bloodType` enum('A','B','AB','O') DEFAULT NULL,
  `rhType` enum('Positive','Negative') DEFAULT NULL,
  `medicalHistory` text DEFAULT NULL,
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donor_profiles`
--

INSERT INTO `donor_profiles` (`id`, `userId`, `phone`, `dateOfBirth`, `address`, `gender`, `bloodType`, `rhType`, `medicalHistory`, `updatedAt`) VALUES
(1, 4, '0900000101', '1990-01-01 00:00:00', 'Address donor1', 'Male', 'A', 'Positive', NULL, '2025-12-14 06:51:25'),
(2, 5, '0900000102', '1990-02-02 00:00:00', 'Address donor2', 'Female', 'B', 'Negative', NULL, '2025-12-14 06:51:25'),
(3, 6, '0900000103', '1990-03-03 00:00:00', 'Address donor3', 'Male', 'AB', 'Positive', NULL, '2025-12-14 06:51:25'),
(5, 8, '0900000105', '1990-05-05 00:00:00', 'Address donor5', 'Male', 'A', 'Positive', NULL, '2025-12-14 06:51:25'),
(6, 9, '0900000106', '1990-06-06 00:00:00', 'Address donor6', 'Female', 'B', 'Negative', NULL, '2025-12-14 06:51:25'),
(7, 10, '0900000107', '1990-07-07 00:00:00', 'Address donor7', 'Male', 'AB', 'Positive', NULL, '2025-12-14 06:51:25'),
(9, 12, '0900000109', '1990-09-09 00:00:00', 'Address donor9', 'Male', 'A', 'Positive', NULL, '2025-12-14 06:51:25'),
(10, 13, '0900000110', '1990-10-10 00:00:00', 'Address donor10', 'Female', 'B', 'Negative', NULL, '2025-12-14 06:51:25'),
(11, 14, '0900000111', '1990-11-11 00:00:00', 'Address donor11', 'Male', 'AB', 'Positive', NULL, '2025-12-14 06:51:25'),
(12, 15, '0900000112', '1990-12-12 00:00:00', 'Address donor12', 'Female', 'O', 'Negative', NULL, '2025-12-14 06:51:25'),
(13, 16, '0900000113', '1990-01-13 00:00:00', 'Address donor13', 'Male', 'A', 'Positive', NULL, '2025-12-14 06:51:25'),
(14, 17, '0900000114', '1990-02-14 00:00:00', 'Address donor14', 'Female', 'B', 'Negative', NULL, '2025-12-14 06:51:25'),
(15, 18, '0900000115', '1990-03-15 00:00:00', 'Address donor15', 'Male', 'AB', 'Positive', NULL, '2025-12-14 06:51:25'),
(16, 19, '0900000116', '1990-04-16 00:00:00', 'Address donor16', 'Female', 'O', 'Negative', NULL, '2025-12-14 06:51:25'),
(17, 20, '0900000117', '1990-05-17 00:00:00', 'Address donor17', 'Male', 'A', 'Positive', NULL, '2025-12-14 06:51:25'),
(18, 21, '0900000118', '1990-06-18 00:00:00', 'Address donor18', 'Female', 'B', 'Negative', NULL, '2025-12-14 06:51:25'),
(19, 22, '0900000119', '1990-07-19 00:00:00', 'Address donor19', 'Male', 'AB', 'Positive', NULL, '2025-12-14 06:51:25'),
(21, 50, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-15 10:27:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `healthcheck`
--

CREATE TABLE `healthcheck` (
  `id` int(11) NOT NULL,
  `appointmentId` int(11) NOT NULL,
  `weight` float NOT NULL,
  `bloodPressure` varchar(50) NOT NULL,
  `heartRate` int(11) NOT NULL,
  `temperature` float NOT NULL,
  `isNormal` tinyint(1) NOT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `healthcheck`
--

INSERT INTO `healthcheck` (`id`, `appointmentId`, `weight`, `bloodPressure`, `heartRate`, `temperature`, `isNormal`, `notes`, `createdAt`) VALUES
(1, 30, 65, '110/70', 60, 36.4, 0, 'Auto healthcheck', '2025-12-14 06:51:25'),
(2, 29, 66, '111/71', 61, 36.5, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(3, 28, 67, '112/72', 62, 36.6, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(5, 26, 69, '114/74', 64, 36.8, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(6, 25, 70, '115/75', 65, 36.4, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(7, 24, 71, '116/76', 66, 36.5, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(9, 22, 73, '118/78', 68, 36.7, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(10, 21, 74, '119/79', 69, 36.8, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(13, 18, 77, '122/72', 72, 36.6, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(14, 17, 78, '123/73', 73, 36.7, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(15, 16, 79, '124/74', 74, 36.8, 0, 'Auto healthcheck', '2025-12-14 06:51:25'),
(16, 15, 80, '125/75', 75, 36.4, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(17, 14, 81, '126/76', 76, 36.5, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(18, 13, 82, '127/77', 77, 36.6, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(19, 12, 83, '128/78', 78, 36.7, 1, 'Auto healthcheck', '2025-12-14 06:51:25'),
(20, 11, 84, '129/79', 79, 36.8, 1, 'Auto healthcheck', '2025-12-14 06:51:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('System','Appointment','BloodUrgent','Inventory') DEFAULT 'System',
  `isRead` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notification`
--

INSERT INTO `notification` (`id`, `userId`, `title`, `message`, `type`, `isRead`, `createdAt`) VALUES
(1, 1, 'System ready', 'Database seeded', 'System', 0, '2025-12-14 01:56:00'),
(2, 4, 'Appointment scheduled', 'Your appointment is scheduled', 'Appointment', 0, '2025-12-14 01:56:00'),
(3, 5, 'Appointment confirmed', 'Your appointment has been confirmed', 'Appointment', 0, '2025-12-14 01:56:00'),
(4, 6, 'Appointment scheduled', 'Your appointment is scheduled', 'Appointment', 0, '2025-12-14 01:56:00'),
(6, 8, 'Health check needed', 'Please attend health check', 'Appointment', 0, '2025-12-14 01:56:00'),
(7, 9, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(8, 10, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(10, 12, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(11, 13, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(12, 14, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(13, 15, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(14, 16, 'Donation thank you', 'Thank you for donating', 'System', 0, '2025-12-14 01:56:00'),
(15, 17, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(16, 18, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(17, 19, 'Donation processed', 'Your donation has been processed', 'System', 0, '2025-12-14 01:56:00'),
(18, 20, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(19, 21, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00'),
(20, 22, 'Appointment reminder', 'Reminder: upcoming appointment', 'Appointment', 0, '2025-12-14 01:56:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `avatarUrl` varchar(255) DEFAULT NULL,
  `role` enum('Admin','Donor','Doctor') NOT NULL DEFAULT 'Donor',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(191) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `remember_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `name`, `email`, `avatarUrl`, `role`, `createdAt`, `updatedAt`, `reset_token`, `reset_token_expiry`, `is_deleted`, `remember_token`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'Administrator', 'admin@example.com', NULL, 'Admin', '2025-12-14 01:56:00', '2025-12-14 03:06:53', NULL, NULL, 0, NULL),
(2, 'doctor1', 'e10adc3949ba59abbe56e057f20f883e', 'Dr. Alice', 'doctor1@example.com', NULL, 'Doctor', '2025-12-14 01:56:00', '2025-12-14 03:06:53', NULL, NULL, 0, NULL),
(3, 'doctor2', 'e10adc3949ba59abbe56e057f20f883e', 'Dr. Bob', 'doctor2@example.com', NULL, 'Doctor', '2025-12-14 01:56:00', '2025-12-14 03:06:53', NULL, NULL, 0, NULL),
(4, 'donor1', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 1', 'donor1@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-15 14:07:24', NULL, NULL, 0, 'a864192194a62440d19e7575e8fc0aeb5a12a9233da9428578e48f012b2355bf'),
(5, 'donor2', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 2', 'donor2@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(6, 'donor3', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 3', 'donor3@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(8, 'donor5', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 5', 'donor5@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(9, 'donor6', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 6', 'donor6@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(10, 'donor7', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 7', 'donor7@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(12, 'donor9', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 9', 'donor9@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(13, 'donor10', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 10', 'donor10@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(14, 'donor11', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 11', 'donor11@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(15, 'donor12', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 12', 'donor12@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(16, 'donor13', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 13', 'donor13@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(17, 'donor14', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 14', 'donor14@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(18, 'donor15', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 15', 'donor15@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(19, 'donor16', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 16', 'donor16@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(20, 'donor17', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 17', 'donor17@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(21, 'donor18', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 18', 'donor18@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 06:51:25', NULL, NULL, 0, NULL),
(22, 'donor20', 'e10adc3949ba59abbe56e057f20f883e', 'Donor 20', 'donor19@example.com', NULL, 'Donor', '2025-12-14 06:51:25', '2025-12-14 07:58:23', NULL, NULL, 0, NULL),
(37, 'nov_donor1', 'e10adc3949ba59abbe56e057f20f883e', 'Nov Donor A', 'nov1@test.com', NULL, 'Donor', '2025-11-05 10:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(38, 'nov_donor2', 'e10adc3949ba59abbe56e057f20f883e', 'Nov Donor B', 'nov2@test.com', NULL, 'Donor', '2025-11-12 14:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(39, 'nov_donor3', 'e10adc3949ba59abbe56e057f20f883e', 'Nov Donor C', 'nov3@test.com', NULL, 'Donor', '2025-11-20 09:30:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(40, 'nov_donor4', 'e10adc3949ba59abbe56e057f20f883e', 'Nov Donor D', 'nov4@test.com', NULL, 'Donor', '2025-11-28 16:15:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(41, 'dec_donor1', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor A', 'dec1@test.com', NULL, 'Donor', '2025-12-02 08:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(42, 'dec_donor2', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor B', 'dec2@test.com', NULL, 'Donor', '2025-12-05 11:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(43, 'dec_donor3', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor C', 'dec3@test.com', NULL, 'Donor', '2025-12-10 13:45:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(44, 'dec_donor4', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor D', 'dec4@test.com', NULL, 'Donor', '2025-12-14 09:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(45, 'dec_donor5', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor E', 'dec5@test.com', NULL, 'Donor', '2025-12-14 15:30:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(46, 'dec_donor6', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor F', 'dec6@test.com', NULL, 'Donor', '2025-12-18 10:20:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(47, 'dec_donor7', 'e10adc3949ba59abbe56e057f20f883e', 'Dec Donor G', 'dec7@test.com', NULL, 'Donor', '2025-12-25 14:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(48, 'jan_donor1', 'e10adc3949ba59abbe56e057f20f883e', 'Jan Donor A', 'jan1@test.com', NULL, 'Donor', '2026-01-05 09:00:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(49, 'jan_donor2', 'e10adc3949ba59abbe56e057f20f883e', 'Jan Donor B', 'jan2@test.com', NULL, 'Donor', '2026-01-10 10:30:00', '2025-12-14 09:51:33', NULL, NULL, 0, NULL),
(50, '1', '$2y$10$VNwOt6mf/adFr/MFx0NoQuksDfs7..RN12YoRUprkECB13/c8WKNi', '1 1', '1@d.d', 'https://ui-avatars.com/api/?name=1+1&background=random&color=fff', 'Donor', '2025-12-15 10:27:24', '2025-12-15 10:27:24', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_user_fk` (`userId`);

--
-- Chỉ mục cho bảng `bloodunit`
--
ALTER TABLE `bloodunit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bloodunit_appointment_unique` (`appointmentId`);

--
-- Chỉ mục cho bảng `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blood_group` (`blood_group`);

--
-- Chỉ mục cho bảng `donor_profiles`
--
ALTER TABLE `donor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `donor_profiles_userid_unique` (`userId`);

--
-- Chỉ mục cho bảng `healthcheck`
--
ALTER TABLE `healthcheck`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `healthcheck_appointment_unique` (`appointmentId`);

--
-- Chỉ mục cho bảng `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_user_fk` (`userId`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `role` (`role`);

--
-- Chỉ mục cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT cho bảng `bloodunit`
--
ALTER TABLE `bloodunit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `donor_profiles`
--
ALTER TABLE `donor_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `healthcheck`
--
ALTER TABLE `healthcheck`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_user_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bloodunit`
--
ALTER TABLE `bloodunit`
  ADD CONSTRAINT `bloodunit_appointment_fk` FOREIGN KEY (`appointmentId`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `donor_profiles`
--
ALTER TABLE `donor_profiles`
  ADD CONSTRAINT `donor_profiles_user_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `healthcheck`
--
ALTER TABLE `healthcheck`
  ADD CONSTRAINT `healthcheck_appointment_fk` FOREIGN KEY (`appointmentId`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_user_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_users_new` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
