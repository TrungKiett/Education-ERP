<?php
require_once __DIR__ . '/Model.php';

class Schedule extends Model {
    protected $table = 'schedules';
    
    public function getWithDetails($filters = []) {
        $sql = "SELECT ts.*, 
                       t.full_name as teacher_name, 
                       c.name as class_name, 
                       s.name as subject_name 
                FROM {$this->table} ts
                JOIN teachers t ON ts.teacher_id = t.id
                JOIN classrooms c ON ts.class_id = c.id
                JOIN subjects s ON ts.subject_id = s.id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if (!empty($filters['date'])) {
            $sql .= " AND ts.schedule_date = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        
        if (!empty($filters['class_id'])) {
            $sql .= " AND ts.class_id = ?";
            $params[] = $filters['class_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['teacher_id'])) {
            $sql .= " AND ts.teacher_id = ?";
            $params[] = $filters['teacher_id'];
            $types .= 'i';
        }
        
        $sql .= " ORDER BY ts.schedule_date, ts.period";
        
        if (!empty($params)) {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $result = $this->getConnection()->query($sql);
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function checkConflict($teacherId, $classId, $scheduleDate, $period, $room = null, $excludeId = null) {
        $conflicts = [];
        
        // Check teacher conflict
        $sql = "SELECT id FROM {$this->table} WHERE teacher_id = ? AND schedule_date = ? AND period = ?";
        $params = [$teacherId, $scheduleDate, $period];
        $types = 'isi';
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $conflicts[] = 'Giáo viên đã có lịch dạy trong thời gian này';
        }
        $stmt->close();
        
        // Check class conflict
        $sql = "SELECT id FROM {$this->table} WHERE class_id = ? AND schedule_date = ? AND period = ?";
        $params = [$classId, $scheduleDate, $period];
        $types = 'isi';
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $conflicts[] = 'Lớp học đã có lịch học trong thời gian này';
        }
        $stmt->close();
        
        // Check room conflict
        if (!empty($room)) {
            $sql = "SELECT id FROM {$this->table} WHERE room = ? AND schedule_date = ? AND period = ?";
            $params = [$room, $scheduleDate, $period];
            $types = 'ssi';
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
                $types .= 'i';
            }
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $conflicts[] = 'Phòng học đã được sử dụng trong thời gian này';
            }
            $stmt->close();
        }
        
        return $conflicts;
    }
    
    public function getByTeacherId($teacherId, $filters = []) {
        $sql = "SELECT ts.*, 
                       c.name as class_name, 
                       s.name as subject_name,
                       s.code as subject_code
                FROM {$this->table} ts
                JOIN classrooms c ON ts.class_id = c.id
                JOIN subjects s ON ts.subject_id = s.id
                WHERE ts.teacher_id = ?";
        
        $params = [$teacherId];
        $types = 'i';
        
        if (!empty($filters['date'])) {
            $sql .= " AND ts.schedule_date = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $sql .= " AND ts.schedule_date >= ? AND ts.schedule_date <= ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
            $types .= 'ss';
        }
        
        $sql .= " ORDER BY ts.schedule_date, ts.period";
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        return $data;
    }
    
    public function getByClassId($classId, $filters = []) {
        $sql = "SELECT ts.*, 
                       t.full_name as teacher_name,
                       s.name as subject_name,
                       s.code as subject_code
                FROM {$this->table} ts
                JOIN teachers t ON ts.teacher_id = t.id
                JOIN subjects s ON ts.subject_id = s.id
                WHERE ts.class_id = ?";
        
        $params = [$classId];
        $types = 'i';
        
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $sql .= " AND ts.schedule_date >= ? AND ts.schedule_date <= ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
            $types .= 'ss';
        }
        
        $sql .= " ORDER BY ts.schedule_date, ts.period";
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        return $data;
    }
}

