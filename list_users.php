<?php
/**
 * File liệt kê danh sách users và mật khẩu mặc định
 * Truy cập: http://localhost/edu/list_users.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Danh sách Tài khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
            padding: 10px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
        }
        .info {
            color: #2196F3;
            padding: 15px;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .role-admin {
            background: #ffcdd2;
            color: #c62828;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .role-teacher {
            background: #c8e6c9;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .role-student {
            background: #bbdefb;
            color: #1565c0;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .default-password {
            font-family: monospace;
            background: #f5f5f5;
            padding: 5px 10px;
            border-radius: 3px;
            color: #d32f2f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📋 Danh sách Tài khoản Đăng nhập</h1>";

try {
    $conn = getDBConnection();
    
    // Lấy tất cả users
    $result = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY role, username");
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='info'>
            <strong>ℹ️ Hướng dẫn:</strong><br>
            1. Mật khẩu mặc định cho tất cả tài khoản là: <span class='default-password'>123</span><br>
            2. Nếu không đăng nhập được, hãy sử dụng <a href='reset_admin_password.php' style='color: #1976d2; font-weight: bold;'>reset_admin_password.php</a> để reset mật khẩu<br>
            3. Sau khi reset, mật khẩu sẽ là <span class='default-password'>123</span>
        </div>";
        
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Mật khẩu mặc định</th>
                <th>Ngày tạo</th>
              </tr>";
        
        $defaultPassword = '123';
        
        while ($row = $result->fetch_assoc()) {
            $roleClass = 'role-' . $row['role'];
            $roleText = strtoupper($row['role']);
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
            echo "<td><span class='$roleClass'>$roleText</span></td>";
            echo "<td><span class='default-password'>$defaultPassword</span></td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Tóm tắt theo role
        echo "<h2>📊 Tóm tắt theo Role:</h2>";
        
        $adminResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $adminRow = $adminResult->fetch_assoc();
        
        $teacherResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'");
        $teacherRow = $teacherResult->fetch_assoc();
        
        $studentResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
        $studentRow = $studentResult->fetch_assoc();
        
        echo "<div class='info'>";
        echo "<strong>Admin:</strong> " . $adminRow['count'] . " tài khoản<br>";
        echo "<strong>Teacher:</strong> " . $teacherRow['count'] . " tài khoản<br>";
        echo "<strong>Student:</strong> " . $studentRow['count'] . " tài khoản<br>";
        echo "</div>";
        
        // Hiển thị thông tin đăng nhập
        echo "<h2>🔐 Thông tin Đăng nhập:</h2>";
        echo "<div class='info'>";
        echo "<strong>Để đăng nhập, sử dụng:</strong><br><br>";
        
        // Lấy lại danh sách users
        $result2 = $conn->query("SELECT username, role FROM users ORDER BY role, username");
        while ($user = $result2->fetch_assoc()) {
            echo "<strong>Username:</strong> <span class='default-password'>" . htmlspecialchars($user['username']) . "</span> ";
            echo "<strong>Role:</strong> " . strtoupper($user['role']) . " ";
            echo "<strong>Password:</strong> <span class='default-password'>123</span><br>";
        }
        
        echo "<br><strong>⚠️ Lưu ý:</strong> Nếu mật khẩu <span class='default-password'>123</span> không đúng, ";
        echo "vui lòng <a href='reset_admin_password.php' style='color: #1976d2; font-weight: bold;'>reset mật khẩu</a> trước.";
        echo "</div>";
        
    } else {
        echo "<div class='info'>Không có user nào trong database!</div>";
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    echo "<div class='error'>Lỗi: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<a href='login.php' class='btn'>🔑 Đi đến trang đăng nhập</a> ";
echo "<a href='reset_admin_password.php' class='btn btn-danger'>🔧 Reset mật khẩu</a>";

echo "    </div>
</body>
</html>";
?>

