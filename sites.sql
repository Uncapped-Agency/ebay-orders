-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 04, 2022 at 12:44 PM
-- Server version: 5.7.36
-- PHP Version: 8.1.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sites`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_grants`
--

CREATE TABLE `auth_grants` (
  `id` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `expiry` int(11) NOT NULL,
  `refresh_token` text NOT NULL,
  `refresh_token_expiry` int(11) NOT NULL,
  `token_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `auth_grants`
--

INSERT INTO `auth_grants` (`id`, `access_token`, `expiry`, `refresh_token`, `refresh_token_expiry`, `token_type`) VALUES
(1, 'rrtget', 4, 'retretertert', 4, '44444444');

-- --------------------------------------------------------

--
-- Table structure for table `web`
--

CREATE TABLE `web` (
  `OrderNo` varchar(25) NOT NULL,
  `PONumber` varchar(50) NOT NULL,
  `CustType` int(11) NOT NULL,
  `SiteID` varchar(10) NOT NULL,
  `ShipName` varchar(100) NOT NULL,
  `ShipAddress1` varchar(100) NOT NULL,
  `ShipAddress2` varchar(100) NOT NULL,
  `ShipCity` varchar(50) NOT NULL,
  `StateCode` varchar(10) NOT NULL,
  `ShipState` varchar(25) NOT NULL,
  `ShipPostalCode` varchar(25) NOT NULL,
  `CountryCode` varchar(10) NOT NULL,
  `ShipCountry` varchar(50) NOT NULL,
  `ShipPhoneNumber` varchar(15) NOT NULL,
  `ShipFaxNumber` varchar(25) NOT NULL,
  `ShipEmail` varchar(100) NOT NULL,
  `ShipMethod` varchar(50) NOT NULL,
  `OrderDate` datetime NOT NULL,
  `Notes` varchar(8000) NOT NULL,
  `Freight` decimal(18,6) NOT NULL,
  `Handling` decimal(18,6) NOT NULL,
  `Carrier` varchar(50) NOT NULL,
  `TaxCode` varchar(25) NOT NULL,
  `TaxRate` decimal(18,6) NOT NULL,
  `ItemCode` varchar(15) NOT NULL,
  `ItemName` varchar(300) NOT NULL,
  `OrderLineNo` bigint(20) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(18,6) NOT NULL,
  `UnitDiscPrice` decimal(18,6) NOT NULL,
  `SalesTax` decimal(18,6) NOT NULL,
  `DiscountCode` varchar(25) NOT NULL,
  `DiscountValue` decimal(18,6) NOT NULL,
  `SubTotal` decimal(18,6) NOT NULL,
  `Total` decimal(18,6) NOT NULL,
  `SiteCode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_grants`
--
ALTER TABLE `auth_grants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web`
--
ALTER TABLE `web`
  ADD PRIMARY KEY (`OrderNo`,`PONumber`,`CustType`,`SiteID`,`ItemCode`,`OrderLineNo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_grants`
--
ALTER TABLE `auth_grants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
