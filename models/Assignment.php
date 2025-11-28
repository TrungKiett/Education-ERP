<?php
require_once __DIR__ . '/Model.php';

class Assignment extends Model {
    protected $table = 'assignments';
    
    public function getByClassId($classId) {
        $sql = "SELECT a.*, 
                       t.full_name as teacher_name,
                       s.name as subject_name
                FROM {$this->table} a
                JOIN teachers t ON a.teacher_id = t.id
                LEFT JOIN subjects s ON a.subject_id = s.id
                WHERE a.class_id = ? AND a.status = 'published'
                ORDER BY a.due_date ASC";
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
    
    public function getByTeacherId($teacherId) {
        $sql = "SELECT a.*, 
                       c.name as class_name,
                       s.name as subject_name,
                       (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
                FROM {$this->table} a
                JOIN classrooms c ON a.class_id = c.id
                LEFT JOIN subjects s ON a.subject_id = s.id
                WHERE a.teacher_id = ?
                ORDER BY a.created_at DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getPendingGrading($teacherId) {
        $sql = "SELECT a.*, 
                       s.id as submission_id,
                       s.student_id,
                       st.full_name as student_name,
                       s.submitted_at,
                       c.name as class_name
                FROM {$this->table} a
                JOIN submissions s ON a.id = s.assignment_id
                JOIN students st ON s.student_id = st.id
                JOIN classrooms c ON a.class_id = c.id
                WHERE a.teacher_id = ? AND s.status = 'submitted' AND s.score IS NULL
                ORDER BY s.submitted_at ASC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

