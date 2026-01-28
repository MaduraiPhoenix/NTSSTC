TABLE: admin
{

id int(11) NOT NULL AUTO_INCREMENT

username varchar(50) DEFAULT NULL

password varchar(50) DEFAULT NULL
}
PK(id)

TABLE: attendance
{

id int(11) NOT NULL AUTO_INCREMENT

student_id int(11) DEFAULT NULL

teacher_id int(11) DEFAULT NULL

date date DEFAULT NULL

hour int(11) DEFAULT NULL

status enum('P','A') DEFAULT NULL

class_id varchar(50) DEFAULT NULL
}
PK(id)

TABLE: classes
{

id int(11) NOT NULL AUTO_INCREMENT

name varchar(100) DEFAULT NULL

department varchar(100) DEFAULT NULL
}
PK(id)

TABLE: class_teacher_map
{

id int(11) NOT NULL AUTO_INCREMENT

class_id int(11) DEFAULT NULL

teacher_id int(11) DEFAULT NULL
}
PK(id)
FK(class_id → classes.id)
FK(teacher_id → teachers.id)

TABLE: students
{

id int(11) NOT NULL AUTO_INCREMENT

name varchar(100) DEFAULT NULL

roll_no varchar(20) DEFAULT NULL

department varchar(50) DEFAULT NULL

semester int(11) DEFAULT NULL

class_id int(11) NOT NULL
}
PK(id)

Note: In your dump, class_id in students is defined as NOT NULL but no foreign key is declared. You can add:
FK(class_id → classes.id) if you want referential integrity.

TABLE: subjects
{

id int(11) NOT NULL AUTO_INCREMENT

name varchar(100) DEFAULT NULL

department varchar(100) DEFAULT NULL
}
PK(id)

TABLE: teachers
{

id int(11) NOT NULL AUTO_INCREMENT

name varchar(100) DEFAULT NULL

email varchar(100) DEFAULT NULL

department varchar(100) DEFAULT NULL

password varchar(100) DEFAULT NULL
}
PK(id)