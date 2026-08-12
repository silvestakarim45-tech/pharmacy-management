-- Database Backup: pharmacy_management
-- Generated: 2026-06-29 11:10:20

-- Table: audit_logs
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table: customers
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table: medicines
CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL AUTO_INCREMENT,
  `medicine_name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reorder_point` int(11) DEFAULT 10,
  `supplier_id` int(11) DEFAULT NULL,
  `last_restocked` date DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO medicines VALUES ('1', 'paracetamor', 'dawa ya maumivu', NULL, '22', '10', NULL, '2026-06-28', '100.00', '500.00', '2030-12-12');
INSERT INTO medicines VALUES ('2', 'Paracetamol 500mg', 'Pain Relief', '0', '100', '20', NULL, NULL, '200.00', '500.00', '2027-12-31');
INSERT INTO medicines VALUES ('3', 'Amoxicillin 250mg', 'Antibiotics', '0', '45', '15', NULL, NULL, '500.00', '1200.00', '2027-06-30');
INSERT INTO medicines VALUES ('4', 'Ibuprofen 400mg', 'Pain Relief', '0', '75', '20', NULL, NULL, '300.00', '800.00', '2027-09-15');
INSERT INTO medicines VALUES ('5', 'Cough Syrup 100ml', 'Cough & Cold', '0', '60', '15', NULL, NULL, '400.00', '1000.00', '2027-08-20');
INSERT INTO medicines VALUES ('6', 'Vitamin C 500mg', 'Vitamins', '0', '115', '30', NULL, NULL, '150.00', '400.00', '2028-01-15');
INSERT INTO medicines VALUES ('7', 'Antacid Tablets', 'Digestive', '0', '80', '20', NULL, NULL, '250.00', '600.00', '2027-11-30');
INSERT INTO medicines VALUES ('8', 'Aspirin 100mg', 'Pain Relief', '0', '90', '25', NULL, NULL, '180.00', '450.00', '2027-10-25');
INSERT INTO medicines VALUES ('9', 'Allergy Tablets', 'Allergy', '0', '55', '41', NULL, NULL, '350.00', '900.00', '2027-07-15');
INSERT INTO medicines VALUES ('10', 'Multivitamins', 'Vitamins', '0', '70', '20', NULL, NULL, '450.00', '1100.00', '2028-02-28');
INSERT INTO medicines VALUES ('11', 'Eye Drops', 'Eye Care', '0', '40', '10', NULL, NULL, '300.00', '750.00', '2027-05-30');
INSERT INTO medicines VALUES ('12', 'Bandage Pack', 'First Aid', '0', '150', '40', NULL, NULL, '100.00', '300.00', '2028-06-30');
INSERT INTO medicines VALUES ('13', 'Thermometer', 'Medical Equipment', '0', '25', '10', NULL, NULL, '2000.00', '3500.00', '2029-01-01');
INSERT INTO medicines VALUES ('14', 'Blood Pressure Monitor', 'Medical Equipment', '0', '10', '5', NULL, NULL, '15000.00', '25000.00', '2029-06-30');
INSERT INTO medicines VALUES ('15', 'Diabetes Test Strips', 'Diabetes Care', '0', '200', '50', NULL, NULL, '500.00', '1200.00', '2027-04-15');
INSERT INTO medicines VALUES ('16', 'Insulin Pen', 'Diabetes Care', '0', '30', '10', NULL, NULL, '5000.00', '8000.00', '2027-03-31');
INSERT INTO medicines VALUES ('17', 'Paracetamol 500mg', 'Pain Relief', '0', '100', '20', NULL, NULL, '200.00', '500.00', '2027-12-31');
INSERT INTO medicines VALUES ('18', 'Amoxicillin 250mg', 'Antibiotics', '0', '50', '15', NULL, NULL, '500.00', '1200.00', '2027-06-30');
INSERT INTO medicines VALUES ('19', 'Ibuprofen 400mg', 'Pain Relief', '0', '75', '20', NULL, NULL, '300.00', '800.00', '2027-09-15');
INSERT INTO medicines VALUES ('20', 'Cough Syrup 100ml', 'Cough & Cold', '0', '60', '15', NULL, NULL, '400.00', '1000.00', '2027-08-20');
INSERT INTO medicines VALUES ('21', 'Vitamin C 500mg', 'Vitamins', '0', '120', '30', NULL, NULL, '150.00', '400.00', '2028-01-15');
INSERT INTO medicines VALUES ('22', 'Antacid Tablets', 'Digestive', '0', '80', '20', NULL, NULL, '250.00', '600.00', '2027-11-30');
INSERT INTO medicines VALUES ('23', 'Aspirin 100mg', 'Pain Relief', '0', '90', '25', NULL, NULL, '180.00', '450.00', '2027-10-25');
INSERT INTO medicines VALUES ('24', 'Allergy Tablets', 'Allergy', '0', '55', '15', NULL, NULL, '350.00', '900.00', '2027-07-15');
INSERT INTO medicines VALUES ('25', 'Multivitamins', 'Vitamins', '0', '70', '20', NULL, NULL, '450.00', '1100.00', '2028-02-28');
INSERT INTO medicines VALUES ('26', 'Eye Drops', 'Eye Care', '0', '40', '10', NULL, NULL, '300.00', '750.00', '2027-05-30');
INSERT INTO medicines VALUES ('27', 'Bandage Pack', 'First Aid', '0', '150', '40', NULL, NULL, '100.00', '300.00', '2028-06-30');
INSERT INTO medicines VALUES ('28', 'Thermometer', 'Medical Equipment', '0', '25', '10', NULL, NULL, '2000.00', '3500.00', '2029-01-01');
INSERT INTO medicines VALUES ('29', 'Blood Pressure Monitor', 'Medical Equipment', '0', '10', '5', NULL, NULL, '15000.00', '25000.00', '2029-06-30');
INSERT INTO medicines VALUES ('30', 'Diabetes Test Strips', 'Diabetes Care', '0', '200', '50', NULL, NULL, '500.00', '1200.00', '2027-04-15');
INSERT INTO medicines VALUES ('31', 'Insulin Pen', 'Diabetes Care', '0', '30', '10', NULL, NULL, '5000.00', '8000.00', '2027-03-31');

-- Table: order_items
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table: orders
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `delivery_address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table: sales
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `seller_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`sale_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sales VALUES ('1', '1', '2', '1000.00', '2026-06-20 20:42:58', NULL);
INSERT INTO sales VALUES ('2', '6', '5', '2000.00', '2026-06-27 19:05:55', NULL);
INSERT INTO sales VALUES ('3', '3', '5', '6000.00', '2026-06-27 19:07:52', '3');

-- Table: suppliers
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table: users
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users VALUES ('1', 'Administrator', NULL, NULL, 'admin', '$2y$10$PjTH8NfU3p.57cHqOVENbOtxIrnGYNeOhXm4yd4fK4IDyEotirJjq', 'admin', 'active');
INSERT INTO users VALUES ('2', 'veronica malata', 'vero@gmail.com', '0684398893', 'veronica', '$2y$10$27fXAmDydD8PpPev9BUhqOhZU5peIVoj45hphtEaYlQs93cqIj8.C', 'seller', 'active');
INSERT INTO users VALUES ('3', 'Pharmacist Seller', 'seller@pharmacy.com', '0712345678', 'seller', '$2y$10$RqkpW594EL0t3IDesoeSw.JknvnXSPBpZpT9Fc43Pj5VY0qNWr3x6', 'seller', 'active');

