CREATE TABLE faculty (
  FacultyID INT AUTO_INCREMENT PRIMARY KEY,
  AppealID INT,
  Comment TEXT,
  SuggestedAction TEXT,
  OOF_Signature VARCHAR(100),
  OOF_CommentDate DATE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  FOREIGN KEY (AppealID) REFERENCES Appeal(AppealID)
);


