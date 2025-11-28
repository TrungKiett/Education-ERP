<?php
$pageTitle = 'Phân lớp Học sinh';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-people"></i> Quản lý</p>
            <h2>Phân lớp Học sinh</h2>
            <p class="mb-0 text-muted">Gán học sinh vào lớp học và khóa học</p>
        </div>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'x-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Assignment Form -->
    <div class="card glass-card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Phân lớp mới</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="assignForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="student_id" class="form-label fw-semibold">
                            Học sinh <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">-- Chọn học sinh --</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id'] ?? ''; ?>" 
                                    data-name="<?php echo htmlspecialchars($student['full_name']); ?>">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                                <?php if (isset($student['from_application'])): ?>
                                    <span class="text-muted">(Từ hồ sơ tuyển sinh)</span>
                                <?php endif; ?>
                                - <?php echo htmlspecialchars($student['phone']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="class_id" class="form-label fw-semibold">
                            Lớp học <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="">-- Chọn lớp học --</option>
                            <?php foreach ($classrooms as $classroom): ?>
                            <option value="<?php echo $classroom['id']; ?>">
                                <?php echo htmlspecialchars($classroom['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="course_id" class="form-label fw-semibold">
                            Khóa học <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">-- Chọn khóa học --</option>
                            <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" 
                                    data-price="<?php echo $course['price']; ?>">
                                <?php echo htmlspecialchars($course['name']); ?>
                                - <?php echo number_format($course['price'], 0, ',', '.'); ?> đ
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label for="notes" class="form-label fw-semibold">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Phân lớp
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Existing Enrollments -->
    <?php if (!empty($existingEnrollments)): ?>
    <div class="card glass-card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Danh sách đã phân lớp</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Học sinh</th>
                            <th>Lớp học</th>
                            <th>Khóa học</th>
                            <th>Ngày ghi danh</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existingEnrollments as $studentId => $enrollments): ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['student_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['class_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($enrollment['course_name']); ?>
                                    <br><small class="text-muted">
                                        <?php echo number_format($enrollment['course_price'], 0, ',', '.'); ?> đ
                                    </small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $enrollment['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php 
                                        echo $enrollment['status'] === 'active' ? 'Đang học' : 
                                            ($enrollment['status'] === 'completed' ? 'Hoàn thành' : 'Đã hủy');
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

