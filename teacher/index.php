<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireTeacher();

$pageTitle = 'Trang Giáo viên';
require_once '../includes/header.php';

$conn = getDBConnection();
$teacherId = null;

// Get teacher ID from users.profile_id
$userId = getCurrentUserId();
$userStmt = $conn->prepare("SELECT profile_id FROM users WHERE id = ? AND role = 'teacher'");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header('Location: ../logout.php');
    exit();
}

$userData = $userResult->fetch_assoc();
$teacherId = $userData['profile_id'] ?? null;

if (!$teacherId) {
    header('Location: ../logout.php');
    exit();
}

$userStmt->close();

// Get teacher info
$teacherStmt = $conn->prepare("SELECT id, full_name FROM teachers WHERE id = ?");
$teacherStmt->bind_param("i", $teacherId);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();
$teacher = $teacherResult->fetch_assoc();
$teacherStmt->close();

// Get view parameter
$view = $_GET['view'] ?? 'today';
$currentDate = $_GET['date'] ?? date('Y-m-d');

// Calculate dates for different views
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
$weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));
$monthStart = date('Y-m-01', strtotime($currentDate));
$monthEnd = date('Y-m-t', strtotime($currentDate));

// Get schedules based on view
// Database dùng schedule_date (DATE) thay vì weekday
$schedules = [];
$whereClause = "WHERE ts.teacher_id = $teacherId";

$today = date('Y-m-d');
$currentWeekday = date('N'); // 1=Monday, 7=Sunday

if ($view === 'today') {
    // Show schedules for today
    $whereClause .= " AND ts.schedule_date = '$today'";
    $orderBy = "ORDER BY ts.period";
} elseif ($view === 'week') {
    // Show schedules for the week (Monday to Friday)
    $whereClause .= " AND ts.schedule_date >= '$weekStart' AND ts.schedule_date <= '$weekEnd'";
    $orderBy = "ORDER BY ts.schedule_date, ts.period";
} elseif ($view === 'month') {
    // Show schedules for the month
    $whereClause .= " AND ts.schedule_date >= '$monthStart' AND ts.schedule_date <= '$monthEnd'";
    $orderBy = "ORDER BY ts.schedule_date, ts.period";
}

$sql = "SELECT ts.*, 
               c.name as class_name, 
               s.name as subject_name,
               s.code as subject_code
        FROM schedules ts
        JOIN classrooms c ON ts.class_id = c.id
        JOIN subjects s ON ts.subject_id = s.id
        $whereClause
        $orderBy";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

closeDBConnection($conn);

$daysOfWeek = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-person-badge"></i> Lịch dạy của: <?php echo htmlspecialchars($teacher['full_name']); ?></h2>
    <hr>
    
    <!-- View Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'today' ? 'active' : ''; ?>" href="?view=today&date=<?php echo $today; ?>">
                <i class="bi bi-calendar-day"></i> Hôm nay
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'week' ? 'active' : ''; ?>" href="?view=week&date=<?php echo $currentDate; ?>">
                <i class="bi bi-calendar-week"></i> Tuần này
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'month' ? 'active' : ''; ?>" href="?view=month&date=<?php echo $currentDate; ?>">
                <i class="bi bi-calendar-month"></i> Tháng này
            </a>
        </li>
    </ul>
    
    <!-- Date Navigation -->
    <?php if ($view !== 'today'): ?>
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <form method="GET" class="d-inline-flex">
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <input type="date" class="form-control me-2" name="date" value="<?php echo $currentDate; ?>" onchange="this.form.submit()">
        </form>
        <div>
            <?php if ($view === 'week'): ?>
            <span class="text-muted"><?php echo date('d/m/Y', strtotime($weekStart)); ?> - <?php echo date('d/m/Y', strtotime($weekEnd)); ?></span>
            <?php elseif ($view === 'month'): ?>
            <span class="text-muted">Tháng <?php echo date('m/Y', strtotime($currentDate)); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Schedules Display -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($schedules)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Chưa có lịch dạy trong khoảng thời gian này.
            </div>
            <?php else: ?>
            
            <?php if ($view === 'today'): ?>
            <!-- Today View -->
            <h5 class="mb-3"><?php echo date('d/m/Y', strtotime($currentDate)); ?> - <?php echo $daysOfWeek[date('w', strtotime($currentDate))]; ?></h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tiết</th>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><strong>Tiết <?php echo $schedule['period']; ?></strong></td>
                            <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['subject_name']); ?> (<?php echo htmlspecialchars($schedule['subject_code']); ?>)</td>
                            <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($view === 'week'): ?>
            <!-- Week View -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Thứ</th>
                            <th>Ngày</th>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <th class="text-center">Tiết <?php echo $i; ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $weekSchedule = [];
                        foreach ($schedules as $schedule) {
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            if ($scheduleDate) {
                                $day = date('N', strtotime($scheduleDate)); // 1=Monday, 2=Tuesday, ..., 7=Sunday
                                $period = $schedule['period'];
                                if (!isset($weekSchedule[$day])) {
                                    $weekSchedule[$day] = [];
                                }
                                $weekSchedule[$day][$period] = $schedule;
                            }
                        }
                        
                        // Display Monday to Friday (1-5)
                        for ($day = 1; $day <= 5; $day++):
                            $date = date('Y-m-d', strtotime($weekStart . ' +' . ($day - 1) . ' days'));
                        ?>
                        <tr>
                            <td><strong><?php echo $daysOfWeek[$day]; ?></strong></td>
                            <td><?php echo date('d/m', strtotime($date)); ?></td>
                            <?php for ($period = 1; $period <= 10; $period++): ?>
                            <td class="text-center">
                                <?php if (isset($weekSchedule[$day][$period])): 
                                    $sched = $weekSchedule[$day][$period];
                                ?>
                                <div class="small">
                                    <strong><?php echo htmlspecialchars($sched['class_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($sched['subject_name']); ?><br>
                                    <?php if (!empty($sched['room'])): ?>
                                    <small class="text-muted"><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($sched['room']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($view === 'month'): ?>
            <!-- Month View -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Thứ</th>
                            <th>Tiết</th>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): 
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            $dayOfWeekNum = $scheduleDate ? date('w', strtotime($scheduleDate)) : 0; // 0=Sunday, 1=Monday, ...
                            $dayOfWeek = $daysOfWeek[$dayOfWeekNum] ?? 'N/A';
                        ?>
                        <tr>
                            <td><?php echo $dayOfWeek; ?></td>
                            <td><strong>Tiết <?php echo $schedule['period']; ?></strong></td>
                            <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['subject_name']); ?> (<?php echo htmlspecialchars($schedule['subject_code']); ?>)</td>
                            <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

