CREATE TABLE StudentCourse (
    StudentID INT PRIMARY KEY,
    CourseCode VARCHAR(20),
    Credit INT,
    Group INT,
    FOREIGN KEY (CourseCode) REFERENCES Course(CourseCode)
);