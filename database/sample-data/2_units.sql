-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 23, 2022 at 02:13 PM
-- Server version: 10.3.32-MariaDB-cll-lve
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u6592316_beta_rencanakan`
--

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'OH', '2021-09-19 09:04:29', NULL),
(2, 'm3', '2021-09-19 09:04:29', NULL),
(3, 'Liter', '2021-09-19 09:04:29', '2021-10-22 02:53:34'),
(4, 'zak', '2021-09-19 09:04:29', NULL),
(5, 'kg', '2021-09-19 09:04:29', NULL),
(7, 'Meter', '2021-10-18 23:52:21', '2021-10-18 23:52:21'),
(8, 'Buah', '2021-10-22 02:54:19', '2021-10-22 02:54:19'),
(9, 'm2', '2021-10-22 05:56:12', '2021-10-22 05:56:12'),
(10, 'Batang', '2021-10-27 10:34:35', '2021-10-27 10:34:35'),
(11, 'Sewa-Hari', '2021-10-30 06:34:47', '2021-10-30 06:34:47'),
(12, 'Lembar', '2021-10-31 02:41:04', '2021-10-31 02:41:04'),
(13, 'Dus', '2021-11-01 01:41:58', '2021-11-01 01:41:58'),
(14, 'Sewa-Jam', '2021-11-01 15:42:48', '2021-11-01 15:42:48'),
(15, 'Jam', '2021-11-01 15:42:54', '2021-11-01 15:42:54'),
(16, 'Set', '2021-11-01 16:09:57', '2021-11-01 16:09:57'),
(17, 'Unit', '2021-11-01 16:41:50', '2021-11-01 16:41:50'),
(18, 'Sewa-Bulan', '2021-11-01 16:44:00', '2021-11-01 16:44:00'),
(19, 'Pohon', '2021-11-02 13:37:02', '2021-11-02 13:37:02'),
(20, 'Rol', '2021-11-02 13:43:24', '2021-11-02 13:43:24'),
(21, 'Tube', '2021-11-02 13:50:19', '2021-11-02 13:50:19'),
(22, 'Sewa-Hari/m2', '2021-11-02 14:01:26', '2021-11-02 14:01:26'),
(23, 'Botol', '2021-11-02 14:02:07', '2021-11-02 14:02:07');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
