<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireStudent();

$pageTitle = 'Thời khóa biểu Học sinh';
require_once '../includes/header.php';

$conn = getDBConnection();
$studentId = null;
$classId = null;

// Get student ID and class_id from users.profile_id
$userId = getCurrentUserId();
$userStmt = $conn->prepare("SELECT profile_id FROM users WHERE id = ? AND role = 'student'");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header('Location: ../logout.php');
    exit();
}

$userData = $userResult->fetch_assoc();
$studentId = $userData['profile_id'] ?? null;

if (!$studentId) {
    header('Location: ../logout.php');
    exit();
}

$userStmt->close();

// Get student info
$studentStmt = $conn->prepare("SELECT id, full_name, class_id FROM students WHERE id = ?");
$studentStmt->bind_param("i", $studentId);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
$student = $studentResult->fetch_assoc();
$classId = $student['class_id'];
$studentStmt->close();

if (!$classId) {
    echo '<div class="container-fluid mt-4">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Bạn chưa được phân vào lớp học nào. Vui lòng liên hệ quản trị viên.
            </div>
          </div>';
    require_once '../includes/footer.php';
    exit();
}

// Get class name
$classStmt = $conn->prepare("SELECT name FROM classrooms WHERE id = ?");
$classStmt->bind_param("i", $classId);
$classStmt->execute();
$classResult = $classStmt->get_result();
$classroom = $classResult->fetch_assoc();
$classStmt->close();

// Get schedules for the week (weekday 1-5: Monday-Friday)
$schedules = [];
$sql = "SELECT ts.*, 
               t.full_name as teacher_name,
               s.name as subject_name,
               s.code as subject_code
        FROM schedules ts
        JOIN teachers t ON ts.teacher_id = t.id
        JOIN subjects s ON ts.subject_id = s.id
        WHERE ts.class_id = ? 
        AND ts.weekday BETWEEN 1 AND 5
        ORDER BY ts.weekday, ts.period";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
$stmt->close();

closeDBConnection($conn);

$daysOfWeek = ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-calendar-week"></i> Thời khóa biểu của: <?php echo htmlspecialchars($student['full_name']); ?></h2>
    <p class="text-muted">Lớp: <?php echo htmlspecialchars($classroom['name']); ?></p>
    <hr>
    
    <!-- Timetable -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($schedules)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Chưa có lịch học trong tuần này.
            </div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 80px;">Tiết</th>
                            <?php
                            // Display Monday to Friday
                            for ($day = 1; $day <= 5; $day++):
                            ?>
                            <th class="text-center">
                                <div><?php echo $daysOfWeek[$day]; ?></div>
                            </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Organize schedules by day and period
                        $weekSchedule = [];
                        foreach ($schedules as $schedule) {
                            $day = $schedule['weekday']; // weekday: 1=Monday, 2=Tuesday, ...
                            $period = $schedule['period'];
                            if (!isset($weekSchedule[$day])) {
                                $weekSchedule[$day] = [];
                            }
                            $weekSchedule[$day][$period] = $schedule;
                        }
                        
                        // Display periods 1-10
                        for ($period = 1; $period <= 10; $period++):
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $period; ?></td>
                            <?php
                            // Display Monday to Friday (1-5)
                            for ($day = 1; $day <= 5; $day++):
                            ?>
                            <td class="align-middle">
                                <?php if (isset($weekSchedule[$day][$period])): 
                                    $sched = $weekSchedule[$day][$period];
                                ?>
                                <div class="p-2 bg-light rounded">
                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($sched['subject_name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($sched['subject_code']); ?></div>
                                    <div class="small mt-1">
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($sched['teacher_name']); ?>
                                    </div>
                                    <?php if (!empty($sched['room'])): ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-door-open"></i> <?php echo htmlspecialchars($sched['room']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted">-</div>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

