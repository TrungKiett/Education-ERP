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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $code = $_POST['code'] ?? '';
    $classId = $_POST['class_id'] ?? null;
    
    // Validation
    if (empty($fullName) || empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        $conn = getDBConnection();
        
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'Tên đăng nhập đã tồn tại!';
        } else {
            // Create student record first
            $studentStmt = $conn->prepare("INSERT INTO students (code, full_name, class_id, email, phone, username) VALUES (?, ?, ?, ?, ?, ?)");
            $studentStmt->bind_param("ssisss", $code, $fullName, $classId, $email, $phone, $username);
            
            if ($studentStmt->execute()) {
                $studentId = $conn->insert_id;
                
                // Create user account with profile_id = student.id
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userStmt = $conn->prepare("INSERT INTO users (username, password_hash, role, profile_id) VALUES (?, ?, 'student', ?)");
                $userStmt->bind_param("ssi", $username, $hashedPassword, $studentId);
                
                if ($userStmt->execute()) {
                    $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                    // Clear form data for security
                    unset($fullName, $username, $email, $phone, $code, $classId);
                } else {
                    // Rollback: delete student if user creation fails
                    $conn->query("DELETE FROM students WHERE id = $studentId");
                    $error = 'Lỗi khi tạo tài khoản! Vui lòng thử lại.';
                }
                $userStmt->close();
            } else {
                $error = 'Lỗi khi tạo hồ sơ học sinh! Vui lòng thử lại.';
            }
            $studentStmt->close();
        }
        
        $checkStmt->close();
        closeDBConnection($conn);
    }
}

// Get all classrooms for selection (optional)
$conn = getDBConnection();
$classrooms = [];
$classResult = $conn->query("SELECT id, name, grade FROM classrooms ORDER BY name");
while ($row = $classResult->fetch_assoc()) {
    $classrooms[] = $row;
}
closeDBConnection($conn);

$pageTitle = 'Đăng ký';
require_once 'includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <div class="mt-2">
                            <a href="login.php" class="btn btn-sm btn-primary">Đi đến trang đăng nhập</a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php
                        // Preserve form values if there was an error
                        $formFullName = isset($fullName) ? htmlspecialchars($fullName) : '';
                        $formUsername = isset($username) ? htmlspecialchars($username) : '';
                        $formCode = isset($code) ? htmlspecialchars($code) : '';
                        $formEmail = isset($email) ? htmlspecialchars($email) : '';
                        $formPhone = isset($phone) ? htmlspecialchars($phone) : '';
                        $formClassId = isset($classId) ? $classId : '';
                        ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo $formFullName; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo $formUsername; ?>" required>
                                <small class="text-muted">Tên đăng nhập phải là duy nhất</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Mã học sinh</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="<?php echo $formCode; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $formEmail; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?php echo $formPhone; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Lớp học (tùy chọn)</label>
                            <select class="form-select" id="class_id" name="class_id">
                                <option value="">Chọn lớp học (có thể để trống)</option>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                        <?php echo ($formClassId == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                    <?php if (!empty($class['grade'])): ?>
                                        - Khối <?php echo htmlspecialchars($class['grade']); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Bạn có thể được phân vào lớp sau khi đăng ký</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validate password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword && confirmPassword.length > 0) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp!');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

