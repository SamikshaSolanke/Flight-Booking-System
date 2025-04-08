--QUERY TO CREATE DATABASE--
CREATE DATABASE DBMS_PROJECT;

--QUERY TO USE THAT DATABASE--
USE DBMS_PROJECT;

--QUERY TO CREATE Users TABLE--
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    password VARCHAR(255) NOT NULL
);

--QUERY TO CREATE Companies TABLE--
CREATE TABLE Companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

--QUERY TO CREATE Airports TABLE--
CREATE TABLE Airports (
    airport_code VARCHAR(10) PRIMARY KEY,
    City VARCHAR(255) NOT NULL,
    State VARCHAR(255) NOT NULL
);

--QUERY TO CREATE Flights TABLE--
CREATE TABLE Flights (
    flight_id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    departure_date DATE NOT NULL,
    seats_no INT NOT NULL,
    from_airport_code VARCHAR(10) NOT NULL,
    to_airport_code VARCHAR(10) NOT NULL,
    Price INT NOT NULL,
    FOREIGN KEY (company_id) REFERENCES Companies(company_id),
    FOREIGN KEY (from_airport_code) REFERENCES Airports(airport_code),
    FOREIGN KEY (to_airport_code) REFERENCES Airports(airport_code)
);

--QUERY TO CREATE Bookings TABLE--
CREATE TABLE Bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Price INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (flight_id) REFERENCES Flights(flight_id)
);

--QUERY TO ADD Airports IN THE Airports TABLE--
INSERT INTO Airports (airport_code, City, State) VALUES
('DEL', 'Delhi', 'Delhi'),
('BOM', 'Mumbai', 'Maharashtra'),
('MAA', 'Chennai', 'Tamil Nadu'),
('BLR', 'Bengaluru', 'Karnataka'),
('HYD', 'Hyderabad', 'Telangana'),
('CCU', 'Kolkata', 'West Bengal'),
('AMD', 'Ahmedabad', 'Gujarat'),
('PNQ', 'Pune', 'Maharashtra'),
('GOI', 'Goa', 'Goa'),
('COK', 'Kochi', 'Kerala'),
('JAI', 'Jaipur', 'Rajasthan'),
('LKO', 'Lucknow', 'Uttar Pradesh'),
('IXC', 'Chandigarh', 'Chandigarh'),
('TRV', 'Thiruvananthapuram', 'Kerala'),
('PAT', 'Patna', 'Bihar');

--QUERY TO CREATE A STORED PROCEDURE FOR LOGIN--
DELIMITER //
CREATE PROCEDURE authenticate_user(
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255),
    IN p_user_type VARCHAR(10)
)
BEGIN
    IF p_user_type = 'user' THEN
        -- Authenticate regular user
        SELECT user_id, name, email
        FROM Users
        WHERE email = p_email;
    ELSEIF p_user_type = 'company' THEN
        -- Authenticate company
        SELECT company_id, company_name, email
        FROM Companies
        WHERE email = p_email;
    ELSE
        -- Invalid user type
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid user type specified';
    END IF;
END //
DELIMITER ;
