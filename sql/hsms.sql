-- =====================================================
-- Home Service Management System (HSMS) - Database Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS hsms_db;
USE hsms_db;

-- ---------------------------------------------------
-- 1. Customer
-- ---------------------------------------------------
CREATE TABLE Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Address VARCHAR(255),
    Password VARCHAR(255) NOT NULL,   -- store hashed password (password_hash)
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------
-- 2. Admin (added — needed for admin login)
-- ---------------------------------------------------
CREATE TABLE Admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- ---------------------------------------------------
-- 3. Employee / Technician
-- ---------------------------------------------------
CREATE TABLE Employee (
    EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Specialization VARCHAR(100) NOT NULL,   -- e.g. Plumbing, Electrical
    Experience INT DEFAULT 0,               -- years
    Availability ENUM('Available','Busy','Offline') DEFAULT 'Available',
    Rating DECIMAL(3,2) DEFAULT 0.00,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------
-- 4. ServiceCategory
-- ---------------------------------------------------
CREATE TABLE ServiceCategory (
    ServiceID INT AUTO_INCREMENT PRIMARY KEY,
    ServiceName VARCHAR(100) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Description TEXT
);

-- ---------------------------------------------------
-- 5. ServiceRequest
-- ---------------------------------------------------
CREATE TABLE ServiceRequest (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    ServiceID INT NOT NULL,
    EmployeeID INT NULL,
    RequestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PreferredDate DATE NOT NULL,
    PreferredTime TIME,
    Address VARCHAR(255) NOT NULL,
    Description TEXT,
    PhotoPath VARCHAR(255) NULL,           -- problem photo upload
    Status ENUM('Pending','Assigned','In Progress','Completed','Cancelled') DEFAULT 'Pending',
    IsEmergency BOOLEAN DEFAULT FALSE,
    Latitude DECIMAL(10, 8) NULL,
    Longitude DECIMAL(11, 8) NULL,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE,
    FOREIGN KEY (ServiceID) REFERENCES ServiceCategory(ServiceID),
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeID) ON DELETE SET NULL
);

-- ---------------------------------------------------
-- 6. Payment
-- ---------------------------------------------------
CREATE TABLE Payment (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    RequestID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentMethod ENUM('Cash','Card','Online') NOT NULL,
    PaymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('Pending','Paid','Refunded') DEFAULT 'Pending',
    FOREIGN KEY (RequestID) REFERENCES ServiceRequest(RequestID) ON DELETE CASCADE
);

-- ---------------------------------------------------
-- 7. Review
-- ---------------------------------------------------
CREATE TABLE Review (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    EmployeeID INT NOT NULL,
    RequestID INT NOT NULL,
    Rating INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    Comment TEXT,
    ReviewDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE,
    FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeID) ON DELETE CASCADE,
    FOREIGN KEY (RequestID) REFERENCES ServiceRequest(RequestID) ON DELETE CASCADE
);

-- =====================================================
-- Seed data
-- =====================================================

INSERT INTO Admin (Name, Email, Password) VALUES
('System Admin', 'admin@hsms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 
-- ^ password hash for "admin123" (generate your own via password_hash())

INSERT INTO ServiceCategory (ServiceName, Price, Description) VALUES
('TV Repair', 1500.00, 'Repair of LED, LCD, CRT televisions'),
('Plumbing', 1200.00, 'Pipe leaks, blockages, fittings'),
('Electrical Services', 1800.00, 'Wiring, switches, circuit issues'),
('AC Repair & Maintenance', 2500.00, 'Gas refill, servicing, repair'),
('RO Water Filter Service', 1000.00, 'Filter replacement and servicing'),
('Carpenter Services', 1500.00, 'Furniture repair and fittings'),
('Painting', 3000.00, 'Interior and exterior painting'),
('CCTV Installation', 5000.00, 'New CCTV setup and configuration'),
('Refrigerator Repair', 2000.00, 'Cooling and compressor issues'),
('Washing Machine Repair', 1800.00, 'Motor, drainage, electrical faults');

INSERT INTO Employee (Name, Phone, Email, Password, Specialization, Experience) VALUES
('Kasun Perera', '0771234567', 'kasun@hsms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Plumbing', 5),
('Nimal Silva', '0779876543', 'nimal@hsms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Electrical Services', 8);
