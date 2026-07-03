CREATE DATABASE IF NOT EXISTS driveease_db;
USE driveease_db;

-- 1. Table Users
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    phone VARCHAR(20) NULL,
    license_no VARCHAR(50) NULL,
    ktp_no VARCHAR(50) NULL,
    profile_pic VARCHAR(255) NULL,
    document_pic VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table Cars
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    fuel_type ENUM('diesel', 'bensin', 'hybrid') NOT NULL,
    capacity INT NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    luggage INT NOT NULL,
    price_per_day DECIMAL(12, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    status ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table Bookings (Transactions)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    pickup_date DATETIME NOT NULL,
    return_date DATETIME NOT NULL,
    duration_days INT NOT NULL,
    driver_option TINYINT(1) DEFAULT 0,
    insurance_option TINYINT(1) DEFAULT 0,
    driver_fee DECIMAL(12,2) DEFAULT 0.00,
    insurance_fee DECIMAL(12,2) DEFAULT 0.00,
    base_price DECIMAL(12,2) NOT NULL,
    tax_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'approved', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Users
-- Admin: admin@driveease.com / admin123
-- Customer: customer@driveease.com / customer123
INSERT INTO users (name, email, password, role, phone, license_no, ktp_no) VALUES 
('Lexus Administrator', 'admin@driveease.com', '$2y$10$CA0xIqwxssP8eNcf0hTZHe0iYIde/G6uaaSXA/kC3wWru5lHO88Oq', 'admin', '081234567890', 'SIM-999888'),
('Aria Permana', 'customer@driveease.com', '$2y$10$sxzU1ztVPkgw39lDx1bMweaeuvvFy6Yo.cOAXtYN5VPav4UQkezgW', 'customer', '089876543210', 'SIM-112233');

-- Seed Cars (High-Quality Unsplash placeholders for visual excellence)
INSERT INTO cars (name, brand, fuel_type, capacity, transmission, luggage, price_per_day, image_url, status, description) VALUES
('Pajero Sport Dakar', 'Mitsubishi', 'diesel', 7, 'automatic', 450, 1200000.00, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'available', 'SUV premium tangguh, handal di segala medan, bertenaga diesel dengan konsumsi bahan bakar yang efisien untuk perjalanan keluarga maupun bisnis.'),
('Innova Reborn V', 'Toyota', 'diesel', 7, 'automatic', 400, 800000.00, 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=800&q=80', 'available', 'Medium MPV legendaris yang nyaman, kabin luas, suspensi empuk, dan mesin diesel 2GD-FTV yang terkenal tangguh dan irit.'),
('Civic RS Turbo', 'Honda', 'bensin', 5, 'automatic', 450, 1500000.00, 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=800&q=80', 'available', 'Sedan sport premium dengan performa mesin turbo yang responsif, desain aerodinamis modern, dan interior futuristik berteknologi tinggi.'),
('Avanza Veloz Facelift', 'Toyota', 'bensin', 7, 'automatic', 320, 500000.00, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'available', 'Pilihan utama keluarga Indonesia. Desain modern, efisiensi bahan bakar tinggi, serta kenyamanan ekstra untuk mobilitas perkotaan.'),
('Brio Satya E', 'Honda', 'bensin', 5, 'automatic', 250, 350000.00, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800&q=80', 'available', 'City car lincah, sangat mudah diparkir di perkotaan padat, konsumsi bensin sangat hemat, cocok untuk profesional muda.'),
('Ioniq 5 Signature', 'Hyundai', 'hybrid', 5, 'automatic', 520, 1800000.00, 'https://images.unsplash.com/photo-1669062391219-c6031f08f877?auto=format&fit=crop&w=800&q=80', 'available', 'Kendaraan Listrik (EV) murni dengan arsitektur futuristik, pengisian daya cepat, kabin luas bak lounge, dan teknologi kemudi otonom.'),
('Innova Zenix Q Hybrid', 'Toyota', 'hybrid', 7, 'automatic', 410, 1000000.00, 'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=800&q=80', 'available', 'MPV premium bertenaga Hybrid Generasi ke-5. Hening, ramah lingkungan, mewah dengan panoramic sunroof, dan sangat hemat bahan bakar.');

-- Seed Bookings (to simulate billing data and dynamic dashboard charts)
-- Monthly stats: April, May, June 2026.
INSERT INTO bookings (user_id, car_id, pickup_location, pickup_date, return_date, duration_days, driver_option, insurance_option, driver_fee, insurance_fee, base_price, tax_price, total_price, status, created_at) VALUES
(2, 1, 'Bandara YIA', '2026-04-10 09:00:00', '2026-04-13 09:00:00', 3, 1, 1, 450000.00, 150000.00, 3600000.00, 420000.00, 4620000.00, 'completed', '2026-04-09 10:00:00'),
(2, 3, 'Hotel Ambarrukmo', '2026-04-20 10:00:00', '2026-04-22 10:00:00', 2, 0, 1, 0.00, 100000.00, 3000000.00, 310000.00, 341000.00, 'completed', '2026-04-19 14:22:00'),
(2, 6, 'Garasi DriveEase Sleman', '2026-05-01 08:00:00', '2026-05-05 08:00:00', 4, 1, 1, 600000.00, 200000.00, 7200000.00, 800000.00, 8800000.00, 'completed', '2026-04-30 08:15:00'),
(2, 7, 'Bandara Adisutjipto', '2026-05-15 13:00:00', '2026-05-17 13:00:00', 2, 0, 0, 0.00, 0.00, 2000000.00, 200000.00, 2200000.00, 'completed', '2026-05-14 17:05:00'),
(2, 2, 'Stasiun Tugu', '2026-06-01 07:00:00', '2026-06-04 07:00:00', 3, 1, 0, 450000.00, 0.00, 2400000.00, 285000.00, 3135000.00, 'active', '2026-05-31 09:30:00'),
(2, 6, 'Hotel Marriott', '2026-06-10 11:00:00', '2026-06-12 11:00:00', 2, 0, 1, 0.00, 100000.00, 3600000.00, 370000.00, 4070000.00, 'pending', '2026-06-05 20:00:00');
