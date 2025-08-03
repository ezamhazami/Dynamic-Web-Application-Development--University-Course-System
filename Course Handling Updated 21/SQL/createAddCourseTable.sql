CREATE TABLE AddCourse (
  AddCourseID INT AUTO_INCREMENT PRIMARY KEY,
  AppealID INT,
  CourseCode VARCHAR(10),
  `Group` VARCHAR(20),
  FOREIGN KEY (AppealID) REFERENCES Appeal(AppealID),
  FOREIGN KEY (CourseCode) REFERENCES Course(CourseCode)
);