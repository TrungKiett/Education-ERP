<?php
$pageTitle = 'Quản lý Hồ sơ Tuyển sinh';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-file-earmark-text"></i> Quản lý</p>
            <h2>Hồ sơ Tuyển sinh</h2>
            <p class="mb-0 text-muted">Xem xét và phê duyệt các hồ sơ đăng ký tuyển sinh</p>
        </div>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'x-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card text-white bg-gradient-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Chờ xét duyệt</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['pending_count']; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card text-white bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Đã phê duyệt</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['approved_count']; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card text-white bg-gradient-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Đã từ chối</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['rejected_count']; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <ul class="nav nav-tabs custom-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" 
               href="?action=enrollment.adminEnrollments&status=pending">
                <i class="bi bi-clock-history"></i> Chờ xét duyệt
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>" 
               href="?action=enrollment.adminEnrollments&status=approved">
                <i class="bi bi-check-circle"></i> Đã phê duyệt
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>" 
               href="?action=enrollment.adminEnrollments&status=rejected">
                <i class="bi bi-x-circle"></i> Đã từ chối
            </a>
        </li>
    </ul>
    
    <!-- Enrollments Table -->
    <div class="card glass-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ và tên</th>
                            <th>Ngày sinh</th>
                            <th>Địa chỉ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>Tài liệu</th>
                            <th>Ngày nộp</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enrollments)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Không có hồ sơ nào</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td><strong>#<?php echo $enrollment['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($enrollment['full_name']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($enrollment['date_of_birth'])); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['address']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['phone']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['email'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($enrollment['documents']) && is_array($enrollment['documents'])): ?>
                                    <?php foreach ($enrollment['documents'] as $doc): ?>
                                        <a href="<?php echo htmlspecialchars($doc['url']); ?>" target="_blank" 
                                           class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="bi bi-file-earmark"></i> <?php echo htmlspecialchars($doc['name']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($enrollment['created_at'])); ?></td>
                            <td>
                                <?php if ($enrollment['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success" 
                                        onclick="approveEnrollment(<?php echo $enrollment['id']; ?>, '<?php echo htmlspecialchars($enrollment['full_name']); ?>')"
                                        title="Phê duyệt">
                                    <i class="bi bi-check-circle"></i> Phê duyệt
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="rejectEnrollment(<?php echo $enrollment['id']; ?>, '<?php echo htmlspecialchars($enrollment['full_name']); ?>')"
                                        title="Từ chối">
                                    <i class="bi bi-x-circle"></i> Từ chối
                                </button>
                                <?php else: ?>
                                <span class="badge bg-<?php echo $enrollment['status'] === 'approved' ? 'success' : 'danger'; ?>">
                                    <?php echo $enrollment['status'] === 'approved' ? 'Đã phê duyệt' : 'Đã từ chối'; ?>
                                </span>
                                <?php if (!empty($enrollment['notes'])): ?>
                                <br><small class="text-muted" title="<?php echo htmlspecialchars($enrollment['notes']); ?>">
                                    <i class="bi bi-info-circle"></i> Có ghi chú
                                </small>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Phê duyệt hồ sơ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" id="approve_id">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn phê duyệt hồ sơ của <strong id="approve_name"></strong>?</p>
                    <p class="text-success"><i class="bi bi-info-circle"></i> Hệ thống sẽ tự động tạo bản ghi học sinh mới sau khi phê duyệt.</p>
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Phê duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối hồ sơ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" id="reject_id">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn từ chối hồ sơ của <strong id="reject_name"></strong>?</p>
                    <div class="mb-3">
                        <label for="reject_notes" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_notes" name="notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveEnrollment(id, name) {
    document.getElementById('approve_id').value = id;
    document.getElementById('approve_name').textContent = name;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectEnrollment(id, name) {
    document.getElementById('reject_id').value = id;
    document.getElementById('reject_name').textContent = name;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

