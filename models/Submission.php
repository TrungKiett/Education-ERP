<?php
require_once __DIR__ . '/Model.php';

class Submission extends Model {
    protected $table = 'submissions';
    
    public function getByAssignmentId($assignmentId) {
        $sql = "SELECT s.*, 
                       st.full_name as student_name,
                       st.code as student_code
                FROM {$this->table} s
                JOIN students st ON s.student_id = st.id
                WHERE s.assignment_id = ?
                ORDER BY s.submitted_at DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getByStudentId($studentId) {
        $sql = "SELECT s.*, 
                       a.title as assignment_title,
                       a.due_date,
                       a.max_score,
                       c.name as class_name
                FROM {$this->table} s
                JOIN assignments a ON s.assignment_id = a.id
                JOIN classrooms c ON a.class_id = c.id
                WHERE s.student_id = ?
                ORDER BY s.submitted_at DESC";
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
    
    public function getByStudentAndAssignment($studentId, $assignmentId) {
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM {$this->table} WHERE student_id = ? AND assignment_id = ?"
        );
        $stmt->bind_param("ii", $studentId, $assignmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function updateGrade($id, $score, $feedback, $gradedBy) {
        $data = [
            'score' => $score,
            'feedback' => $feedback,
            'graded_by' => $gradedBy,
            'graded_at' => date('Y-m-d H:i:s'),
            'status' => 'graded'
        ];
        return $this->update($id, $data);
    }
}

