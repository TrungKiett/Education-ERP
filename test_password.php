<?php
/**
 * File kiểm tra và test password hash
 * Truy cập: http://localhost/edu/test_password.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Test Password</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; padding: 10px; background: #e8f5e9; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #e3f2fd; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        input, button { padding: 8px; margin: 5px; }
    </style>
</head>
<body>
    <h2>🔐 Kiểm tra Password Hash</h2>";

// Lấy username từ form hoặc URL
$username = $_GET['username'] ?? $_POST['username'] ?? 'admin';
$password = $_POST['password'] ?? '123';

echo "<form method='POST'>
    <div>
        <label>Username:</label>
        <input type='text' name='username' value='$username' required>
    </div>
    <div>
        <label>Password (plain text):</label>
        <input type='text' name='password' value='$password' required>
    </div>
    <button type='submit'>Kiểm tra</button>
</form>
<hr>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['username'])) {
    $conn = getDBConnection();
    
    // Lấy thông tin user từ database
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        echo "<div class='info'><strong>Thông tin user:</strong></div>";
        echo "<table>";
        echo "<tr><th>ID</th><td>" . $user['id'] . "</td></tr>";
        echo "<tr><th>Username</th><td>" . $user['username'] . "</td></tr>";
        echo "<tr><th>Role</th><td>" . $user['role'] . "</td></tr>";
        echo "<tr><th>Password Hash</th><td style='word-break: break-all;'>" . htmlspecialchars($user['password_hash']) . "</td></tr>";
        echo "</table>";
        
        echo "<div class='info'><strong>Kiểm tra password:</strong></div>";
        echo "<p>Password nhập vào: <strong>$password</strong></p>";
        
        // Thử verify với password_verify
        if (password_verify($password, $user['password_hash'])) {
            echo "<div class='success'>✓ password_verify() = TRUE - Mật khẩu đúng!</div>";
        } else {
            echo "<div class='error'>✗ password_verify() = FALSE - Mật khẩu sai!</div>";
            
            // Tạo hash mới cho password 123
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            echo "<div class='info'><strong>Hash mới cho password '$password':</strong><br>";
            echo "<code style='word-break: break-all;'>$newHash</code></div>";
            
            echo "<div class='info'><strong>Để cập nhật mật khẩu trong database, chạy SQL:</strong><br>";
            echo "<code>UPDATE users SET password_hash = '$newHash' WHERE username = '$username';</code></div>";
        }
        
        // Kiểm tra xem password_hash có đúng format không
        if (strlen($user['password_hash']) < 60) {
            echo "<div class='error'>⚠ Password hash có vẻ không đúng format (quá ngắn). Cần được hash lại!</div>";
        }
        
    } else {
        echo "<div class='error'>✗ Không tìm thấy user '$username' trong database!</div>";
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

echo "</body></html>";
?>

