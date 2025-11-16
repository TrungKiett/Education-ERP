<?php
/**
 * File kết nối với database MySQL
 * Database: edu
 * 
 * Cách sử dụng:
 * require_once 'connect.php';
 * // Sau đó có thể sử dụng biến $conn để truy vấn database
 */

// Cấu hình kết nối database
$db_host = 'localhost';    // Địa chỉ MySQL server
$db_user = 'root';         // Tên người dùng MySQL
$db_pass = '';             // Mật khẩu MySQL (để trống nếu không có)
$db_name = 'edu';          // Tên database

// Kết nối database
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    
    // Thiết lập charset UTF-8 để hỗ trợ tiếng Việt
    $conn->set_charset("utf8mb4");
    
    // Kiểm tra kết nối (chỉ hiển thị khi test)
    if (isset($_GET['test']) || (defined('DEBUG') && DEBUG)) {
        if (!$conn->connect_error) {
            echo "✓ Kết nối database '$db_name' thành công!<br>";
            echo "Host: <strong>$db_host</strong><br>";
            echo "User: <strong>$db_user</strong><br>";
            echo "MySQL Version: " . $conn->server_info . "<br><br>";
        }
    }
    
} catch (Exception $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Hàm lấy kết nối (để tương thích với code cũ)
function getConnection() {
    global $conn;
    return $conn;
}

// Hàm đóng kết nối
function closeConnection($connection = null) {
    global $conn;
    $connection = $connection ?? $conn;
    if (isset($connection) && $connection) {
        $connection->close();
    }
}

?>

