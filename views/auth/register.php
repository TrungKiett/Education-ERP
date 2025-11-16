<?php
$pageTitle = 'Đăng ký';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success) && $success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <div class="mt-2">
                            <a href="?action=login" class="btn btn-sm btn-primary">Đi đến trang đăng nhập</a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="?action=register">
                        <?php
                        $formFullName = isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '';
                        $formUsername = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
                        $formCode = isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '';
                        $formEmail = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
                        $formPhone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
                        $formClassId = isset($_POST['class_id']) ? $_POST['class_id'] : '';
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
                                <?php if (isset($classrooms)): ?>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                        <?php echo ($formClassId == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                    <?php if (!empty($class['grade'])): ?>
                                        - Khối <?php echo htmlspecialchars($class['grade']); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Bạn có thể được phân vào lớp sau khi đăng ký</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted">Đã có tài khoản? <a href="?action=login">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

