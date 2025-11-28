<?php
require_once __DIR__ . '/Model.php';

class Transaction extends Model {
    protected $table = 'transactions';
    
    public function findByTransactionCode($transactionCode) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE transaction_code = ?");
        $stmt->bind_param("s", $transactionCode);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getByInvoiceId($invoiceId) {
        return $this->findAll("invoice_id = ?", [$invoiceId], "created_at DESC");
    }
}

