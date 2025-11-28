<?php
require_once __DIR__ . '/Model.php';

class AcademicResult extends Model {
    protected $table = 'academic_results';
    
    public function getByStudentId($studentId, $semester = null, $academicYear = null) {
        $sql = "SELECT ar.*, 
                       s.name as subject_name,
                       c.name as class_name,
                       a.title as assignment_title
                FROM {$this->table} ar
                JOIN subjects s ON ar.subject_id = s.id
                JOIN classrooms c ON ar.class_id = c.id
                LEFT JOIN assignments a ON ar.assignment_id = a.id
                WHERE ar.student_id = ?";
        
        $params = [$studentId];
        $types = 'i';
        
        if ($semester) {
            $sql .= " AND ar.semester = ?";
            $params[] = $semester;
            $types .= 's';
        }
        
        if ($academicYear) {
            $sql .= " AND ar.academic_year = ?";
            $params[] = $academicYear;
            $types .= 's';
        }
        
        $sql .= " ORDER BY ar.created_at DESC";
        
        $stmt = $this->getConnection()->prepare($sql);
        if (count($params) > 1) {
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param($types, $studentId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

