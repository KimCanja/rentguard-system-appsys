-- RentGuard Database Schema

CREATE DATABASE IF NOT EXISTS rentguard;
USE rentguard;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- Add profile_photo column to users table
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER role;
);

-- Customers table (extended profile)
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    license_number VARCHAR(50),
    birthdate DATE,
    damage_incidents_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    year INT,
    type VARCHAR(50),
    status ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
    current_mileage INT DEFAULT 0,
    photo_url VARCHAR(255),
    price_per_day DECIMAL(10, 2) DEFAULT 0.00
    -- Add passenger_capacity column to vehicles table
ALTER TABLE vehicles ADD COLUMN passenger_capacity INT DEFAULT 4 AFTER type;
);

-- Rentals table
CREATE TABLE IF NOT EXISTS rentals (
    rental_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    pickup_time TIME,
    status ENUM('pending', 'approved', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    total_price DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE
);

-- Rental Photos table
CREATE TABLE IF NOT EXISTS rental_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    type ENUM('before', 'after') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE
);

-- Damage Reports table
CREATE TABLE IF NOT EXISTS damage_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    customer_id INT NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    severity ENUM('low', 'medium', 'high') DEFAULT 'low',
    admin_notes TEXT,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

-- Schedules table
CREATE TABLE IF NOT EXISTS schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    available_date DATE NOT NULL,
    time_slot VARCHAR(50),
    vehicle_id INT NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE
);

-- Create SOS alerts table
CREATE TABLE IF NOT EXISTS sos_alerts (
    sos_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rental_id INT,
    vehicle_id INT,
    alert_type ENUM('emergency', 'accident', 'mechanical', 'assault', 'other') DEFAULT 'emergency',
    location_lat DECIMAL(10, 8),
    location_lng DECIMAL(11, 8),
    location_address TEXT,
    message TEXT,
    status ENUM('pending', 'responded', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    responded_by INT,
    admin_response TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin (password: admin123) - plaintext as requested
INSERT INTO users (name, email, password, role) 
VALUES 
('System Admin', 'admin@rentguard.com', 'admin123', 'admin')
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    password = VALUES(password),
    role = VALUES(role);