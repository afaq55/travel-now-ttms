
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin','agent','customer','user') DEFAULT 'user',
  is_active TINYINT(1) DEFAULT 1,
  reset_token VARCHAR(255) NULL,
  reset_expires DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- PACKAGES
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  short_desc VARCHAR(255),
  description TEXT,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  duration INT DEFAULT NULL,
  location VARCHAR(255),
  image VARCHAR(255),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (title),
  INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- BOOKINGS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  user_name VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  package_id INT DEFAULT NULL,
  pax INT DEFAULT 1,
  travel_date DATE DEFAULT NULL,
  total_price DECIMAL(12,2) DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- PAYMENTS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  booking_type ENUM('package','bus') NOT NULL,
  booking_id INT NOT NULL,
  payment_method ENUM('card','easypaisa','jazzcash','cash','other') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  transaction_id VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- REVIEWS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  package_id INT DEFAULT NULL,
  user_id INT DEFAULT NULL,
  user_name VARCHAR(255) DEFAULT NULL,
  rating TINYINT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- BUSES
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS buses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bus_name VARCHAR(255) NOT NULL,
  bus_type VARCHAR(100),
  from_city VARCHAR(255),
  to_city VARCHAR(255),
  departure_time TIME,
  arrival_time TIME,
  total_seats INT DEFAULT 40,
  available_seats INT DEFAULT 40,
  price DECIMAL(12,2) DEFAULT 0.00,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- BUS BOOKINGS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS bus_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  bus_id INT NOT NULL,
  seat_no VARCHAR(20),
  cnic VARCHAR(50),
  phone VARCHAR(50),
  travel_date DATE,
  status VARCHAR(50) DEFAULT 'confirmed',
  payment_status VARCHAR(50) DEFAULT 'paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
  UNIQUE KEY ux_bus_seat_date (bus_id, travel_date, seat_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- MESSAGES
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- SETTINGS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- SAMPLE DATA
-- -------------------------------------------------------

-- Demo INSERT statements intentionally omitted from the public repository.

