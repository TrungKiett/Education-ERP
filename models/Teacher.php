<?php
require_once __DIR__ . '/Model.php';

class Teacher extends Model {
    protected $table = 'teachers';
    
    public function getWithUser() {
        $sql = "SELECT t.*, u.username 
                FROM {$this->table} t 
                LEFT JOIN users u ON u.profile_id = t.id AND u.role = 'teacher'
                ORDER BY t.full_name";
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
}

