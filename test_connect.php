<?php
/**
 * File test kết nối database
 * Truy cập: http://localhost/edu/test_connect.php
 */

require_once 'connect.php';

echo "<h2>Kiểm tra kết nối database 'edu'</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
</style>";

if (isset($conn) && $conn) {
    echo "<p class='success'>✓ Kết nối database thành công!</p>";
    echo "<p><strong>Database:</strong> edu</p>";
    echo "<p><strong>Host:</strong> localhost</p>";
    echo "<p><strong>User:</strong> root</p>";
    
    // Liệt kê tất cả các bảng
    echo "<h3>Danh sách các bảng trong database:</h3>";
    $result = $conn->query("SHOW TABLES");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>STT</th><th>Tên bảng</th><th>Số hàng</th></tr>";
        $i = 1;
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            // Đếm số hàng
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
            $countRow = $countResult->fetch_assoc();
            $rowCount = $countRow['count'];
            
            echo "<tr>";
            echo "<td>$i</td>";
            echo "<td><strong>$tableName</strong></td>";
            echo "<td>$rowCount</td>";
            echo "</tr>";
            $i++;
        }
        echo "</table>";
        
        // Kiểm tra các bảng cần thiết
        echo "<h3>Kiểm tra các bảng cần thiết:</h3>";
        $requiredTables = [
            'users' => 'Bảng người dùng',
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
                echo "<td class='success'>✓ Tồn tại</td>";
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td><strong>$table</strong></td>";
                echo "<td>$description</td>";
                echo "<td class='error'>✗ Không tồn tại</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>Database không có bảng nào!</p>";
    }
    
} else {
    echo "<p class='error'>✗ Không thể kết nối database</p>";
}

echo "<br><hr>";
echo "<a href='login.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại trang đăng nhập</a>";
?>

