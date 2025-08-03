CREATE TABLE Appeal (
  AppealID INT AUTO_INCREMENT PRIMARY KEY,
  StudentID INT,
  Problem TEXT,
  Request TEXT,
  Signature VARCHAR(100),
  AppealDate DATE,
  FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);
