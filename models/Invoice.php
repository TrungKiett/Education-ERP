<?php
require_once __DIR__ . '/Model.php';

class Invoice extends Model {
    protected $table = 'invoices';
    
    public function getByStudentId($studentId) {
        $sql = "SELECT i.*, 
                       c.name as course_name,
                       e.enrollment_date
                FROM {$this->table} i
                JOIN courses c ON i.course_id = c.id
                JOIN enrollments e ON i.enrollment_id = e.id
                WHERE i.student_id = ?
                ORDER BY i.created_at DESC";
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
    
    public function getUnpaidByStudentId($studentId) {
        return $this->findAll("student_id = ? AND status = 'unpaid'", [$studentId], "due_date ASC");
    }
    
    public function findByInvoiceNumber($invoiceNumber) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE invoice_number = ?");
        $stmt->bind_param("s", $invoiceNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function updatePaymentStatus($id, $status, $paymentMethod = null, $paidDate = null) {
        $data = ['status' => $status];
        if ($paymentMethod) {
            $data['payment_method'] = $paymentMethod;
        }
        if ($paidDate) {
            $data['paid_date'] = $paidDate;
        }
        return $this->update($id, $data);
    }
}

