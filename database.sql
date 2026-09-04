CREATE DATABASE IF NOT EXISTS LPA_eComms;
USE LPA_eComms;

CREATE TABLE IF NOT EXISTS lpa_stock (
    lpa_stock_ID VARCHAR(20) PRIMARY KEY,
    lpa_stock_name VARCHAR(250),
    lpa_stock_desc TEXT,
    lpa_stock_onhand VARCHAR(5),
    lpa_stock_price DECIMAL(7,2),
    lpa_stock_status CHAR(1),
    lpa_stock_image VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS lpa_clients (
    lpa_client_ID VARCHAR(20) PRIMARY KEY,
    lpa_client_firstname VARCHAR(50),
    lpa_client_lastname VARCHAR(50),
    lpa_client_address VARCHAR(250),
    lpa_client_phone VARCHAR(30),
    lpa_client_status CHAR(1),
    lpa_client_username VARCHAR(30) UNIQUE,
    lpa_client_password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS lpa_invoices (
    lpa_inv_no VARCHAR(20) PRIMARY KEY,
    lpa_inv_date DATETIME,
    lpa_inv_client_ID VARCHAR(20),
    lpa_inv_client_name VARCHAR(50),
    lpa_inv_client_address VARCHAR(250),
    lpa_inv_amount DECIMAL(8,2),
    lpa_inv_status CHAR(1)
);

CREATE TABLE IF NOT EXISTS lpa_invoice_items (
    lpa_invitem_no VARCHAR(20) PRIMARY KEY,
    lpa_invitem_inv_no VARCHAR(20),
    lpa_invitem_stock_ID VARCHAR(20),
    lpa_invitem_stock_name VARCHAR(250),
    lpa_invitem_qty VARCHAR(6),
    lpa_invitem_stock_price DECIMAL(7,2),
    lpa_invitem_stock_amount DECIMAL(7,2),
    lpa_inv_status CHAR(1)
);

CREATE TABLE IF NOT EXISTS lpa_users (
    lpa_user_ID VARCHAR(20) PRIMARY KEY,
    lpa_user_username VARCHAR(30),
    lpa_user_password VARCHAR(255),
    lpa_user_firstname VARCHAR(50),
    lpa_user_lastname VARCHAR(50),
    lpa_user_group VARCHAR(50),
    lpa_inv_status CHAR(1)
);

INSERT INTO lpa_stock 
(lpa_stock_ID, lpa_stock_name, lpa_stock_desc, lpa_stock_onhand, lpa_stock_price, lpa_stock_status, lpa_stock_image)
VALUES
('STK001', 'Wireless Mouse', 'Simple wireless mouse for office use.', '25', 19.95, 'E', 'wireless-mouse.webp'),
('STK002', 'USB Keyboard', 'Standard USB keyboard with full layout.', '18', 29.95, 'E', 'usb-keyboard.webp'),
('STK003', 'HDMI Cable', '2 metre HDMI cable for monitors and TVs.', '40', 12.50, 'E', 'hdmi-cable.webp'),
('STK004', 'Laptop Stand', 'Adjustable laptop stand for desk setup.', '10', 35.00, 'E', 'laptop-stand.webp');
