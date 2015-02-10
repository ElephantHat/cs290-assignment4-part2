# Create table "rstvideo" with:
# "id" - an auto incrementing integer which is the primary key of each video.
# "name" - the name of the video, a required, unique variable length string with a maximum length of 255 characters.
# "category" - the category the video belongs to (action, comedy, drama etc), a variable length string with a maximum length of 255 characters.
# "length" - the length of the movie in minutes, recorded as a positive integer.
# "rented" - a required boolean value indicating whether the video is checkedin in. When added it should default to checked in.

CREATE TABLE rstvideo(
id int primary key auto_increment,
name varchar(255) UNIQUE NOT NULL,
category varchar(255) DEFAULT 'Uncategorized',
length int unsigned,
rented boolean NOT NULL DEFAULT 0)
engine=innodb;
