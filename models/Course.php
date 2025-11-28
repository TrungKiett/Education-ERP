<?php
require_once __DIR__ . '/Model.php';

class Course extends Model {
    protected $table = 'courses';
    
    public function getActiveCourses() {
        return $this->findAll("status = 'active'", [], "name ASC");
    }
    
    public function getWithPrice() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name";
        $result = $this->getConnection()->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

