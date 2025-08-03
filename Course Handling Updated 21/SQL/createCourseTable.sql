CREATE TABLE Course (
  CourseCode VARCHAR(10) PRIMARY KEY,
  StudentID INT,
  CourseName VARCHAR(100),
  CreditHour INT,
  FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);
