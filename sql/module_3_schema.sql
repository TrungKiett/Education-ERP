-- Module 3: Nền tảng Giảng viên (Giao bài tập & Chấm điểm)
-- Assignment (Bài tập)
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `max_score` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT 'Điểm tối đa',
  `due_date` datetime NOT NULL COMMENT 'Hạn nộp bài',
  `status` enum('draft','published','closed') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_class_id` (`class_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_assignment_class` FOREIGN KEY (`class_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Submission (Bài nộp)
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_url` text DEFAULT NULL COMMENT 'URL file từ Supabase Storage',
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL COMMENT 'Kích thước file (bytes)',
  `submitted_at` datetime NOT NULL,
  `status` enum('submitted','graded','late') DEFAULT 'submitted',
  `score` decimal(5,2) DEFAULT NULL COMMENT 'Điểm số',
  `feedback` text DEFAULT NULL COMMENT 'Phản hồi từ giáo viên',
  `graded_at` datetime DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL COMMENT 'Teacher ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment_student` (`assignment_id`, `student_id`),
  KEY `idx_assignment_id` (`assignment_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  KEY `idx_graded_by` (`graded_by`),
  CONSTRAINT `fk_submission_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_submission_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_submission_teacher` FOREIGN KEY (`graded_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Academic_Results (Học bạ điện tử)
CREATE TABLE IF NOT EXISTS `academic_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL DEFAULT 100.00,
  `grade` varchar(10) DEFAULT NULL COMMENT 'A, B, C, D, F',
  `semester` varchar(20) DEFAULT NULL COMMENT 'Học kỳ',
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'Năm học',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_class_id` (`class_id`),
  KEY `idx_subject_id` (`subject_id`),
  KEY `idx_assignment_id` (`assignment_id`),
  KEY `idx_semester_year` (`semester`, `academic_year`),
  CONSTRAINT `fk_academic_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_academic_class` FOREIGN KEY (`class_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_academic_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_academic_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger: Tự động cập nhật Academic_Results khi điểm được chấm
DELIMITER $$
CREATE TRIGGER `trg_update_academic_results_on_grading`
AFTER UPDATE ON `submissions`
FOR EACH ROW
BEGIN
    DECLARE class_id_val INT(11);
    DECLARE subject_id_val INT(11);
    DECLARE semester_val VARCHAR(20);
    DECLARE academic_year_val VARCHAR(20);
    
    -- Chỉ xử lý khi điểm được cập nhật (từ NULL sang có giá trị)
    IF OLD.score IS NULL AND NEW.score IS NOT NULL AND NEW.graded_by IS NOT NULL THEN
        -- Lấy thông tin từ assignment
        SELECT a.class_id, a.subject_id INTO class_id_val, subject_id_val
        FROM assignments a WHERE a.id = NEW.assignment_id;
        
        -- Tính toán học kỳ và năm học (có thể tùy chỉnh logic)
        SET semester_val = CASE 
            WHEN MONTH(NOW()) BETWEEN 1 AND 5 THEN 'HK2'
            WHEN MONTH(NOW()) BETWEEN 6 AND 8 THEN 'HK3'
            ELSE 'HK1'
        END;
        SET academic_year_val = CONCAT(YEAR(NOW()) - 1, '-', YEAR(NOW()));
        
        -- Tính điểm chữ
        SET @grade_letter = CASE
            WHEN NEW.score >= 90 THEN 'A'
            WHEN NEW.score >= 80 THEN 'B'
            WHEN NEW.score >= 70 THEN 'C'
            WHEN NEW.score >= 60 THEN 'D'
            ELSE 'F'
        END;
        
        -- Insert hoặc update vào academic_results
        INSERT INTO academic_results (
            student_id, class_id, subject_id, assignment_id,
            score, max_score, grade, semester, academic_year
        ) VALUES (
            NEW.student_id, class_id_val, subject_id_val, NEW.assignment_id,
            NEW.score, NEW.max_score, @grade_letter, semester_val, academic_year_val
        )
        ON DUPLICATE KEY UPDATE
            score = NEW.score,
            max_score = NEW.max_score,
            grade = @grade_letter,
            updated_at = NOW();
    END IF;
END$$
DELIMITER ;

