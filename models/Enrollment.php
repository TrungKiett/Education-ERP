<?php
require_once __DIR__ . '/Model.php';

class Enrollment extends Model {
    protected $table = 'enrollment_applications';
    
    public function findByStatus($status) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getPendingCount() {
        $result = $this->getConnection()->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'");
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    public function updateStatus($id, $status, $notes = null) {
        $data = ['status' => $status];
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        return $this->update($id, $data);
    }
}

