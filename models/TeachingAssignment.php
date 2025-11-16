<?php
require_once __DIR__ . '/Model.php';

class TeachingAssignment extends Model {
    protected $table = 'teaching_assignments';
    
    public function getByTeacherId($teacherId) {
        $sql = "SELECT ta.*, 
                       s.name as subject_name,
                       c.name as class_name
                FROM {$this->table} ta
                JOIN subjects s ON ta.subject_id = s.id
                JOIN classrooms c ON ta.class_id = c.id
                WHERE ta.teacher_id = ?
                ORDER BY c.name, s.name";
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        return $data;
    }
    
    public function checkAssignment($teacherId, $subjectId, $classId) {
        $stmt = $this->getConnection()->prepare("SELECT id FROM {$this->table} WHERE teacher_id = ? AND subject_id = ? AND class_id = ?");
        $stmt->bind_param("iii", $teacherId, $subjectId, $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}

