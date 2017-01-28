-- noinspection SqlNoDataSourceInspectionForFile
CREATE TABLE wp_cm_courses(
	ID int NOT NULL auto_increment,
	name VARCHAR(100) NOT NULL UNIQUE,
	description TEXT,
	price int NOT NULL,
	active BOOLEAN NOT NULL DEFAULT FALSE,
	span int NOT NULL COMMENT 'Days',
	PRIMARY KEY (ID)
);

CREATE TABLE wp_cm_course_parts(
	ID int NOT NULL auto_increment,
	courseID int NOT NULL,
	name VARCHAR(100) NOT NULL,
	courseIndex int NOT NULL,
	PRIMARY KEY (ID),
	FOREIGN KEY (courseID) REFERENCES wp_cm_courses(ID) ON DELETE CASCADE
);

CREATE TABLE wp_cm_parts(
	ID int NOT NULL auto_increment,
	coursePartID int NOT NULL,
	title VARCHAR(100),
	content LONGTEXT,
	type VARCHAR(65),
	partIndex int NOT NULL,
	PRIMARY KEY (ID),
	FOREIGN KEY (coursePartID) REFERENCES wp_cm_course_parts(ID) ON DELETE CASCADE
);

CREATE TABLE wp_cm_tags(
	ID int NOT NULL auto_increment,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE wp_cm_rel_tag_course(
	courseID int,
	tagID int,
	FOREIGN KEY (courseID) REFERENCES wp_cm_courses(ID) ON DELETE CASCADE,
	FOREIGN KEY (tagID) REFERENCES wp_cm_tags(ID) ON DELETE CASCADE,
	PRIMARY KEY (courseID, tagID)
);



---!! UNINSTALL !!---

DROP TABLE IF EXISTS wp_cm_parts,
             wp_cm_course_parts,
             wp_cm_rel_tag_course,
             wp_cm_tags,
             wp_cm_courses;



---!! FOR TESTING !!---
INSERT INTO wp_cm_courses (name,desciption,price,active,span) VALUES("Test Course","Just another Course",0,0,35);

INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(1,"Vecka 1",0);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(1,"Vecka 2",1);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(1,"Vecka 3",2);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(1,"Vecka 4",3);

INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(1,"Hello World",
	"<h5>Welcome to the first week!</h5>","text",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(1,"Look at this!",
	"thisisalinktoavideo.video","video",1);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(2,"Week Two",
	"https://i.imgur.com/C4bpUzv.jpg","image",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(3,"Week Three",
	"{What is your favorite color?},{Please describe your fear:},","question",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(4,"Week Four",
	"<h5>Look at this PDF please</h5>","download",0);

INSERT INTO wp_cm_tags (name) VALUES("Tag ex.");

INSERT INTO wp_cm_rel_tag_course (courseID,tagID) VALUES(1,1);


INSERT INTO wp_cm_courses (name,active,span) VALUES("Test Course 2",0,45);

INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(2,"Del 1",0);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(2,"Del 2",1);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(2,"Del 3",2);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(2,"Del 4",3);
INSERT INTO wp_cm_course_parts (courseID,name,courseIndex) VALUES(2,"Del 5",4);

INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(5,"Dag 1",
	"<h5>Welcome to the first week!</h5>","text",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(5,"Look at this!",
	"thisisalinktoavideo.video","video",1);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(6,"Dag 2",
	"https://i.imgur.com/C4bpUzv.jpg","image",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(7,"Dag 3",
	"{What is your favorite animal?},{Please describe your first five years:},","question",0);
INSERT INTO wp_cm_parts (coursePartID,title,content,type,partIndex) VALUES(8,"Dag 4",
	"<h5>Look at this PDF please</h5>","download",0);

INSERT INTO wp_cm_tags (name) VALUES("Weight Loss");

INSERT INTO wp_cm_rel_tag_course (courseID,tagID) VALUES(2,1);
INSERT INTO wp_cm_rel_tag_course (courseID,tagID) VALUES(2,2);
