<?php
// Test database connection
require_once 'config/database.php';

echo "<h2>Kiểm tra kết nối database 'edu'</h2>";

try {
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Kết nối database thành công!</p>";
        
        // List all tables
        echo "<h3>Danh sách bảng trong database:</h3>";
        $result = $conn->query("SHOW TABLES");
        
        if ($result && $result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li><strong>" . $row[0] . "</strong></li>";
            }
            echo "</ul>";
            
            // Check if required tables exist
            echo "<h3>Kiểm tra các bảng cần thiết:</h3>";
            $requiredTables = ['users', 'teachers', 'students', 'classrooms', 'subjects', 'schedules', 'teaching_assignments'];
            
            $existingTables = [];
            $result2 = $conn->query("SHOW TABLES");
            while ($row = $result2->fetch_array()) {
                $existingTables[] = $row[0];
            }
            
            echo "<ul>";
            foreach ($requiredTables as $table) {
                if (in_array($table, $existingTables)) {
                    echo "<li style='color: green;'>✓ Bảng <strong>$table</strong> tồn tại</li>";
                } else {
                    echo "<li style='color: red;'>✗ Bảng <strong>$table</strong> không tồn tại</li>";
                }
            }
            echo "</ul>";
            
            // Check structure of schedules table
            if (in_array('schedules', $existingTables)) {
                echo "<h3>Cấu trúc bảng 'schedules':</h3>";
                $result3 = $conn->query("DESCRIBE schedules");
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $result3->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Field'] . "</td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            // Check structure of teaching_assignments table
            if (in_array('teaching_assignments', $existingTables)) {
                echo "<h3>Cấu trúc bảng 'teaching_assignments':</h3>";
                $result4 = $conn->query("DESCRIBE teaching_assignments");
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $result4->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Field'] . "</td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } else {
            echo "<p style='color: orange;'>Database không có bảng nào</p>";
        }
        
        closeDBConnection($conn);
        
    } else {
        echo "<p style='color: red;'>✗ Không thể kết nối database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<br><a href='login.php'>← Quay lại trang đăng nhập</a>";
?>

