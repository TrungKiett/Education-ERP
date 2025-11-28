<?php
$pageTitle = 'Trang quản trị';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-lightning-charge"></i> Tổng quan</p>
            <h2>Bảng điều khiển</h2>
            <p class="mb-0 text-muted">Theo dõi nhanh số liệu trong hệ thống giáo dục</p>
        </div>
        <a href="?action=admin.schedules" class="btn btn-lg btn-primary shadow-sm">
            <i class="bi bi-calendar-check"></i> Quản lý lịch dạy
        </a>
    </div>
    
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card text-white bg-gradient-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Giáo viên</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['teachers'] ?? 0; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                    </div>
                    <a href="?action=admin.teachers" class="stretched-link text-white text-decoration-none fw-semibold">
                        Xem chi tiết <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card text-white bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Học sinh</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['students'] ?? 0; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                    </div>
                    <a href="?action=admin.students" class="stretched-link text-white text-decoration-none fw-semibold">
                        Xem chi tiết <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card text-white bg-gradient-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Lớp học</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['classrooms'] ?? 0; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-building fs-3"></i>
                        </div>
                    </div>
                    <a href="?action=admin.classrooms" class="stretched-link text-white text-decoration-none fw-semibold">
                        Xem chi tiết <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card text-white bg-gradient-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Môn học</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['subjects'] ?? 0; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-book fs-3"></i>
                        </div>
                    </div>
                    <a href="?action=admin.subjects" class="stretched-link text-white text-decoration-none fw-semibold">
                        Xem chi tiết <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card glass-card quick-actions">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Tác vụ nhanh</h5>
                        <span class="text-muted small">Truy cập nhanh những danh mục thường dùng</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.teachers" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-badge"></i> Quản lý Giáo viên
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.students" class="btn btn-outline-success w-100">
                                <i class="bi bi-people"></i> Quản lý Học sinh
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.classrooms" class="btn btn-outline-info w-100">
                                <i class="bi bi-building"></i> Quản lý Lớp học
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.subjects" class="btn btn-outline-warning w-100">
                                <i class="bi bi-book"></i> Quản lý Môn học
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.schedules" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-calendar-check"></i> Phân công Lịch dạy
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=admin.teacherSubjects" class="btn btn-outline-dark w-100">
                                <i class="bi bi-person-workspace"></i> Gán Môn dạy
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="?action=enrollment.adminEnrollments" class="btn btn-outline-primary w-100 position-relative">
                                <i class="bi bi-file-earmark-text"></i> Hồ sơ Tuyển sinh
                                <?php if (isset($stats['pending_enrollments']) && $stats['pending_enrollments'] > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $stats['pending_enrollments']; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

