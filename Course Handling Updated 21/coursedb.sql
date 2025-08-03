-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 03:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `academic`
--

DROP TABLE IF EXISTS `academic`;
CREATE TABLE `academic` (
  `AcademicID` int(11) NOT NULL,
  `AppealID` int(11) DEFAULT NULL,
  `NoteActionTaken` text DEFAULT NULL,
  `OOA_SignatureAndStamp` varchar(100) DEFAULT NULL,
  `OOA_CommentDate` date DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic`
--

INSERT INTO `academic` (`AcademicID`, `AppealID`, `NoteActionTaken`, `OOA_SignatureAndStamp`, `OOA_CommentDate`, `username`, `password`, `Status`) VALUES
(1, NULL, NULL, NULL, NULL, 'acadmin', 'academic23', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `addcourse`
--

DROP TABLE IF EXISTS `addcourse`;
CREATE TABLE `addcourse` (
  `AddCourseID` int(11) NOT NULL,
  `AppealID` int(11) DEFAULT NULL,
  `CourseCode` varchar(10) DEFAULT NULL,
  `Group` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Table structure for table `appeal`
--

DROP TABLE IF EXISTS `appeal`;
CREATE TABLE `appeal` (
  `AppealID` int(11) NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `Problem` text DEFAULT NULL,
  `Request` text DEFAULT NULL,
  `Signature` varchar(100) DEFAULT NULL,
  `AppealDate` date DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `CourseCode` varchar(10) NOT NULL,
  `GroupNumber` int(11) DEFAULT 1,
  `CourseName` varchar(100) DEFAULT NULL,
  `credit` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`CourseCode`, `GroupNumber`, `CourseName`, `credit`) VALUES
('COURSE101', 2, 'Fundamentals of Computing', 3),
('COURSE102', 2, 'Discrete Mathematics', 3),
('COURSE103', 2, 'Algorithms and Complexity', 3),
('COURSE104', 2, 'Human Computer Interaction', 3),
('COURSE105', 2, 'Cloud Computing', 3),
('COURSE106', 2, 'Big Data Analytics', 3),
('COURSE107', 2, 'Machine Learning', 3),
('COURSE108', 2, 'Game Development', 3),
('COURSE109', 2, 'Digital Logic Design', 3),
('COURSE110', 2, 'Data Visualization', 3),
('CSC101', 2, 'Introduction to Computer Science', 3),
('CSC102', 2, 'Data Structures', 3),
('CSC103', 2, 'Database Systems', 3),
('CSC104', 2, 'Web Programming', 3),
('CSC105', 2, 'Operating Systems', 4),
('CSC106', 2, 'Computer Networks', 3),
('CSC107', 2, 'Software Engineering', 3),
('CSC108', 2, 'Artificial Intelligence', 3),
('CSC109', 2, 'Mobile App Development', 3),
('CSC110', 2, 'Cyber Security Fundamentals', 3),
('CSC574', 2, 'Web Application', 3);

-- --------------------------------------------------------

--
-- Table structure for table `dropcourse`
--

DROP TABLE IF EXISTS `dropcourse`;
CREATE TABLE `dropcourse` (
  `DropCourseID` int(11) NOT NULL,
  `AppealID` int(11) DEFAULT NULL,
  `CourseCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

DROP TABLE IF EXISTS `faculty`;
CREATE TABLE `faculty` (
  `FacultyID` int(11) NOT NULL,
  `AppealID` int(11) DEFAULT NULL,
  `Comment` text DEFAULT NULL,
  `SuggestedAction` text DEFAULT NULL,
  `OOF_Signature` varchar(100) DEFAULT NULL,
  `OOF_CommentDate` date DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`FacultyID`, `AppealID`, `Comment`, `SuggestedAction`, `OOF_Signature`, `OOF_CommentDate`, `username`, `password`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, 'kppim', 'kppim123');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL,
  `StudentName` varchar(100) DEFAULT NULL,
  `Faculty` varchar(100) DEFAULT NULL,
  `ProgramCode` varchar(50) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Campus` varchar(100) DEFAULT NULL,
  `GraduatingStatus` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `studentcourse`
--

DROP TABLE IF EXISTS `studentcourse`;
CREATE TABLE `studentcourse` (
  `StudentID` int(11) NOT NULL,
  `CourseCode` varchar(10) NOT NULL,
  `Credit` int(11) DEFAULT NULL,
  `Group` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic`
--
ALTER TABLE `academic`
  ADD PRIMARY KEY (`AcademicID`),
  ADD KEY `AppealID` (`AppealID`);

--
-- Indexes for table `addcourse`
--
ALTER TABLE `addcourse`
  ADD PRIMARY KEY (`AddCourseID`),
  ADD KEY `AppealID` (`AppealID`),
  ADD KEY `CourseCode` (`CourseCode`);

--
-- Indexes for table `appeal`
--
ALTER TABLE `appeal`
  ADD PRIMARY KEY (`AppealID`),
  ADD KEY `StudentID` (`StudentID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`CourseCode`);

--
-- Indexes for table `dropcourse`
--
ALTER TABLE `dropcourse`
  ADD PRIMARY KEY (`DropCourseID`),
  ADD KEY `AppealID` (`AppealID`),
  ADD KEY `CourseCode` (`CourseCode`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`FacultyID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `AppealID` (`AppealID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudentID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `studentcourse`
--
ALTER TABLE `studentcourse`
  ADD PRIMARY KEY (`StudentID`,`CourseCode`),
  ADD KEY `CourseCode` (`CourseCode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic`
--
ALTER TABLE `academic`
  MODIFY `AcademicID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `addcourse`
--
ALTER TABLE `addcourse`
  MODIFY `AddCourseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `appeal`
--
ALTER TABLE `appeal`
  MODIFY `AppealID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `dropcourse`
--
ALTER TABLE `dropcourse`
  MODIFY `DropCourseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `FacultyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `StudentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic`
--
-- Constraints for table `academic`
ALTER TABLE `academic`
  ADD CONSTRAINT `academic_ibfk_1` FOREIGN KEY (`AppealID`) REFERENCES `appeal` (`AppealID`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- Constraints for table `addcourse`
ALTER TABLE `addcourse`
  ADD CONSTRAINT `addcourse_ibfk_1` FOREIGN KEY (`AppealID`) REFERENCES `appeal` (`AppealID`)
  ON UPDATE CASCADE ON DELETE CASCADE,
  ADD CONSTRAINT `addcourse_ibfk_2` FOREIGN KEY (`CourseCode`) REFERENCES `course` (`CourseCode`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- Constraints for table `appeal`
ALTER TABLE `appeal`
  ADD CONSTRAINT `appeal_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- Constraints for table `dropcourse`
ALTER TABLE `dropcourse`
  ADD CONSTRAINT `dropcourse_ibfk_1` FOREIGN KEY (`AppealID`) REFERENCES `appeal` (`AppealID`)
  ON UPDATE CASCADE ON DELETE CASCADE,
  ADD CONSTRAINT `dropcourse_ibfk_2` FOREIGN KEY (`CourseCode`) REFERENCES `course` (`CourseCode`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- Constraints for table `faculty`
ALTER TABLE `faculty`
  ADD CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`AppealID`) REFERENCES `appeal` (`AppealID`)
  ON UPDATE CASCADE ON DELETE CASCADE;

-- Constraints for table `studentcourse`
ALTER TABLE `studentcourse`
  ADD CONSTRAINT `studentcourse_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`)
  ON UPDATE CASCADE ON DELETE CASCADE,
  ADD CONSTRAINT `studentcourse_ibfk_2` FOREIGN KEY (`CourseCode`) REFERENCES `course` (`CourseCode`)
  ON UPDATE CASCADE ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
