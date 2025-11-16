<?php
require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    
    public function findByUsername($username) {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        // Check password: can be plain text or hash
        $passwordValid = false;
        
        // If password_hash starts with $2y$ or $2a$, it's bcrypt hash
        if (strpos($user['password_hash'], '$2y$') === 0 || strpos($user['password_hash'], '$2a$') === 0) {
            // Use password_verify for hash
            $passwordValid = password_verify($password, $user['password_hash']);
        } else {
            // Direct comparison for plain text (like admin = "123")
            $passwordValid = ($password === $user['password_hash']);
        }
        
        return $passwordValid ? $user : false;
    }
    
    public function createUser($username, $password, $role, $profileId = null, $email = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'username' => $username,
            'password_hash' => $hashedPassword,
            'role' => $role
        ];
        
        if ($profileId !== null) {
            $data['profile_id'] = $profileId;
        }
        
        if ($email !== null) {
            $data['email'] = $email;
        }
        
        return $this->create($data);
    }
}

