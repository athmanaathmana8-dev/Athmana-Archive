-- Enhanced Airport Management System Database Schema
-- This file contains the complete database structure for the enhanced system

CREATE DATABASE IF NOT EXISTS airport_management_system;
USE airport_management_system;

-- Airports table
CREATE TABLE IF NOT EXISTS airport (
    airport_id INT PRIMARY KEY AUTO_INCREMENT,
    airport_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flights table (enhanced)
CREATE TABLE IF NOT EXISTS flights (
    flight_number VARCHAR(10) PRIMARY KEY,
    flight_company VARCHAR(50) NOT NULL,
    departing_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    no_of_seats INT NOT NULL,
    source VARCHAR(50) NOT NULL,
    destination VARCHAR(50) NOT NULL,
    price_economy DECIMAL(10,2) NOT NULL,
    price_business DECIMAL(10,2) NOT NULL,
    price_first DECIMAL(10,2) NOT NULL,
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_from DATETIME NULL,
    blocked_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seats table for seat management
CREATE TABLE IF NOT EXISTS seats (
    seat_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(10),
    seat_number VARCHAR(10) NOT NULL,
    seat_class ENUM('Economy', 'Business', 'First') NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    is_reserved BOOLEAN DEFAULT FALSE,
    reserved_until DATETIME NULL,
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number) ON DELETE CASCADE
);

-- Employees table (enhanced)
CREATE TABLE IF NOT EXISTS employee (
    emp_id VARCHAR(20) PRIMARY KEY,
    emp_name VARCHAR(100) NOT NULL,
    job VARCHAR(50) NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    airport_id INT,
    flight_number VARCHAR(10),
    email VARCHAR(100),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (airport_id) REFERENCES airport(airport_id),
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number)
);

-- Passengers table (enhanced)
CREATE TABLE IF NOT EXISTS passenger (
    p_id VARCHAR(20) PRIMARY KEY,
    passenger_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    date_of_birth DATE,
    flight_number VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number)
);

-- Tickets table (enhanced)
CREATE TABLE IF NOT EXISTS tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    flying_to VARCHAR(50) NOT NULL,
    flying_from VARCHAR(50) NOT NULL,
    departing_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    class ENUM('Economy', 'Business', 'First') NOT NULL,
    flight_number VARCHAR(10) NOT NULL,
    p_id VARCHAR(20) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded', 'Cancelled') DEFAULT 'Pending',
    payment_id VARCHAR(100),
    booking_reference VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number),
    FOREIGN KEY (p_id) REFERENCES passenger(p_id)
);

-- Create index on booking_reference for fast lookups (not unique)
CREATE INDEX idx_booking_reference ON tickets(booking_reference);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card', 'Debit Card', 'Net Banking', 'UPI', 'Wallet') NOT NULL,
    payment_status ENUM('Pending', 'Success', 'Failed', 'Refunded') DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    payment_gateway VARCHAR(50),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id)
);

-- Flight blocks table for tracking blocked flights
CREATE TABLE IF NOT EXISTS flight_blocks (
    block_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(10) NOT NULL,
    blocked_by VARCHAR(20) NOT NULL,
    reason TEXT,
    blocked_from DATETIME NOT NULL,
    blocked_until DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number),
    FOREIGN KEY (blocked_by) REFERENCES employee(emp_id)
);

-- Reservations table for seat reservations
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    p_id VARCHAR(20) NOT NULL,
    flight_number VARCHAR(10) NOT NULL,
    seat_id INT NOT NULL,
    reserved_until DATETIME NOT NULL,
    status ENUM('Active', 'Expired', 'Converted') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (p_id) REFERENCES passenger(p_id),
    FOREIGN KEY (flight_number) REFERENCES flights(flight_number),
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id)
);

-- Admin table for admin authentication
CREATE TABLE IF NOT EXISTS admin (
    admin_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    admin_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Note: Default admin account will be created automatically by Adminloginpage.php
-- Default credentials: admin_id: admin123, password: admin123
-- IMPORTANT: Change default password after first login!

-- Insert sample data
INSERT INTO airport (airport_name, city, country) VALUES 
('Kempegowda International Airport', 'Bangalore', 'India'),
('Indira Gandhi International Airport', 'Delhi', 'India'),
('Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'),
('Chennai International Airport', 'Chennai', 'India'),
('Rajiv Gandhi International Airport', 'Hyderabad', 'India'),
('Netaji Subhas Chandra Bose International Airport', 'Kolkata', 'India'),
('Pune Airport', 'Pune', 'India'),
('Sardar Vallabhbhai Patel International Airport', 'Ahmedabad', 'India'),
('Cochin International Airport', 'Kochi', 'India'),
('Dabolim Airport', 'Goa', 'India'),
('Jaipur International Airport', 'Jaipur', 'India'),
('Chaudhary Charan Singh International Airport', 'Lucknow', 'India'),
('Chandigarh Airport', 'Chandigarh', 'India'),
('Devi Ahilya Bai Holkar Airport', 'Indore', 'India'),
('Biju Patnaik International Airport', 'Bhubaneswar', 'India'),
('Coimbatore International Airport', 'Coimbatore', 'India'),
('Mangalore International Airport', 'Mangalore', 'India'),
('Trivandrum International Airport', 'Trivandrum', 'India'),
('Visakhapatnam Airport', 'Vizag', 'India');

INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) VALUES 
('AI101', 'Air India', '08:00:00', '10:30:00', 150, 'Bangalore', 'Delhi', 15000.00, 25000.00, 35000.00),
('SG202', 'SpiceJet', '14:30:00', '17:00:00', 120, 'Bangalore', 'Mumbai', 12000.00, 20000.00, 30000.00),
('6E303', 'IndiGo', '19:45:00', '22:15:00', 180, 'Bangalore', 'Chennai', 8000.00, 15000.00, 25000.00),
('AI404', 'Air India', '06:30:00', '09:00:00', 160, 'Delhi', 'Bangalore', 18000.00, 28000.00, 38000.00);

-- Create indexes for better performance
CREATE INDEX idx_flights_destination ON flights(destination);
CREATE INDEX idx_flights_departing_date ON flights(departing_time);
CREATE INDEX idx_tickets_payment_status ON tickets(payment_status);
CREATE INDEX idx_seats_flight_available ON seats(flight_number, is_available);
CREATE INDEX idx_reservations_status ON reservations(status);

