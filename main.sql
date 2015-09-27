DROP DATABASE IF EXIST game_db;
CREATE DATABASE game_db;

CREATE TABLE user(
    userID int not null auto_increment primary key,
    username varchar(255) not null,
    password varchar(255) not null,
    email varchar(255) not null,
    date_created TIMESTAMP not null default CURRENT_TIMESTAMP,
    date_modified DATETIME not null
    );
    
CREATE INDEX idx_username ON user (username) USING BTREE;
CREATE INDEX idx_email ON user (email) USING BTREE;

CREATE TABLE rooms(
    roomID int not null auto_increment primary key,
    roomname varchar(255) not null,
    roompass varchar(255),
    owner int not null,
    gameStatusID int not null,
    
);

CREATE TABLE game_status(
    gameStatusID int not null auto_increment primary key,
    gameStatus varchar(255) not null,
    remarks text
);

INSERT INTO game_status (gameStatus) VALUES
('open'),
('')