<?php
$pageTitle = 'Trang quản trị';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-speedometer2"></i> Bảng Điều khiển</h2>
    <hr>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Giáo viên</h5>
                            <h2><?php echo $stats['teachers'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-person-badge" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <a href="?action=admin.teachers" class="text-white text-decoration-none">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Học sinh</h5>
                            <h2><?php echo $stats['students'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <a href="?action=admin.students" class="text-white text-decoration-none">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Lớp học</h5>
                            <h2><?php echo $stats['classrooms'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-building" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <a href="?action=admin.classrooms" class="text-white text-decoration-none">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Môn học</h5>
                            <h2><?php echo $stats['subjects'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-book" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <a href="?action=admin.subjects" class="text-white text-decoration-none">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-list-ul"></i> Quản lý nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.teachers" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-badge"></i> Quản lý Giáo viên
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.students" class="btn btn-outline-success w-100">
                                <i class="bi bi-people"></i> Quản lý Học sinh
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.classrooms" class="btn btn-outline-info w-100">
                                <i class="bi bi-building"></i> Quản lý Lớp học
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.subjects" class="btn btn-outline-warning w-100">
                                <i class="bi bi-book"></i> Quản lý Môn học
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.schedules" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-calendar-check"></i> Phân công Lịch dạy
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?action=admin.teacherSubjects" class="btn btn-outline-dark w-100">
                                <i class="bi bi-person-workspace"></i> Gán Môn dạy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

