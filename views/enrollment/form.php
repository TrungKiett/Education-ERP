<?php
$pageTitle = 'Đăng ký tuyển sinh';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card glass-card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2">
                            <i class="bi bi-file-earmark-text text-primary"></i> Đơn đăng ký tuyển sinh
                        </h2>
                        <p class="text-muted">Vui lòng điền đầy đủ thông tin bên dưới để nộp hồ sơ</p>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="enrollmentForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-semibold">
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label fw-semibold">
                                    Ngày sinh <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">
                                    Địa chỉ <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                          required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       pattern="[0-9]{10,11}" required>
                                <small class="text-muted">Nhập số điện thoại 10-11 chữ số</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">
                                    Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <small class="text-muted">Để nhận email xác nhận</small>
                            </div>
                            
                            <div class="col-12">
                                <label for="documents" class="form-label fw-semibold">
                                    Tài liệu đính kèm
                                </label>
                                <input type="file" class="form-control" id="documents" name="documents[]" 
                                       multiple accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    Chấp nhận: PDF, JPG, PNG (tối đa 5MB mỗi file). Có thể chọn nhiều file.
                                </small>
                                <div id="fileList" class="mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2 justify-content-end">
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Nộp hồ sơ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="bi bi-shield-check"></i> 
                    Thông tin của bạn được bảo mật và chỉ sử dụng cho mục đích tuyển sinh
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('documents').addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    if (e.target.files.length > 0) {
        const list = document.createElement('ul');
        list.className = 'list-unstyled';
        
        Array.from(e.target.files).forEach((file, index) => {
            const li = document.createElement('li');
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            li.innerHTML = `<i class="bi bi-file-earmark"></i> ${file.name} <small class="text-muted">(${sizeMB} MB)</small>`;
            list.appendChild(li);
        });
        
        fileList.appendChild(list);
    }
});

document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    const files = document.getElementById('documents').files;
    let hasError = false;
    
    Array.from(files).forEach(file => {
        if (file.size > 5 * 1024 * 1024) {
            alert(`File ${file.name} vượt quá 5MB!`);
            hasError = true;
        }
    });
    
    if (hasError) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

