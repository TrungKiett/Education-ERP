<?php
require_once __DIR__ . '/Model.php';

class Student extends Model {
    protected $table = 'students';
    
    public function getWithClassroom() {
        $sql = "SELECT s.*, c.name as class_name 
                FROM {$this->table} s 
                LEFT JOIN classrooms c ON s.class_id = c.id
                ORDER BY s.full_name";
        $result = $this->getConnection()->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getByProfileId($profileId) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $profileId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getByClassId($classId) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE class_id = ? ORDER BY full_name");
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

