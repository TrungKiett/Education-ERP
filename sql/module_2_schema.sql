-- Module 2: Tự động hóa Tài chính (Học phí)
-- Discount (Chiết khấu)
CREATE TABLE IF NOT EXISTS `discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL COMMENT 'Mã giảm giá',
  `name` varchar(255) NOT NULL,
  `type` enum('percentage','fixed') DEFAULT 'percentage' COMMENT 'percentage: %, fixed: số tiền cố định',
  `value` decimal(10,2) NOT NULL COMMENT 'Giá trị chiết khấu',
  `min_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Số tiền tối thiểu để áp dụng',
  `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'Số tiền giảm tối đa (cho percentage)',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Giới hạn số lần sử dụng',
  `used_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice (Hóa đơn)
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL COMMENT 'Số hóa đơn',
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Tổng tiền trước giảm giá',
  `discount_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Số tiền được giảm',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Tổng tiền sau giảm giá',
  `status` enum('unpaid','paid','cancelled','refunded') DEFAULT 'unpaid',
  `due_date` date NOT NULL COMMENT 'Hạn chót thanh toán',
  `paid_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Phương thức thanh toán',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_number` (`invoice_number`),
  KEY `idx_enrollment_id` (`enrollment_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `fk_invoice_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_discount` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction (Giao dịch thanh toán)
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `transaction_code` varchar(100) NOT NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'VNPAY, BANK_TRANSFER, etc.',
  `payment_gateway` varchar(50) DEFAULT NULL COMMENT 'Tên cổng thanh toán',
  `status` enum('pending','success','failed','cancelled') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL COMMENT 'JSON response từ cổng thanh toán',
  `webhook_data` text DEFAULT NULL COMMENT 'JSON data từ webhook',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_transaction_code` (`transaction_code`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_transaction_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger: Tự động tạo Invoice khi Enrollment được tạo
DELIMITER $$
CREATE TRIGGER `trg_create_invoice_on_enrollment`
AFTER INSERT ON `enrollments`
FOR EACH ROW
BEGIN
    DECLARE course_price DECIMAL(10,2);
    DECLARE invoice_num VARCHAR(50);
    DECLARE due_date DATE;
    
    -- Lấy giá khóa học
    SELECT price INTO course_price FROM courses WHERE id = NEW.course_id;
    
    -- Tạo số hóa đơn (INV-YYYYMMDD-XXXX)
    SET invoice_num = CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(LAST_INSERT_ID(), 4, '0'));
    
    -- Hạn chót: 7 ngày sau ngày ghi danh
    SET due_date = DATE_ADD(NEW.enrollment_date, INTERVAL 7 DAY);
    
    -- Tạo hóa đơn
    INSERT INTO invoices (
        invoice_number, enrollment_id, student_id, course_id,
        subtotal, total_amount, status, due_date
    ) VALUES (
        invoice_num, NEW.id, NEW.student_id, NEW.course_id,
        COALESCE(course_price, 0), COALESCE(course_price, 0), 'unpaid', due_date
    );
END$$
DELIMITER ;

