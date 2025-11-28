<?php
require_once __DIR__ . '/Model.php';

class Discount extends Model {
    protected $table = 'discounts';
    
    public function findByCode($code) {
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM {$this->table} 
             WHERE code = ? AND status = 'active' 
             AND (start_date IS NULL OR start_date <= CURDATE())
             AND (end_date IS NULL OR end_date >= CURDATE())
             AND (usage_limit IS NULL OR used_count < usage_limit)"
        );
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function calculateDiscount($discount, $amount) {
        if (!$discount) {
            return 0;
        }
        
        $discountAmount = 0;
        
        if ($discount['type'] === 'percentage') {
            $discountAmount = ($amount * $discount['value']) / 100;
            if ($discount['max_discount']) {
                $discountAmount = min($discountAmount, $discount['max_discount']);
            }
        } else {
            $discountAmount = $discount['value'];
        }
        
        // Kiểm tra min_amount
        if ($discount['min_amount'] && $amount < $discount['min_amount']) {
            return 0;
        }
        
        return min($discountAmount, $amount); // Không được giảm nhiều hơn số tiền
    }
    
    public function incrementUsage($id) {
        $stmt = $this->getConnection()->prepare(
            "UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

