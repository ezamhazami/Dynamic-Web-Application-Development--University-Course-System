CREATE TABLE Student (
  StudentID INT AUTO_INCREMENT PRIMARY KEY,
  StudentName VARCHAR(100),
  Faculty VARCHAR(100),
  ProgramCode VARCHAR(50),
  Email VARCHAR(100),
  PhoneNumber VARCHAR(20),
  Campus VARCHAR(100),
  GraduatingStatus VARCHAR(50),
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
