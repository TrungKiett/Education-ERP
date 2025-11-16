<?php
/**
 * File kiểm tra kết nối với database edu
 * Truy cập: http://localhost/edu/check_connection.php
 */

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Kiểm tra kết nối Database</title>
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
        .error {
            color: #f44336;
            font-weight: bold;
            padding: 10px;
            background: #ffebee;
            border-left: 4px solid #f44336;
            margin: 10px 0;
        }
        .info {
            color: #2196F3;
            padding: 10px;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Kiểm tra kết nối Database 'edu'</h1>";

// Cấu hình database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'edu';

echo "<div class='info'><strong>Thông tin kết nối:</strong><br>";
echo "Host: <strong>$db_host</strong><br>";
echo "User: <strong>$db_user</strong><br>";
echo "Database: <strong>$db_name</strong></div>";

// Thử kết nối
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        echo "<div class='error'>✗ Kết nối thất bại!</div>";
        echo "<div class='error'>Lỗi: " . $conn->connect_error . "</div>";
    } else {
        echo "<div class='success'>✓ Kết nối database thành công!</div>";
        
        // Thiết lập charset
        $conn->set_charset("utf8mb4");
        
        // Lấy thông tin phiên bản MySQL
        $version = $conn->server_info;
        echo "<div class='info'><strong>MySQL Version:</strong> $version</div>";
        
        // Liệt kê tất cả các bảng
        echo "<h2>📋 Danh sách các bảng trong database:</h2>";
        $result = $conn->query("SHOW TABLES");
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>STT</th><th>Tên bảng</th><th>Số hàng</th><th>Kích thước</th></tr>";
            $i = 1;
            while ($row = $result->fetch_array()) {
                $tableName = $row[0];
                
                // Đếm số hàng
                $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
                $countRow = $countResult->fetch_assoc();
                $rowCount = $countRow['count'];
                
                // Lấy kích thước bảng
                $sizeResult = $conn->query("
                    SELECT 
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb 
                    FROM information_schema.TABLES 
                    WHERE table_schema = '$db_name' 
                    AND table_name = '$tableName'
                ");
                $sizeRow = $sizeResult->fetch_assoc();
                $size = $sizeRow ? $sizeRow['size_mb'] . ' MB' : 'N/A';
                
                echo "<tr>";
                echo "<td>$i</td>";
                echo "<td><strong>$tableName</strong></td>";
                echo "<td>$rowCount</td>";
                echo "<td>$size</td>";
                echo "</tr>";
                $i++;
            }
            echo "</table>";
            
            // Kiểm tra các bảng cần thiết
            echo "<h2>✅ Kiểm tra các bảng cần thiết:</h2>";
            $requiredTables = [
                'users' => 'Bảng người dùng (đăng nhập)',
                'teachers' => 'Bảng giáo viên',
                'students' => 'Bảng học sinh',
                'classrooms' => 'Bảng lớp học',
                'subjects' => 'Bảng môn học',
                'schedules' => 'Bảng lịch dạy',
                'teaching_assignments' => 'Bảng phân công môn dạy'
            ];
            
            $result2 = $conn->query("SHOW TABLES");
            $existingTables = [];
            while ($row = $result2->fetch_array()) {
                $existingTables[] = $row[0];
            }
            
            echo "<table>";
            echo "<tr><th>Bảng</th><th>Mô tả</th><th>Trạng thái</th></tr>";
            foreach ($requiredTables as $table => $description) {
                if (in_array($table, $existingTables)) {
                    echo "<tr>";
                    echo "<td><strong>$table</strong></td>";
                    echo "<td>$description</td>";
                    echo "<td><span style='color: #4CAF50; font-weight: bold;'>✓ Tồn tại</span></td>";
                    echo "</tr>";
                } else {
                    echo "<tr>";
                    echo "<td><strong>$table</strong></td>";
                    echo "<td>$description</td>";
                    echo "<td><span style='color: #f44336; font-weight: bold;'>✗ Không tồn tại</span></td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
            
            // Kiểm tra cấu trúc bảng users
            echo "<h2>🔑 Kiểm tra cấu trúc bảng 'users':</h2>";
            if (in_array('users', $existingTables)) {
                $result3 = $conn->query("DESCRIBE users");
                echo "<table>";
                echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $result3->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><strong>" . $row['Field'] . "</strong></td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Kiểm tra xem có cột password_hash không
                $columnsResult = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
                if ($columnsResult->num_rows > 0) {
                    echo "<div class='success'>✓ Bảng 'users' có cột 'password_hash' - Đúng!</div>";
                } else {
                    echo "<div class='error'>✗ Bảng 'users' KHÔNG có cột 'password_hash' - Cần kiểm tra lại!</div>";
                }
                
                // Hiển thị số lượng user
                $userCount = $conn->query("SELECT COUNT(*) as count FROM users");
                $userCountRow = $userCount->fetch_assoc();
                echo "<div class='info'><strong>Số lượng người dùng:</strong> " . $userCountRow['count'] . "</div>";
            }
            
        } else {
            echo "<div class='error'>Database không có bảng nào!</div>";
        }
        
        $conn->close();
        
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Lỗi kết nối!</div>";
    echo "<div class='error'>Chi tiết: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<a href='login.php' class='btn'>← Quay lại trang đăng nhập</a>";
echo " <a href='index.php' class='btn' style='background: #2196F3;'>🏠 Trang chủ</a>";

echo "    </div>
</body>
</html>";
?>

