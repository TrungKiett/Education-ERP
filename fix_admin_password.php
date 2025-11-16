<?php
/**
 * Fix password cho admin: Tạo hash cho password "123"
 * Truy cập: http://localhost/edu/fix_admin_password.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Fix Admin Password</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 700px; margin: 0 auto; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; padding: 15px; background: #e8f5e9; margin: 10px 0; border-left: 4px solid green; border-radius: 5px; }
        .error { color: red; padding: 15px; background: #ffebee; margin: 10px 0; border-left: 4px solid red; border-radius: 5px; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; margin: 15px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .btn { padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Fix Mật khẩu Admin</h1>";

$conn = getDBConnection();

// Lấy tất cả users
$result = $conn->query("SELECT id, username, password_hash, role FROM users ORDER BY id");

if ($result && $result->num_rows > 0) {
    echo "<h2>📋 Danh sách Users:</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Role</th><th>Trạng thái</th></tr>";
    
    $usersToFix = [];
    
    while ($row = $result->fetch_assoc()) {
        $isHash = (strpos($row['password_hash'], '$2y$') === 0 || strpos($row['password_hash'], '$2a$') === 0);
        $status = $isHash ? "✓ Hash hợp lệ" : "⚠ Plain text";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td style='word-break: break-all; font-size: 11px;'>" . htmlspecialchars($row['password_hash']) . "</td>";
        echo "<td>" . strtoupper($row['role']) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
        
        if (!$isHash) {
            $usersToFix[] = $row;
        }
    }
    
    echo "</table>";
    
    // Fix plain text passwords
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix'])) {
        $passwordToFix = $_POST['password'] ?? '123';
        $hashedPassword = password_hash($passwordToFix, PASSWORD_DEFAULT);
        
        $fixed = 0;
        foreach ($usersToFix as $user) {
            // Chỉ fix nếu password_hash là plain text (không phải hash)
            if (strpos($user['password_hash'], '$2y$') !== 0 && strpos($user['password_hash'], '$2a$') !== 0) {
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $user['id']);
                
                if ($stmt->execute()) {
                    $fixed++;
                }
                $stmt->close();
            }
        }
        
        if ($fixed > 0) {
            echo "<div class='success'>✓ Đã fix mật khẩu cho <strong>$fixed</strong> tài khoản!</div>";
            echo "<div class='info'>Mật khẩu cho các tài khoản này: <strong>$passwordToFix</strong></div>";
            echo "<div class='success'>Bây giờ tất cả mật khẩu đã được hash. Bạn có thể đăng nhập!</div>";
            echo "<a href='login.php' class='btn'>🔑 Đi đến trang đăng nhập</a>";
        } else {
            echo "<div class='info'>Không có tài khoản nào cần fix (tất cả đã là hash hợp lệ).</div>";
        }
        
    } else {
        if (count($usersToFix) > 0) {
            echo "<div class='info'><strong>Tìm thấy " . count($usersToFix) . " tài khoản có password dạng plain text.</strong></div>";
            echo "<div class='info'>Tài khoản cần fix:</div>";
            echo "<ul>";
            foreach ($usersToFix as $user) {
                echo "<li><strong>" . htmlspecialchars($user['username']) . "</strong> (Role: " . $user['role'] . ") - Password hiện tại: <strong>" . htmlspecialchars($user['password_hash']) . "</strong></li>";
            }
            echo "</ul>";
            
            echo "<form method='POST'>";
            echo "<div class='info'>";
            echo "<strong>Nhập mật khẩu để hash:</strong><br>";
            echo "<input type='text' name='password' value='123' style='padding: 10px; width: 200px; margin: 10px 0;' required><br>";
            echo "<small>(Mật khẩu này sẽ được hash và cập nhật cho các tài khoản plain text)</small>";
            echo "</div>";
            echo "<input type='hidden' name='fix' value='1'>";
            echo "<button type='submit' style='padding: 12px 30px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 5px;'>🔧 Fix Mật khẩu</button>";
            echo "</form>";
        } else {
            echo "<div class='success'>✓ Tất cả mật khẩu đã được hash đúng cách!</div>";
        }
    }
}

closeDBConnection($conn);

echo "    </div>
</body>
</html>";
?>

