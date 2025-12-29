-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 29, 2025 at 09:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventorybillingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `book_cost_history`
--

CREATE TABLE `book_cost_history` (
  `Cost_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Material_Cost` decimal(10,2) DEFAULT NULL,
  `Labour_Cost` decimal(10,2) DEFAULT NULL,
  `Extra_Cost` decimal(10,2) DEFAULT NULL,
  `Profit_Percentage` decimal(5,2) DEFAULT NULL,
  `Final_Price` decimal(10,2) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_cost_items`
--

CREATE TABLE `book_cost_items` (
  `Item_ID` int(11) NOT NULL,
  `Sheet_ID` int(11) NOT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Equipment` varchar(150) DEFAULT NULL,
  `Cost` decimal(10,2) DEFAULT NULL,
  `Quantity` decimal(10,2) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_cost_items`
--

INSERT INTO `book_cost_items` (`Item_ID`, `Sheet_ID`, `Category`, `Equipment`, `Cost`, `Quantity`, `Amount`) VALUES
(1, 1, 'Page type', 'Ivory Photo Paper', 3.00, 2.00, 6.00),
(2, 2, 'Page Type', 'Ivory Photo Paper', 5.00, 3.00, 15.00),
(3, 2, 'Print Cost', 'Heavy', 3.00, 3.00, 9.00),
(4, 2, 'Print Cost', 'Heavy Double Side', 19.00, 3.00, 57.00);

-- --------------------------------------------------------

--
-- Table structure for table `book_cost_records`
--

CREATE TABLE `book_cost_records` (
  `Cost_ID` int(11) NOT NULL,
  `Book_Name` varchar(150) NOT NULL,
  `Paper_Cost` decimal(10,2) DEFAULT NULL,
  `Ink_Cost` decimal(10,2) DEFAULT NULL,
  `Binding_Cost` decimal(10,2) DEFAULT NULL,
  `Labour_Cost` decimal(10,2) DEFAULT NULL,
  `Other_Cost` decimal(10,2) DEFAULT NULL,
  `Total_Cost` decimal(10,2) DEFAULT NULL,
  `Record_Month` int(11) DEFAULT NULL,
  `Record_Year` int(11) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_cost_sheets`
--

CREATE TABLE `book_cost_sheets` (
  `Sheet_ID` int(11) NOT NULL,
  `Book_Name` varchar(150) NOT NULL,
  `Month` int(11) NOT NULL,
  `Year` int(11) NOT NULL,
  `Grand_Total` decimal(10,2) NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_cost_sheets`
--

INSERT INTO `book_cost_sheets` (`Sheet_ID`, `Book_Name`, `Month`, `Year`, `Grand_Total`, `Created_At`) VALUES
(1, 'Boat Matrix', 12, 2025, 0.00, '2025-12-27 13:42:11'),
(2, 'Boat matrix', 12, 2025, 81.00, '2025-12-29 20:06:29');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`Category_ID`, `Category_Name`, `Created_At`) VALUES
(1, 'Toys', '2025-12-23 13:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `cost_sheets`
--

CREATE TABLE `cost_sheets` (
  `id` int(11) NOT NULL,
  `sheet_name` varchar(100) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_ID`, `Name`, `Phone`, `Email`, `Address`, `City`) VALUES
(7, 'aditya', '123478965', 'aditya@gmail.com', '1', 'Pune'),
(14, 'Ravi Kumar ', '9876543210', 'ravi@example.com', '12, MG Road', 'Bengaluru'),
(15, 'Priya ', '9988776655', 'priya@elect.in', '45, Linking Road', 'Mumbai'),
(16, 'Sunil ', '9000111222', 'sunil@store.com', '7A, Nehru Place', 'Delhi'),
(17, 'Apex ', '9234567890', 'apex@sol.net', '101, Phase 3', 'Pune');

-- --------------------------------------------------------

--
-- Table structure for table `daily_production`
--

CREATE TABLE `daily_production` (
  `Production_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Production_Date` date NOT NULL,
  `Notes` varchar(255) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_production`
--

INSERT INTO `daily_production` (`Production_ID`, `Product_ID`, `Quantity`, `Production_Date`, `Notes`, `Created_At`) VALUES
(3, 3, 2, '2025-12-25', NULL, '2025-12-25 12:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `demand_forecast`
--

CREATE TABLE `demand_forecast` (
  `Forecast_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Forecast_Month` date NOT NULL,
  `Predicted_Demand` int(11) NOT NULL,
  `Forecast_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Employee_ID` int(11) NOT NULL,
  `Employee_Name` varchar(100) NOT NULL,
  `Hourly_Rate` decimal(8,2) NOT NULL DEFAULT 100.00,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`Employee_ID`, `Employee_Name`, `Hourly_Rate`, `Status`, `Created_At`) VALUES
(1, 'Nama', 35.00, 'Active', '2025-12-21 21:17:57'),
(2, 'Snehal', 37.00, 'Active', '2025-12-21 21:17:57'),
(3, 'Shraddha', 35.00, 'Active', '2025-12-21 21:17:57'),
(4, 'Medha', 35.00, 'Active', '2025-12-27 13:45:39');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `Attendance_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Attendance_Date` date NOT NULL,
  `Entry_Time` time NOT NULL,
  `Exit_Time` time DEFAULT NULL,
  `Working_Hours` decimal(5,2) DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `Recorded_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`Attendance_ID`, `Employee_ID`, `Attendance_Date`, `Entry_Time`, `Exit_Time`, `Working_Hours`, `Remarks`, `Recorded_At`) VALUES
(8, 2, '2025-12-27', '11:12:00', '16:10:00', 4.97, '', '2025-12-27 13:46:52'),
(9, 1, '2025-12-27', '11:00:00', '15:00:00', 4.00, '', '2025-12-27 13:47:34'),
(10, 3, '2025-12-27', '00:00:00', '00:00:00', 0.00, 'absent', '2025-12-27 13:48:58'),
(11, 3, '2025-12-29', '13:56:00', '14:56:00', 1.00, '', '2025-12-29 12:57:15');

--
-- Triggers `employee_attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_calc_hours_insert` BEFORE INSERT ON `employee_attendance` FOR EACH ROW BEGIN
    IF NEW.Exit_Time IS NOT NULL THEN
        IF NEW.Exit_Time >= NEW.Entry_Time THEN
            SET NEW.Working_Hours =
            TIME_TO_SEC(TIMEDIFF(NEW.Exit_Time, NEW.Entry_Time)) / 3600;
        ELSE
            SET NEW.Working_Hours =
            (TIME_TO_SEC(TIMEDIFF('24:00:00', NEW.Entry_Time)) +
             TIME_TO_SEC(TIMEDIFF(NEW.Exit_Time, '00:00:00'))) / 3600;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calc_hours_update` BEFORE UPDATE ON `employee_attendance` FOR EACH ROW BEGIN
    IF NEW.Exit_Time IS NOT NULL THEN
        IF NEW.Exit_Time >= NEW.Entry_Time THEN
            SET NEW.Working_Hours =
            TIME_TO_SEC(TIMEDIFF(NEW.Exit_Time, NEW.Entry_Time)) / 3600;
        ELSE
            SET NEW.Working_Hours =
            (TIME_TO_SEC(TIMEDIFF('24:00:00', NEW.Entry_Time)) +
             TIME_TO_SEC(TIMEDIFF(NEW.Exit_Time, '00:00:00'))) / 3600;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `finished_products`
--

CREATE TABLE `finished_products` (
  `Product_ID` int(11) NOT NULL,
  `Product_Code` varchar(50) DEFAULT NULL,
  `Product_Name` varchar(100) NOT NULL,
  `Item_Code` varchar(50) DEFAULT NULL,
  `Category` enum('Book','Toy') NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Category_ID` int(11) NOT NULL,
  `Min_Level` int(11) NOT NULL DEFAULT 5,
  `Min_Required` int(11) NOT NULL DEFAULT 5,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_products`
--

INSERT INTO `finished_products` (`Product_ID`, `Product_Code`, `Product_Name`, `Item_Code`, `Category`, `Quantity`, `Price`, `Created_At`, `Category_ID`, `Min_Level`, `Min_Required`, `is_active`) VALUES
(3, NULL, 'Book 1', NULL, 'Book', 1, 100.00, '2025-12-25 12:51:05', 1, 5, 5, 0),
(6, '001', 'Beginning Sound Part 1', NULL, 'Book', 2, 750.00, '2025-12-28 10:41:22', 13, 5, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `Invoice_ID` int(11) NOT NULL,
  `customer_ID` int(11) DEFAULT NULL,
  `Date` date NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`Invoice_ID`, `customer_ID`, `Date`, `TotalAmount`) VALUES
(1, 7, '2025-10-13', 0.00),
(2, NULL, '2025-09-10', 3549.00),
(4, NULL, '2025-10-13', 110.00),
(5, 15, '2025-11-30', 2975.00),
(6, 7, '2025-11-30', 85.00),
(7, 7, '2025-12-01', 110.00),
(8, 16, '2025-12-21', 199.00),
(9, 15, '2025-12-21', 350.00);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `Invoice_ID` int(11) NOT NULL,
  `Customer_Name` varchar(150) NOT NULL,
  `Invoice_Date` date NOT NULL,
  `Grand_Total` decimal(10,2) NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`Invoice_ID`, `Customer_Name`, `Invoice_Date`, `Grand_Total`, `Created_At`) VALUES
(1, 'Advay', '2025-12-27', 100.00, '2025-12-27 12:32:11'),
(2, 'Dr Sampada', '2025-12-28', 750.00, '2025-12-28 10:42:11'),
(3, 'Advay', '2025-12-28', 100.00, '2025-12-28 12:42:02'),
(4, 'Advay', '2025-12-28', 750.00, '2025-12-28 13:20:04'),
(5, 'Dr Sampada', '2025-12-29', 750.00, '2025-12-29 12:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_details`
--

CREATE TABLE `invoice_details` (
  `InvoiceDetail_ID` int(11) NOT NULL,
  `Invoice_ID` int(11) DEFAULT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL,
  `Rate` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_details`
--

INSERT INTO `invoice_details` (`InvoiceDetail_ID`, `Invoice_ID`, `Product_ID`, `Quantity`, `Rate`, `Subtotal`) VALUES
(2, 5, 5, 35, 85.00, 2975.00),
(3, 6, 5, 1, 85.00, 85.00),
(4, 7, 4, 1, 110.00, 110.00),
(5, 8, 3, 1, 199.00, 199.00),
(6, 9, 1, 1, 350.00, 350.00);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `Item_ID` int(11) NOT NULL,
  `Invoice_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`Item_ID`, `Invoice_ID`, `Product_ID`, `Quantity`, `Price`, `Total`) VALUES
(1, 1, 3, 1, 100.00, 100.00),
(2, 2, 6, 1, 750.00, 750.00),
(3, 3, 3, 1, 100.00, 100.00),
(4, 4, 6, 1, 750.00, 750.00),
(5, 5, 6, 1, 750.00, 750.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_requirements`
--

CREATE TABLE `production_requirements` (
  `Req_ID` int(11) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Required_Qty` int(11) DEFAULT NULL,
  `Order_ID` int(11) DEFAULT NULL,
  `Status` enum('Pending','Completed') DEFAULT 'Pending',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_requirements`
--

INSERT INTO `production_requirements` (`Req_ID`, `Product_ID`, `Required_Qty`, `Order_ID`, `Status`, `Created_At`) VALUES
(1, 6, 1, 4, 'Completed', '2025-12-28 13:20:04'),
(2, 6, 1, 5, 'Completed', '2025-12-29 12:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Product_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `HsnCode` varchar(20) DEFAULT NULL,
  `Unit` varchar(10) DEFAULT NULL,
  `Stock_Quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`Product_ID`, `Name`, `Price`, `HsnCode`, `Unit`, `Stock_Quantity`) VALUES
(1, 'A4 Printer Paper (Rim)', 350.00, '4802', 'RIM', 44),
(3, 'USB-C Cable (3M)', 199.00, '8544', 'PCS', 80),
(4, 'LED Bulb (12W)', 110.00, '8539', 'PCS', 309),
(5, 'Notebook Dairy', 85.00, '4820', 'PCS', 4);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`Category_ID`, `Category_Name`) VALUES
(1, 'Book'),
(13, 'Velcro Books');

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `Purchase_ID` int(11) NOT NULL,
  `Supplier_ID` int(11) DEFAULT NULL,
  `Date` date NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`Purchase_ID`, `Supplier_ID`, `Date`, `TotalAmount`) VALUES
(1, 7, '2025-12-01', 600.00),
(2, 7, '2025-12-01', 6000.00),
(3, 1, '2025-12-20', 80.00),
(4, 1, '2025-12-21', 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `PurchaseDetail_ID` int(11) NOT NULL,
  `Purchase_ID` int(11) DEFAULT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL,
  `Rate` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_details`
--

INSERT INTO `purchase_details` (`PurchaseDetail_ID`, `Purchase_ID`, `Product_ID`, `Quantity`, `Rate`, `Subtotal`) VALUES
(1, 1, 4, 10, 60.00, 600.00),
(2, 2, 4, 100, 60.00, 6000.00),
(3, 3, 3, 1, 80.00, 80.00),
(4, 4, 4, 1, 60.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `Material_ID` int(11) NOT NULL,
  `Material_Name` varchar(100) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `Minimum_Required` int(11) NOT NULL DEFAULT 1,
  `Unit` varchar(50) DEFAULT 'units',
  `Min_Required` int(11) NOT NULL DEFAULT 0,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Min_Level` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`Material_ID`, `Material_Name`, `Quantity`, `Minimum_Required`, `Unit`, `Min_Required`, `Created_At`, `Min_Level`) VALUES
(5, 'Paper Sheets', 498, 100, 'units', 0, '2025-12-22 19:25:09', 10),
(6, 'Ink Bottles', 8, 3, 'units', 0, '2025-12-22 19:25:09', 10),
(8, 'A4 blue lamination sheet', 0, 1, 'units', 1, '2025-12-23 13:10:44', 10),
(9, 'Bubble wrap', 6, 1, 'units', 5, '2025-12-25 14:20:09', 10),
(10, 'A4 Red Lamination sheets', 3, 1, 'units', 1, '2025-12-27 13:33:04', 10);

-- --------------------------------------------------------

--
-- Table structure for table `raw_material_usage`
--

CREATE TABLE `raw_material_usage` (
  `Usage_ID` int(11) NOT NULL,
  `Material_ID` int(11) NOT NULL,
  `Used_Quantity` int(11) NOT NULL,
  `Usage_Date` date NOT NULL,
  `Notes` varchar(255) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_material_usage`
--

INSERT INTO `raw_material_usage` (`Usage_ID`, `Material_ID`, `Used_Quantity`, `Usage_Date`, `Notes`, `Created_At`) VALUES
(1, 8, 1, '2025-12-25', '', '2025-12-25 13:41:35'),
(2, 9, 2, '2025-12-25', '', '2025-12-25 14:20:38'),
(3, 8, 2, '2025-12-27', '', '2025-12-27 13:50:52'),
(4, 10, 1, '2025-12-27', 'snehal', '2025-12-27 14:40:31');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment`
--

CREATE TABLE `stock_adjustment` (
  `Adjustment_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Adjustment_Quantity` int(11) NOT NULL,
  `Reason` varchar(255) NOT NULL,
  `Notes` text DEFAULT NULL,
  `Recorded_By` varchar(100) DEFAULT 'System/Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `Supplier_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`Supplier_ID`, `Name`, `Phone`, `Email`) VALUES
(1, 'ABC', '123456789', 'test123@gmail.com'),
(7, 'Alpha Wholesale', '8000123456', 'alpha@wholesale.com'),
(8, 'Gama Tech Imports', '8899001122', 'gama@tech.com');

-- --------------------------------------------------------

--
-- Table structure for table `supplies_ledger`
--

CREATE TABLE `supplies_ledger` (
  `Product_ID` int(11) NOT NULL,
  `Supplier_ID` int(11) NOT NULL,
  `Supply_Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'Cashier'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Username`, `Password`, `Role`) VALUES
(1, 'admin', '$2y$10$OVbtCcmAENu4kP9.eh0tIOwmm4lek/Cf.p242cb7dqRNKqvGI/.h6', 'Admin'),
(2, 'TEST', '$2y$10$2jo2W2J8TN05U5pLLf/eBu4fs4p4dvIc3VJG5XuZG7ytQ5i9VnLB6', 'Manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book_cost_history`
--
ALTER TABLE `book_cost_history`
  ADD PRIMARY KEY (`Cost_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `book_cost_items`
--
ALTER TABLE `book_cost_items`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `Sheet_ID` (`Sheet_ID`);

--
-- Indexes for table `book_cost_records`
--
ALTER TABLE `book_cost_records`
  ADD PRIMARY KEY (`Cost_ID`);

--
-- Indexes for table `book_cost_sheets`
--
ALTER TABLE `book_cost_sheets`
  ADD PRIMARY KEY (`Sheet_ID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `cost_sheets`
--
ALTER TABLE `cost_sheets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_ID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_customer_name` (`Name`);

--
-- Indexes for table `daily_production`
--
ALTER TABLE `daily_production`
  ADD PRIMARY KEY (`Production_ID`),
  ADD KEY `fk_daily_product` (`Product_ID`);

--
-- Indexes for table `demand_forecast`
--
ALTER TABLE `demand_forecast`
  ADD PRIMARY KEY (`Forecast_ID`),
  ADD UNIQUE KEY `unique_forecast` (`Product_ID`,`Forecast_Month`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`Employee_ID`),
  ADD UNIQUE KEY `Employee_Name` (`Employee_Name`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`Attendance_ID`),
  ADD UNIQUE KEY `unique_employee_day` (`Employee_ID`,`Attendance_Date`);

--
-- Indexes for table `finished_products`
--
ALTER TABLE `finished_products`
  ADD PRIMARY KEY (`Product_ID`),
  ADD UNIQUE KEY `Item_Code` (`Item_Code`),
  ADD KEY `fk_finished_products_category` (`Category_ID`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`Invoice_ID`),
  ADD KEY `customer_ID` (`customer_ID`),
  ADD KEY `idx_invoice_date` (`Date`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`Invoice_ID`);

--
-- Indexes for table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`InvoiceDetail_ID`),
  ADD KEY `Invoice_ID` (`Invoice_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `Invoice_ID` (`Invoice_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `production_requirements`
--
ALTER TABLE `production_requirements`
  ADD PRIMARY KEY (`Req_ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `idx_product_name` (`Name`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`Purchase_ID`),
  ADD KEY `Supplier_ID` (`Supplier_ID`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`PurchaseDetail_ID`),
  ADD KEY `Purchase_ID` (`Purchase_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`Material_ID`);

--
-- Indexes for table `raw_material_usage`
--
ALTER TABLE `raw_material_usage`
  ADD PRIMARY KEY (`Usage_ID`),
  ADD KEY `Material_ID` (`Material_ID`);

--
-- Indexes for table `stock_adjustment`
--
ALTER TABLE `stock_adjustment`
  ADD PRIMARY KEY (`Adjustment_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`Supplier_ID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `supplies_ledger`
--
ALTER TABLE `supplies_ledger`
  ADD PRIMARY KEY (`Product_ID`,`Supplier_ID`),
  ADD KEY `Supplier_ID` (`Supplier_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book_cost_history`
--
ALTER TABLE `book_cost_history`
  MODIFY `Cost_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `book_cost_items`
--
ALTER TABLE `book_cost_items`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `book_cost_records`
--
ALTER TABLE `book_cost_records`
  MODIFY `Cost_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `book_cost_sheets`
--
ALTER TABLE `book_cost_sheets`
  MODIFY `Sheet_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cost_sheets`
--
ALTER TABLE `cost_sheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `daily_production`
--
ALTER TABLE `daily_production`
  MODIFY `Production_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `demand_forecast`
--
ALTER TABLE `demand_forecast`
  MODIFY `Forecast_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `Employee_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `Attendance_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `finished_products`
--
ALTER TABLE `finished_products`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoice_details`
--
ALTER TABLE `invoice_details`
  MODIFY `InvoiceDetail_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `production_requirements`
--
ALTER TABLE `production_requirements`
  MODIFY `Req_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `Purchase_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `PurchaseDetail_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `Material_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `raw_material_usage`
--
ALTER TABLE `raw_material_usage`
  MODIFY `Usage_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_adjustment`
--
ALTER TABLE `stock_adjustment`
  MODIFY `Adjustment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `Supplier_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_cost_history`
--
ALTER TABLE `book_cost_history`
  ADD CONSTRAINT `book_cost_history_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `finished_products` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `book_cost_items`
--
ALTER TABLE `book_cost_items`
  ADD CONSTRAINT `book_cost_items_ibfk_1` FOREIGN KEY (`Sheet_ID`) REFERENCES `book_cost_sheets` (`Sheet_ID`);

--
-- Constraints for table `daily_production`
--
ALTER TABLE `daily_production`
  ADD CONSTRAINT `daily_production_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `finished_products` (`Product_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_daily_product` FOREIGN KEY (`Product_ID`) REFERENCES `finished_products` (`Product_ID`);

--
-- Constraints for table `demand_forecast`
--
ALTER TABLE `demand_forecast`
  ADD CONSTRAINT `demand_forecast_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`Employee_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_attendance` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`Employee_ID`) ON DELETE CASCADE;

--
-- Constraints for table `finished_products`
--
ALTER TABLE `finished_products`
  ADD CONSTRAINT `fk_finished_products_category` FOREIGN KEY (`Category_ID`) REFERENCES `product_categories` (`Category_ID`);

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`customer_ID`) REFERENCES `customer` (`customer_ID`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`Invoice_ID`) REFERENCES `invoice` (`Invoice_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_details_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`Invoice_ID`) REFERENCES `invoices` (`Invoice_ID`),
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `finished_products` (`Product_ID`);

--
-- Constraints for table `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`Supplier_ID`) REFERENCES `supplier` (`Supplier_ID`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`Purchase_ID`) REFERENCES `purchase` (`Purchase_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`);

--
-- Constraints for table `raw_material_usage`
--
ALTER TABLE `raw_material_usage`
  ADD CONSTRAINT `raw_material_usage_ibfk_1` FOREIGN KEY (`Material_ID`) REFERENCES `raw_materials` (`Material_ID`);

--
-- Constraints for table `stock_adjustment`
--
ALTER TABLE `stock_adjustment`
  ADD CONSTRAINT `stock_adjustment_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`);

--
-- Constraints for table `supplies_ledger`
--
ALTER TABLE `supplies_ledger`
  ADD CONSTRAINT `supplies_ledger_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplies_ledger_ibfk_2` FOREIGN KEY (`Supplier_ID`) REFERENCES `supplier` (`Supplier_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
