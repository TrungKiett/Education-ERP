<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Trang quản trị';
require_once '../includes/header.php';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total teachers
$result = $conn->query("SELECT COUNT(*) as count FROM teachers");
$stats['teachers'] = $result->fetch_assoc()['count'];

// Total students
$result = $conn->query("SELECT COUNT(*) as count FROM students");
$stats['students'] = $result->fetch_assoc()['count'];

// Total classes
$result = $conn->query("SELECT COUNT(*) as count FROM classrooms");
$stats['classes'] = $result->fetch_assoc()['count'];

// Total subjects
$result = $conn->query("SELECT COUNT(*) as count FROM subjects");
$stats['subjects'] = $result->fetch_assoc()['count'];

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-speedometer2"></i> Bảng điều khiển quản trị</h2>
    <hr>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people"></i> Giáo viên</h5>
                    <h3><?php echo $stats['teachers']; ?></h3>
                    <a href="teachers.php" class="text-white">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-badge"></i> Học sinh</h5>
                    <h3><?php echo $stats['students']; ?></h3>
                    <a href="students.php" class="text-white">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-building"></i> Lớp học</h5>
                    <h3><?php echo $stats['classes']; ?></h3>
                    <a href="classrooms.php" class="text-white">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-book"></i> Môn học</h5>
                    <h3><?php echo $stats['subjects']; ?></h3>
                    <a href="subjects.php" class="text-white">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-list-check"></i> Quản lý</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="teachers.php" class="btn btn-outline-primary w-100 p-3">
                                <i class="bi bi-people fs-4"></i><br>
                                Quản lý Giáo viên
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="students.php" class="btn btn-outline-success w-100 p-3">
                                <i class="bi bi-person-badge fs-4"></i><br>
                                Quản lý Học sinh
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="classrooms.php" class="btn btn-outline-info w-100 p-3">
                                <i class="bi bi-building fs-4"></i><br>
                                Quản lý Lớp học
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="subjects.php" class="btn btn-outline-warning w-100 p-3">
                                <i class="bi bi-book fs-4"></i><br>
                                Quản lý Môn học
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="schedule.php" class="btn btn-outline-danger w-100 p-3">
                                <i class="bi bi-calendar-check fs-4"></i><br>
                                Phân công Lịch dạy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

