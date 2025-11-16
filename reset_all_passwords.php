<?php
/**
 * Reset tất cả mật khẩu về 123
 * Truy cập: http://localhost/edu/reset_all_passwords.php
 */

require_once 'config/database.php';

$defaultPassword = '123';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Reset Tất cả Mật khẩu</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 700px; margin: 0 auto; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; padding: 15px; background: #e8f5e9; margin: 10px 0; border-left: 4px solid green; border-radius: 5px; }
        .error { color: red; padding: 15px; background: #ffebee; margin: 10px 0; border-left: 4px solid red; border-radius: 5px; }
        .warning { color: orange; padding: 15px; background: #fff3e0; margin: 10px 0; border-left: 4px solid orange; border-radius: 5px; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; margin: 15px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        button { padding: 12px 30px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 5px; font-size: 16px; margin: 10px 5px; }
        button:hover { background: #45a049; }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #d32f2f; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .default-password { font-family: monospace; background: #f5f5f5; padding: 5px 10px; border-radius: 3px; color: #d32f2f; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Reset Tất cả Mật khẩu</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $conn = getDBConnection();
    
    // Tạo password hash mới
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    // Reset tất cả mật khẩu
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?");
    $stmt->bind_param("s", $hashedPassword);
    
    if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        echo "<div class='success'>✓ Reset mật khẩu thành công cho <strong>$affectedRows</strong> tài khoản!</div>";
        echo "<div class='success'><strong>Mật khẩu mới cho TẤT CẢ tài khoản:</strong> <span class='default-password'>$defaultPassword</span></div>";
        
        // Hiển thị danh sách users
        echo "<h2>📋 Danh sách Tài khoản:</h2>";
        $result = $conn->query("SELECT id, username, role FROM users ORDER BY role, username");
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Mật khẩu</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
                echo "<td>" . strtoupper($row['role']) . "</td>";
                echo "<td><span class='default-password'>$defaultPassword</span></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<div class='info'><strong>✅ Bây giờ bạn có thể đăng nhập với:</strong><br>";
        echo "<strong>Username:</strong> Tên bất kỳ trong danh sách trên<br>";
        echo "<strong>Password:</strong> <span class='default-password'>$defaultPassword</span></div>";
        
        echo "<a href='login.php' style='display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>🔑 Đi đến trang đăng nhập</a>";
        
    } else {
        echo "<div class='error'>✗ Lỗi khi reset: " . $conn->error . "</div>";
    }
    
    $stmt->close();
    closeDBConnection($conn);
    
} else {
    // Hiển thị form xác nhận
    echo "<div class='warning'><strong>⚠️ CẢNH BÁO:</strong> Thao tác này sẽ reset mật khẩu của TẤT CẢ tài khoản về <span class='default-password'>$defaultPassword</span></div>";
    
    echo "<div class='info'><strong>Thông tin:</strong><br>";
    echo "- Tất cả users sẽ có mật khẩu: <span class='default-password'>$defaultPassword</span><br>";
    echo "- Sau khi reset, bạn có thể đăng nhập với bất kỳ username nào và mật khẩu <span class='default-password'>$defaultPassword</span></div>";
    
    // Hiển thị danh sách users hiện tại
    $conn = getDBConnection();
    $result = $conn->query("SELECT id, username, role FROM users ORDER BY role, username");
    
    if ($result && $result->num_rows > 0) {
        echo "<h2>📋 Danh sách Users hiện tại:</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
            echo "<td>" . strtoupper($row['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    closeDBConnection($conn);
    
    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='1'>";
    echo "<button type='submit' class='btn-danger'>⚠️ Xác nhận Reset TẤT CẢ Mật khẩu</button>";
    echo "</form>";
}

echo "    </div>
</body>
</html>";
?>

