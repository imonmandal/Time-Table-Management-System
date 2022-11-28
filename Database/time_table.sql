-- DON'T USE INTEGERS EXCEPT FOR NOS OF LEC....

CREATE DATABASE `time_table`;
-- DROP DATABASE `time_table`;
USE `time_table`;

CREATE TABLE `class` (
    ClassName VARCHAR(255)
);

CREATE TABLE `teacher` (
    TeacherName VARCHAR(255)
);

CREATE TABLE `room` (
    RoomNo VARCHAR(255)
);

CREATE TABLE `subject` (
    SubjectName VARCHAR(255)
);

INSERT INTO `class` VALUES
('TE1'),
('TE2'),
('TE3');

INSERT INTO `teacher` VALUES
('teacher1'),
('teacher2'),
('teacher3');

INSERT INTO `room` VALUES
('301'),
('302'),
('303');

INSERT INTO `subject` VALUES
('subject1'),
('subject2');

CREATE TABLE `teacher3` (
    Lecture_No VARCHAR(5),
    Monday VARCHAR(255),
    Tuesday VARCHAR(255),
    Wednesday VARCHAR(255),
    Thursday VARCHAR(255),
    Friday VARCHAR(255),
    Saturday VARCHAR(255)
);

INSERT INTO `teacher3` (Lecture_No) VALUES ('1'),('2'),('3'),('4');

CREATE TABLE `week` (
    days VARCHAR(25)
);
INSERT INTO `week` VALUES ('Monday'), ('Tuesday'), ('Wednesday'), ('Thursday'), ('Friday'), ('Saturday');

CREATE TABLE `lec` (
    sr_no INT,
    lec INT
);

INSERT INTO `lec` VALUES(1, 4);