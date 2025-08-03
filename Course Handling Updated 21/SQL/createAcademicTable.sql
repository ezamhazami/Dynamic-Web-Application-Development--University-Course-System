CREATE TABLE academic (
  AcademicID INT AUTO_INCREMENT PRIMARY KEY,
  AppealID INT,
  NoteActionTaken TEXT,
  OOA_SignatureAndStamp VARCHAR(100),
  OOA_CommentDate DATE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  FOREIGN KEY (AppealID) REFERENCES Appeal(AppealID)
);

