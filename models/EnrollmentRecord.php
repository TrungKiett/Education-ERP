<?php
require_once __DIR__ . '/Model.php';

class EnrollmentRecord extends Model {
    protected $table = 'enrollments';
    
    public function getByStudentId($studentId) {
        $sql = "SELECT e.*, 
                       c.name as class_name,
                       co.name as course_name,
                       co.price as course_price
                FROM {$this->table} e
                JOIN classrooms c ON e.class_id = c.id
                JOIN courses co ON e.course_id = co.id
                WHERE e.student_id = ? AND e.status = 'active'
                ORDER BY e.enrollment_date DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getByClassId($classId) {
        $sql = "SELECT e.*, 
                       s.full_name as student_name,
                       s.code as student_code,
                       co.name as course_name
                FROM {$this->table} e
                JOIN students s ON e.student_id = s.id
                JOIN courses co ON e.course_id = co.id
                WHERE e.class_id = ? AND e.status = 'active'
                ORDER BY s.full_name";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function checkExisting($studentId, $classId, $courseId) {
        $stmt = $this->getConnection()->prepare(
            "SELECT id FROM {$this->table} 
             WHERE student_id = ? AND class_id = ? AND course_id = ? AND status = 'active'"
        );
        $stmt->bind_param("iii", $studentId, $classId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}

