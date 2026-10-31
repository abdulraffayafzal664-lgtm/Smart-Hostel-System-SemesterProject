CREATE DATABASE IF NOT EXISTS smart_hostel;
USE smart_hostel;

CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(10) NOT NULL UNIQUE,
  capacity TINYINT UNSIGNED NOT NULL
);

-- One table for all three roles (User -> Student / Warden / MessStaff)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login_id VARCHAR(20) NOT NULL UNIQUE,      -- reg no / employee id / staff id
  name VARCHAR(60) NOT NULL,
  age TINYINT UNSIGNED NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(15) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Student','Warden','MessStaff') NOT NULL DEFAULT 'Student',
  room_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS meal_settings (
  meal_type ENUM('Breakfast','Lunch','Dinner') PRIMARY KEY,
  meal_time TIME NOT NULL,
  cutoff_hours TINYINT UNSIGNED NOT NULL,    -- bookings close this many hours before the meal
  rate DECIMAL(8,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS meal_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  meal_date DATE NOT NULL,
  meal_type ENUM('Breakfast','Lunch','Dinner') NOT NULL,
  status ENUM('Booked','Cancelled') NOT NULL DEFAULT 'Booked',
  UNIQUE KEY one_booking (student_id, meal_date, meal_type),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO meal_settings VALUES
('Breakfast','08:00:00',12,80.00),
('Lunch','13:00:00',6,150.00),
('Dinner','20:00:00',6,150.00);

INSERT IGNORE INTO rooms (room_number, capacity) VALUES
('A-101',2),('A-102',2),('A-103',3),('B-201',4);
