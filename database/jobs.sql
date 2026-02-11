-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 09, 2025 at 07:18 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillsync_ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `issuer` varchar(255) DEFAULT NULL,
  `cert_date` date DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certifications`
--

INSERT INTO `certifications` (`id`, `user_id`, `title`, `issuer`, `cert_date`, `url`) VALUES
(1, 1, '', '', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `start_year` varchar(10) DEFAULT NULL,
  `end_year` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `user_id`, `institution`, `degree`, `field_of_study`, `start_year`, `end_year`) VALUES
(2, 1, 'Pillai HOC College of arts commerce and science', 'bachelor\'s  in Data Science', 'Data Science', '2021', '2025');

-- --------------------------------------------------------

--
-- Table structure for table `experience`
--

CREATE TABLE `experience` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experience`
--

INSERT INTO `experience` (`id`, `user_id`, `company`, `position`, `start_date`, `end_date`, `description`) VALUES
(1, 1, '', '', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `working_hours` varchar(50) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `vacancies` int(5) NOT NULL,
  `location` varchar(255) DEFAULT 'Not Specified',
  `type` varchar(100) DEFAULT 'Full-time',
  `salary` varchar(100) DEFAULT 'Negotiable',
  `posted_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `title`, `company`, `position`, `working_hours`, `contact`, `description`, `vacancies`, `location`, `type`, `salary`, `posted_on`) VALUES
(1, 1, 'Frontend Developer', 'TechSoft', 'Software Engineer', '9AM-5PM', '9876543210', 'Build responsive web applications using HTML, CSS, and JavaScript.', 2, 'Mumbai', 'Full-time', '50,000-60,000', '2025-09-09'),
(2, 2, 'Backend Developer', 'CodeBase', 'Backend Lead', '10AM-6PM', '9123456780', 'Develop server-side logic and databases.', 1, 'Pune', 'Full-time', '60,000-70,000', '2025-09-09'),
(3, 3, 'Digital Marketing Executive', 'AdVision', 'Marketing Specialist', '9AM-5PM', '9988776655', 'Plan and execute digital marketing campaigns.', 3, 'Bangalore', 'Full-time', '40,000-50,000', '2025-09-09'),
(4, 4, 'Full Stack Developer', 'Innovatech', 'Software Engineer', '9AM-5PM', '9012345678', 'Work on both frontend and backend applications.', 2, 'Hyderabad', 'Full-time', '55,000-65,000', '2025-09-09'),
(5, 5, 'UI/UX Designer', 'DesignPro', 'Designer', '10AM-6PM', '9123987654', 'Create intuitive and attractive user interfaces.', 1, 'Chennai', 'Full-time', '45,000-50,000', '2025-09-09'),
(6, 6, 'Data Analyst', 'DataCorp', 'Analyst', '9AM-5PM', '9988771122', 'Analyze and interpret complex datasets.', 3, 'Delhi', 'Full-time', '50,000-60,000', '2025-09-09'),
(7, 7, 'DevOps Engineer', 'CloudWorks', 'Engineer', '11AM-7PM', '9876541122', 'Maintain CI/CD pipelines and cloud infrastructure.', 1, 'Bangalore', 'Full-time', '65,000-75,000', '2025-09-09'),
(8, 8, 'Content Writer', 'WriteWell', 'Writer', '9AM-4PM', '9765432109', 'Create engaging content for blogs and social media.', 2, 'Mumbai', 'Part-time', '25,000-35,000', '2025-09-09'),
(9, 9, 'Mobile App Developer', 'AppLab', 'Developer', '10AM-6PM', '9345678910', 'Develop mobile applications for Android and iOS.', 2, 'Pune', 'Full-time', '55,000-65,000', '2025-09-09'),
(10, 10, 'QA Tester', 'SoftTest', 'Tester', '9AM-5PM', '9456123789', 'Perform testing and ensure software quality.', 1, 'Hyderabad', 'Full-time', '40,000-50,000', '2025-09-09'),
(11, 11, 'Product Manager', 'MarketLead', 'Manager', '10AM-7PM', '9876123450', 'Oversee product development and roadmap.', 1, 'Delhi', 'Full-time', '80,000-90,000', '2025-09-09'),
(12, 12, 'SEO Specialist', 'WebBoost', 'Specialist', '9AM-5PM', '9123678945', 'Optimize website content for search engines.', 3, 'Chennai', 'Full-time', '45,000-55,000', '2025-09-09');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `technologies` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `project_name`, `description`, `technologies`, `project_url`) VALUES
(2, 1, 'QUASCO LTD', 'Hardware Netroking', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_complete` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `headline`, `location`, `email`, `phone`, `profile_picture`, `bio`, `profile_complete`, `password`, `created_at`) VALUES
(1, 'Lawrance Johnwilson Nadar', 'Backend', 'Mumbai, India', 'lawrencejohnwilson28624@gmail.com', '9653472942', NULL, 'A motivated and adaptable individual seeking to begin my professional journey with an\r\norganisation that oﬀers growth opportunities, values creativity, and fosters learning. I am\r\neager to apply my skills, discipline, and curiosity to contribute meaningfully to team goals\r\nand company success.', 1, 'Nirav@2005', '2025-09-09 09:06:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `experience`
--
ALTER TABLE `experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `experience_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
