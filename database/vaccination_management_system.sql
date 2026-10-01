-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 01:35 PM
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
-- Database: `vaccination_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(20) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `parent_notes` text DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `booking_code`, `parent_id`, `child_id`, `hospital_id`, `vaccine_id`, `appointment_date`, `appointment_time`, `status`, `parent_notes`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(4, 'EVAC-2026-8804', 6, 3, 2, 4, '2026-09-28', '02:30 PM', 'Approved', 'Routine 6-week immunization.', 'Approved for St. Jude Hub.', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(5, 'EVAC-2026-8805', 7, 4, 3, 1, '2026-10-02', '10:15 AM', 'Pending', 'Requesting BCG shot for baby Zoya.', NULL, '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(6, 'EVAC-2026-A453', 5, 5, 3, 3, '2026-09-18', '10:30 AM', 'Pending', 'baby had fever', NULL, '2026-09-16 10:33:40', '2026-09-16 10:33:40'),
(7, 'EVAC-2026-27C8', 8, 6, 3, 1, '2026-09-18', '10:30 AM', 'Pending', '', NULL, '2026-09-16 10:42:08', '2026-09-16 10:42:08'),
(8, 'EVAC-2026-8EDC', 9, 7, 2, 7, '2026-09-23', '11:30 AM', 'Pending', 'i am good', NULL, '2026-09-21 18:08:08', '2026-09-21 18:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `blood_group` varchar(10) DEFAULT 'Unknown',
  `birth_weight` varchar(20) DEFAULT 'N/A',
  `address` text DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `parent_id`, `name`, `dob`, `gender`, `blood_group`, `birth_weight`, `address`, `medical_notes`, `created_at`, `updated_at`) VALUES
(1, 5, 'Leo Jenkins', '2025-11-15', 'Male', 'O+', '3.4 kg', '84 Maple Street, Apt 3B', 'No known allergies. Healthy delivery.', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(3, 6, 'Lucas Miller', '2025-12-20', 'Male', 'B+', '3.6 kg', '512 Oakwood Boulevard', 'None reported.', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(4, 7, 'Zoya Khan', '2026-02-05', 'Female', 'O-', '3.2 kg', '19 Crescent Gardens', 'Born 2 weeks premature, healthy.', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(5, 5, 'hooriya fatima', '2009-09-13', 'Female', 'A+', '3.2', '2/517', 'bcjjvtroui fyhjfo uwe9 iufiefjwe', '2026-09-16 10:32:48', '2026-09-16 10:36:47'),
(6, 8, 'hooriya fatima', '2009-09-13', 'Female', 'A+', '3.2', '2/517', 'hy', '2026-09-16 10:41:59', '2026-09-16 10:41:59'),
(7, 9, 'hamdan', '2007-02-14', 'Male', 'O-', '3.2', '2/517', 'hamdan kahil wajood', '2026-09-21 18:06:24', '2026-09-21 18:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Unread','Read','Replied') DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Eleanor Vance', 'eleanor.vance@yahoo.com', '+1 (555) 123-9988', 'Inquiry regarding Polio campaign dates', 'Hello, could you please provide the schedule for the upcoming supplementary polio vaccination drive?', 'Read', '2026-09-16 09:55:39'),
(2, 'Robert Hayes', 'robert.hayes@outlook.com', '+1 (555) 654-3210', 'Adding a new clinic branch', 'We are opening a new pediatric wing in Green Valley and would like to register with your e-vaccination portal.', 'Unread', '2026-09-16 09:55:39'),
(3, 'Syed Hamdan', 'whoissyedhamdan@gmail.com', '2798725619476', 'gfveruih', 'jjvoirn', 'Unread', '2026-09-16 10:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hospital_name` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(80) NOT NULL DEFAULT 'City Center',
  `location` varchar(150) DEFAULT NULL,
  `operating_hours` varchar(100) DEFAULT '08:00 AM - 05:00 PM',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `user_id`, `hospital_name`, `image`, `email`, `phone`, `address`, `city`, `location`, `operating_hours`, `status`, `created_at`, `updated_at`) VALUES
(2, 3, 'Green Valley Community Clinic', 'hospital_3_1789986140.jpg', 'info@stjudechildren.org', '+1 (555) 789-0123', '77 Meadow Lane, Green Valley', 'Green Valley', 'Outpatient Department', '09:00 AM - 04:30 PM', 'active', '2026-09-16 09:55:39', '2026-09-21 10:22:20'),
(3, 4, 'Green Valley Community Clinic', 'hospital_4_1789986015.jfif', 'care@greenvalleyclinic.org', '+1 (555) 789-0123', '77 Meadow Lane, Green Valley', 'Green Valley', 'Outpatient Department', '09:00 AM - 04:30 PM', 'active', '2026-09-16 09:55:39', '2026-09-21 10:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_vaccines`
--

CREATE TABLE `hospital_vaccines` (
  `id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_vaccines`
--

INSERT INTO `hospital_vaccines` (`id`, `hospital_id`, `vaccine_id`, `status`, `updated_at`) VALUES
(11, 2, 1, 'Available', '2026-09-16 09:55:39'),
(12, 2, 2, 'Available', '2026-09-16 09:55:39'),
(13, 2, 3, 'Available', '2026-09-16 09:55:39'),
(14, 2, 4, 'Available', '2026-09-16 09:55:39'),
(15, 2, 5, 'Available', '2026-09-16 09:55:39'),
(16, 2, 6, 'Available', '2026-09-16 09:55:39'),
(17, 2, 7, 'Available', '2026-09-16 09:55:39'),
(18, 2, 8, 'Available', '2026-09-16 09:55:39'),
(19, 2, 9, 'Available', '2026-09-16 09:55:39'),
(20, 2, 10, 'Available', '2026-09-16 09:55:39'),
(21, 3, 1, 'Available', '2026-09-16 09:55:39'),
(22, 3, 2, 'Available', '2026-09-16 09:55:39'),
(23, 3, 3, 'Available', '2026-09-16 09:55:39'),
(24, 3, 4, 'Available', '2026-09-16 09:55:39'),
(25, 3, 5, 'Unavailable', '2026-09-16 09:55:39'),
(26, 3, 6, 'Available', '2026-09-16 09:55:39'),
(27, 3, 7, 'Available', '2026-09-16 09:55:39'),
(28, 3, 8, 'Available', '2026-09-16 09:55:39'),
(29, 3, 9, 'Unavailable', '2026-09-16 09:55:39'),
(30, 3, 10, 'Available', '2026-09-16 09:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','parent','hospital') NOT NULL DEFAULT 'parent',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@evaccine.com', '+1 (555) 019-2834', '$2y$10$4.Y3W96b.y4SxNU0ZOSh5.oSuMfktvJjH2FDIaed4g74q93rqf.1G', 'admin', 'active', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(3, 'Green Valley Community Clinic', 'info@stjudechildren.org', '+1 (555) 789-0123', '$2y$10$ofeBEZ.MHX3Qqs0XI2gbrOx/6js57iJrSVnbbOJTRaEu6LdHKUHy2', 'hospital', 'active', '2026-09-16 09:55:39', '2026-09-21 10:22:20'),
(4, 'Green Valley Community Clinic', 'care@greenvalleyclinic.org', '+1 (555) 789-0123', '$2y$10$ofeBEZ.MHX3Qqs0XI2gbrOx/6js57iJrSVnbbOJTRaEu6LdHKUHy2', 'hospital', 'active', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(5, 'Sarah Jenkins', 'sarah.jenkins@gmail.com', '+1 (555) 890-1234', '$2y$10$hSbpRc0DaLqBqTcG1UA42.oG11W4jDTWR8P2WDQQbmhKgMaPxC0o6', 'parent', 'active', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(6, 'David Miller', 'david.miller@gmail.com', '+1 (555) 901-2345', '$2y$10$hSbpRc0DaLqBqTcG1UA42.oG11W4jDTWR8P2WDQQbmhKgMaPxC0o6', 'parent', 'active', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(7, 'Amina Khan', 'amina.khan@gmail.com', '+1 (555) 912-3456', '$2y$10$hSbpRc0DaLqBqTcG1UA42.oG11W4jDTWR8P2WDQQbmhKgMaPxC0o6', 'parent', 'active', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(8, 'hooriya', 'hooriya@gmail.com', '24357865478', '$2y$10$UcHx0FgvokLDagMoTF3mIOUOqqZiMRH8ppeT.E3v6.5/e1G9uJNva', 'parent', 'active', '2026-09-16 10:01:24', '2026-09-16 10:01:24'),
(9, 'ramsha fatima', 'ramsha@gmail.com', '24357865478', '$2y$10$NowpTco/nWej7I7/4SXha.wvelDj3/pnYGNjzXkRnGcCFG5m3sA9C', 'parent', 'active', '2026-09-21 18:02:46', '2026-09-21 18:02:46');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_records`
--

CREATE TABLE `vaccination_records` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `child_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `dose_number` int(11) NOT NULL DEFAULT 1,
  `vaccination_date` date NOT NULL,
  `status` enum('Vaccinated','Not Vaccinated') NOT NULL DEFAULT 'Vaccinated',
  `batch_number` varchar(60) DEFAULT NULL,
  `administered_by` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `certificate_code` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL,
  `vaccine_name` varchar(150) NOT NULL,
  `short_code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `target_disease` varchar(200) NOT NULL,
  `recommended_age` varchar(100) NOT NULL,
  `doses_required` int(11) NOT NULL DEFAULT 1,
  `interval_days` int(11) NOT NULL DEFAULT 0,
  `status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vaccines`
--

INSERT INTO `vaccines` (`id`, `vaccine_name`, `short_code`, `description`, `target_disease`, `recommended_age`, `doses_required`, `interval_days`, `status`, `created_at`, `updated_at`) VALUES
(1, 'BCG (Bacillus Calmette-Guérin)', 'BCG', 'Protects infants against severe forms of childhood tuberculosis including tubercular meningitis.', 'Tuberculosis (TB)', 'At Birth', 1, 0, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(2, 'Hepatitis B (Birth Dose)', 'HepB-0', 'First dose to prevent perinatal transmission of Hepatitis B infection.', 'Hepatitis B Virus', 'At Birth (within 24 hrs)', 1, 0, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(3, 'Oral Polio Vaccine (OPV-0)', 'OPV-0', 'Initial oral dose to build mucosal immunity against poliovirus strains.', 'Poliomyelitis (Polio)', 'At Birth', 1, 0, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(4, 'Pentavalent Vaccine (DTP-HepB-Hib)', 'PENTA-1', '5-in-1 combo protecting against Diphtheria, Tetanus, Pertussis, Hepatitis B, and Haemophilus Influenzae type b.', 'Diphtheria, Tetanus, Whooping Cough, HepB, Hib', '6 Weeks', 3, 28, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(5, 'Rotavirus Vaccine (Rotavac)', 'ROTA-1', 'Oral vaccine protecting infants against severe dehydrating diarrhea caused by rotavirus.', 'Rotavirus Diarrhea', '6 Weeks', 3, 28, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(6, 'Pneumococcal Conjugate Vaccine (PCV)', 'PCV-1', 'Protects against Streptococcus pneumoniae causing pneumonia, sepsis, and ear infections.', 'Pneumonia & Meningitis', '6 Weeks', 3, 28, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(7, 'Inactivated Polio Vaccine (IPV)', 'IPV-1', 'Injectable fractional or full dose to strengthen immunity against poliovirus.', 'Poliomyelitis', '6 & 14 Weeks', 2, 56, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(8, 'Measles & Rubella (MR-1)', 'MR-1', 'First dose protecting children against measles rash fever and congenital rubella syndrome.', 'Measles & Rubella', '9 Months', 2, 180, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(9, 'DTP Booster-1', 'DTP-B1', 'First booster shot to maintain high antibody titers against Diphtheria, Tetanus, and Pertussis.', 'Diphtheria, Tetanus, Pertussis', '16-24 Months', 1, 0, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39'),
(10, 'Typhoid Conjugate Vaccine (TCV)', 'TCV', 'Single dose providing lasting immunity against Salmonella typhi infections.', 'Typhoid Fever', '9-12 Months', 1, 0, 'Available', '2026-09-16 09:55:39', '2026-09-16 09:55:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_app_parent` (`parent_id`),
  ADD KEY `fk_app_child` (`child_id`),
  ADD KEY `fk_app_hospital` (`hospital_id`),
  ADD KEY `fk_app_vaccine` (`vaccine_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_children_parent` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hospitals_user` (`user_id`);

--
-- Indexes for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hospital_vaccine` (`hospital_id`,`vaccine_id`),
  ADD KEY `fk_hv_vaccine` (`vaccine_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD KEY `fk_rec_appointment` (`appointment_id`),
  ADD KEY `fk_rec_child` (`child_id`),
  ADD KEY `fk_rec_vaccine` (`vaccine_id`),
  ADD KEY `fk_rec_hospital` (`hospital_id`);

--
-- Indexes for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_app_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_vaccine` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `fk_children_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD CONSTRAINT `fk_hospitals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD CONSTRAINT `fk_hv_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hv_vaccine` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD CONSTRAINT `fk_rec_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rec_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rec_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rec_vaccine` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
