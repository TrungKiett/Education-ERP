<?php
require_once 'config/database.php';
require_once 'config/session.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    $role = getCurrentRole();
    if ($role === 'admin') {
        header('Location: admin/index.php');
    } elseif ($role === 'teacher') {
        header('Location: teacher/index.php');
    } elseif ($role === 'student') {
        header('Location: student/index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra password: có thể là plain text hoặc hash
            $passwordValid = false;
            
            // Nếu password_hash bắt đầu bằng $2y$ hoặc $2a$, đó là bcrypt hash
            if (strpos($user['password_hash'], '$2y$') === 0 || strpos($user['password_hash'], '$2a$') === 0) {
                // Sử dụng password_verify cho hash
                $passwordValid = password_verify($password, $user['password_hash']);
            } else {
                // So sánh trực tiếp cho plain text (như admin = "123")
                $passwordValid = ($password === $user['password_hash']);
            }
            
            if ($passwordValid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } elseif ($user['role'] === 'teacher') {
                    header('Location: teacher/index.php');
                } elseif ($user['role'] === 'student') {
                    header('Location: student/index.php');
                }
                exit();
            } else {
                $error = 'Mật khẩu không đúng!';
            }
        } else {
            $error = 'Tên đăng nhập không tồn tại!';
        }
        
        $stmt->close();
        closeDBConnection($conn);
    }
}

$pageTitle = 'Đăng nhập';
require_once 'includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted small">Mặc định: admin / admin123</p>
                        <p class="text-muted">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

