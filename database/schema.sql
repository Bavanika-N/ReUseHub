/* ReUseHub Database Schema */
/* Requirements Baseline v1.1 (v1.0 + CR-001 Notification System) */

CREATE DATABASE IF NOT EXISTS reusehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reusehub;

/* 15.1 User Information */
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

/* 15.2 Item Information */
CREATE TABLE items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(80) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    contact_number VARCHAR(30) NOT NULL,
    status ENUM('Available','Requested','Sold Out') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

/* 15.2b Additional Item Images (multiple photos per item) */
CREATE TABLE item_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;

/* 15.3 Request Information */
CREATE TABLE requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    requester_id INT NOT NULL,
    request_status ENUM('Pending','Accepted','Rejected') NOT NULL DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

/* 23.6 Notification Information (CR-001 / v1.1) */
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    type ENUM('New Item','Request Received','Request Decision','Item Sold Out') NOT NULL,
    related_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

/* Default admin account (email: admin@reusehub.com / password: Admin@123) */
INSERT INTO users (name, email, password, role)
VALUES ('System Admin', 'admin@reusehub.com', '$2b$10$i1j/rQNNYNai7VOZP3zQn.fLv2KRXWEcsw0oXy89IB5Qvc9ccRB6u', 'admin');
/* NOTE: the hash above corresponds to "Admin@123" (bcrypt). Change this password after first login. */
