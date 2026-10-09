
-- Create database 'travel_agency' and tables
CREATE DATABASE IF NOT EXISTS travel_agency CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_agency;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  role VARCHAR(50) DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  short_desc VARCHAR(512),
  description TEXT,
  price INT,
  duration INT,
  image VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(100),
  package_id INT,
  status VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);
-- Demo seed data omitted from the public repository.

-- Demo INSERT statements intentionally omitted from the public repository.
-- Demo seed data omitted from the public repository.

-- Demo INSERT statements intentionally omitted from the public repository.

