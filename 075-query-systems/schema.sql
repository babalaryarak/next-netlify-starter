CREATE DATABASE IF NOT EXISTS q075 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE q075;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(32) UNIQUE,pass VARCHAR(255),role ENUM('free','vip','admin','owner') DEFAULT 'free',balance DECIMAL(10,2) DEFAULT 0,xp INT DEFAULT 0,avatar VARCHAR(100) DEFAULT NULL,device_limit INT DEFAULT 1,vip_until DATETIME NULL,unlimited TINYINT DEFAULT 0,banned TINYINT DEFAULT 0,reg_ip VARCHAR(45),last_ip VARCHAR(45),last_login DATETIME NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE devices(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,token CHAR(64),ip VARCHAR(45),ua VARCHAR(255),created_at DATETIME DEFAULT CURRENT_TIMESTAMP,UNIQUE(user_id,token));
CREATE TABLE packages(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(30),days INT,price DECIMAL(10,2));
INSERT INTO packages(name,days,price) VALUES('Haftalık',7,400),('Aylık',30,750),('Yıllık',365,1400),('Sınırsız',0,3500);
CREATE TABLE invoices(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,package_id INT NULL,amount DECIMAL(12,6),status ENUM('pending','paid','expired') DEFAULT 'pending',txid VARCHAR(80) UNIQUE NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(60),table_name VARCHAR(64),columns_csv VARCHAR(255),min_role ENUM('free','vip','admin','owner') DEFAULT 'vip');
CREATE TABLE query_log(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,category_id INT,term VARCHAR(255),ip VARCHAR(45),created_at DATETIME DEFAULT CURRENT_TIMESTAMP,KEY(created_at));
CREATE TABLE chat(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,msg VARCHAR(300),created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE demo_records(id INT AUTO_INCREMENT PRIMARY KEY,ad VARCHAR(60),soyad VARCHAR(60),telefon VARCHAR(20),sehir VARCHAR(40));
INSERT INTO demo_records(ad,soyad,telefon,sehir) VALUES('Ali','Yılmaz','5550000001','İstanbul'),('Ayşe','Demir','5550000002','Ankara');
INSERT INTO categories(name,table_name,columns_csv,min_role) VALUES('Demo Ücretsiz','demo_records','ad,soyad,telefon,sehir','free'),('Demo VIP','demo_records','ad,soyad,telefon,sehir','vip');
-- Owner: php -r 'echo password_hash("SIFRE",PASSWORD_DEFAULT);' sonra:
-- INSERT INTO users(username,pass,role) VALUES('owner','HASH','owner');
