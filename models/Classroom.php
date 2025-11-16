<?php
require_once __DIR__ . '/Model.php';

class Classroom extends Model {
    protected $table = 'classrooms';
    
    public function getAllWithStudentCount() {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count
                FROM {$this->table} c 
                ORDER BY c.name";
        $result = $this->getConnection()->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

