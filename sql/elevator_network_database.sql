-- Elevator Network Database Schema
-- Deliverable: Database with primary keys, foreign keys, unique keys, and indexes

CREATE DATABASE IF NOT EXISTS elevator_network;
USE elevator_network;

-- Parent table: Elevator Network (Primary Key: nodeID)
CREATE TABLE elevatorNetwork (
    nodeID INT AUTO_INCREMENT PRIMARY KEY,
    nodeName VARCHAR(100) NOT NULL,
    nodeType ENUM('elevator', 'controller', 'sensor', 'door') NOT NULL,
    ipAddress VARCHAR(15) UNIQUE,  -- Unique key requirement
    currentFloor INT DEFAULT 1,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'online',
    lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),  -- Index requirement
    INDEX idx_nodeType (nodeType)
);

-- Child table: CAN Components (Foreign Key: nodeID references elevatorNetwork)
CREATE TABLE canComponents (
    canID INT AUTO_INCREMENT PRIMARY KEY,
    nodeID INT NOT NULL,  -- Foreign key
    canAddress VARCHAR(8) UNIQUE NOT NULL,  -- Unique key
    componentType ENUM('motor', 'door_sensor', 'floor_sensor', 'button') NOT NULL,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    lastMessage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nodeID) REFERENCES elevatorNetwork(nodeID) ON DELETE CASCADE,
    INDEX idx_componentType (componentType)
);

-- Insert sample data
INSERT INTO elevatorNetwork (nodeName, nodeType, ipAddress, currentFloor, status) VALUES
('Main Controller', 'controller', '192.168.1.100', 1, 'online'),
('Elevator Car', 'elevator', '192.168.1.101', 2, 'online'),
('Floor 1 Sensor', 'sensor', '192.168.1.102', 1, 'online'),
('Floor 2 Sensor', 'sensor', '192.168.1.103', 2, 'online'),
('Main Door System', 'door', '192.168.1.104', 1, 'maintenance');

INSERT INTO canComponents (nodeID, canAddress, componentType, status) VALUES
(1, '0x100', 'motor', 'active'),
(2, '0x101', 'door_sensor', 'active'),
(3, '0x102', 'floor_sensor', 'active'),
(4, '0x103', 'floor_sensor', 'active'),
(5, '0x104', 'button', 'inactive');
