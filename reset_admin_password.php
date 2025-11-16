<?php
/**
 * Script reset mật khẩu admin
 * Truy cập: http://localhost/edu/reset_admin_password.php
 * 
 * LƯU Ý: Xóa file này sau khi đã reset mật khẩu để bảo mật!
 */

require_once 'config/database.php';

$newPassword = $_POST['new_password'] ?? '123';
$username = $_POST['username'] ?? 'admin';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Reset Admin Password</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 600px; margin: 0 auto; }
        .success { color: green; padding: 10px; background: #e8f5e9; margin: 10px 0; border-left: 4px solid green; }
        .error { color: red; padding: 10px; background: #ffebee; margin: 10px 0; border-left: 4px solid red; }
        .warning { color: orange; padding: 10px; background: #fff3e0; margin: 10px 0; border-left: 4px solid orange; }
        input, button { padding: 10px; margin: 5px; width: 100%; box-sizing: border-box; }
        button { background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h2>🔧 Reset Mật khẩu Admin</h2>
    <div class='warning'><strong>⚠ CẢNH BÁO:</strong> Xóa file này sau khi đã reset mật khẩu!</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    // Tạo password hash mới
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Cập nhật password trong database
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<div class='success'>✓ Reset mật khẩu thành công!</div>";
            echo "<div class='success'><strong>Username:</strong> $username<br>";
            echo "<strong>Mật khẩu mới:</strong> $newPassword</div>";
            echo "<div class='success'>Bây giờ bạn có thể đăng nhập với mật khẩu mới!</div>";
            echo "<a href='login.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; margin-top: 10px;'>Đi đến trang đăng nhập</a>";
        } else {
            echo "<div class='error'>✗ Không tìm thấy user '$username' để cập nhật!</div>";
        }
    } else {
        echo "<div class='error'>✗ Lỗi khi cập nhật: " . $conn->error . "</div>";
    }
    
    $stmt->close();
    closeDBConnection($conn);
    
} else {
    // Form reset
    echo "<form method='POST'>
        <div>
            <label>Username:</label>
            <input type='text' name='username' value='$username' required>
        </div>
        <div>
            <label>Mật khẩu mới:</label>
            <input type='text' name='new_password' value='$newPassword' required>
        </div>
        <button type='submit'>Reset Mật khẩu</button>
    </form>";
    
    // Hiển thị danh sách users hiện tại
    echo "<hr><h3>Danh sách users hiện tại:</h3>";
    $conn = getDBConnection();
    $result = $conn->query("SELECT id, username, role FROM users ORDER BY id");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    closeDBConnection($conn);
}

echo "</body></html>";
?>

