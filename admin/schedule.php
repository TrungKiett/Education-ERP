<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Phân công Lịch dạy';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $teacherId = $_POST['teacher_id'] ?? 0;
            $classId = $_POST['class_id'] ?? 0;
            $subjectId = $_POST['subject_id'] ?? 0;
            $scheduleDate = $_POST['schedule_date'] ?? ''; // Date format: Y-m-d
            $period = $_POST['period'] ?? 0;
            $room = $_POST['room'] ?? '';
            
            // Validate input
            if (empty($teacherId) || empty($classId) || empty($subjectId) || empty($scheduleDate) || empty($period)) {
                $message = 'Vui lòng điền đầy đủ thông tin!';
                $messageType = 'danger';
            } else {
                // Validate date format
                $dateObj = DateTime::createFromFormat('Y-m-d', $scheduleDate);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $scheduleDate) {
                    $message = 'Ngày dạy không hợp lệ!';
                    $messageType = 'danger';
                } else {
                    // Check conflicts
                    $conflicts = [];
                    
                    // 1. Check teacher schedule conflict (date + period)
                    $teacherCheckStmt = $conn->prepare("SELECT id FROM schedules WHERE teacher_id = ? AND schedule_date = ? AND period = ?");
                    $teacherCheckStmt->bind_param("isi", $teacherId, $scheduleDate, $period);
                    $teacherCheckStmt->execute();
                    if ($teacherCheckStmt->get_result()->num_rows > 0) {
                        $conflicts[] = 'Giáo viên đã có lịch dạy trong thời gian này';
                    }
                    $teacherCheckStmt->close();
                    
                    // 2. Check class schedule conflict (date + period)
                    $classCheckStmt = $conn->prepare("SELECT id FROM schedules WHERE class_id = ? AND schedule_date = ? AND period = ?");
                    $classCheckStmt->bind_param("isi", $classId, $scheduleDate, $period);
                    $classCheckStmt->execute();
                    if ($classCheckStmt->get_result()->num_rows > 0) {
                        $conflicts[] = 'Lớp học đã có lịch học trong thời gian này';
                    }
                    $classCheckStmt->close();
                    
                    // 3. Check room conflict (if room is provided)
                    if (!empty($room)) {
                        $roomCheckStmt = $conn->prepare("SELECT id FROM schedules WHERE room = ? AND schedule_date = ? AND period = ?");
                        $roomCheckStmt->bind_param("ssi", $room, $scheduleDate, $period);
                        $roomCheckStmt->execute();
                        if ($roomCheckStmt->get_result()->num_rows > 0) {
                            $conflicts[] = 'Phòng học đã được sử dụng trong thời gian này';
                        }
                        $roomCheckStmt->close();
                    }
                    
                    // 4. Check if teacher can teach this subject (from teaching_assignments with class_id)
                    $subjectCheckStmt = $conn->prepare("SELECT id FROM teaching_assignments WHERE teacher_id = ? AND subject_id = ? AND class_id = ?");
                    $subjectCheckStmt->bind_param("iii", $teacherId, $subjectId, $classId);
                    $subjectCheckStmt->execute();
                    if ($subjectCheckStmt->get_result()->num_rows === 0) {
                        $conflicts[] = 'Giáo viên chưa được gán môn học này cho lớp này';
                    }
                    $subjectCheckStmt->close();
                    
                    if (!empty($conflicts)) {
                        $message = 'Lỗi: ' . implode(', ', $conflicts);
                        $messageType = 'danger';
                    } else {
                        // Insert schedule with schedule_date
                        $insertStmt = $conn->prepare("INSERT INTO schedules (teacher_id, class_id, subject_id, schedule_date, period, room) VALUES (?, ?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("iiisis", $teacherId, $classId, $subjectId, $scheduleDate, $period, $room);
                        
                        if ($insertStmt->execute()) {
                            $message = 'Phân công lịch dạy thành công!';
                            $messageType = 'success';
                            // Redirect to show the newly created schedule
                            header('Location: schedule.php?date=' . urlencode($scheduleDate));
                            exit();
                        } else {
                            $message = 'Lỗi khi phân công lịch dạy: ' . $conn->error;
                            $messageType = 'danger';
                        }
                        $insertStmt->close();
                    }
                }
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if ($id > 0) {
                $deleteStmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
                $deleteStmt->bind_param("i", $id);
                
                if ($deleteStmt->execute()) {
                    $message = 'Xóa lịch dạy thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa!';
                    $messageType = 'danger';
                }
                $deleteStmt->close();
            }
        }
    }
}

// Get filter parameters
$filterDate = $_GET['date'] ?? ''; // Don't filter by default - show all schedules
$filterClassId = $_GET['class_id'] ?? '';
$filterTeacherId = $_GET['teacher_id'] ?? '';

// Get all classrooms for filter
$classrooms = [];
$classResult = $conn->query("SELECT id, name FROM classrooms ORDER BY name");
while ($row = $classResult->fetch_assoc()) {
    $classrooms[] = $row;
}

// Get all teachers for filter
$teachers = [];
$teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name");
while ($row = $teacherResult->fetch_assoc()) {
    $teachers[] = $row;
}

// Get all subjects
$subjects = [];
$subjectResult = $conn->query("SELECT id, name FROM subjects ORDER BY name");
while ($row = $subjectResult->fetch_assoc()) {
    $subjects[] = $row;
}

// Get schedules
$schedules = [];
$sql = "SELECT ts.*, 
               t.full_name as teacher_name, 
               c.name as class_name, 
               s.name as subject_name 
        FROM schedules ts
        JOIN teachers t ON ts.teacher_id = t.id
        JOIN classrooms c ON ts.class_id = c.id
        JOIN subjects s ON ts.subject_id = s.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filterDate)) {
    $sql .= " AND ts.schedule_date = ?";
    $params[] = $filterDate;
    $types .= 's';
}

if (!empty($filterClassId)) {
    $sql .= " AND ts.class_id = ?";
    $params[] = $filterClassId;
    $types .= 'i';
}

if (!empty($filterTeacherId)) {
    $sql .= " AND ts.teacher_id = ?";
    $params[] = $filterTeacherId;
    $types .= 'i';
}

// Order by schedule_date if it exists, otherwise by id
$sql .= " ORDER BY COALESCE(ts.schedule_date, ts.id) DESC, ts.period";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

if (!empty($params)) {
    $stmt->close();
}

// Get teachers with their assigned subjects for the form
$teacherSubjects = [];
$tsResult = $conn->query("
    SELECT ts.teacher_id, ts.subject_id, ts.class_id, t.full_name as teacher_name, s.name as subject_name, c.name as class_name
    FROM teaching_assignments ts
    JOIN teachers t ON ts.teacher_id = t.id
    JOIN subjects s ON ts.subject_id = s.id
    JOIN classrooms c ON ts.class_id = c.id
    ORDER BY t.full_name, s.name
");
while ($row = $tsResult->fetch_assoc()) {
    if (!isset($teacherSubjects[$row['teacher_id']])) {
        $teacherSubjects[$row['teacher_id']] = [
            'name' => $row['teacher_name'],
            'subjects' => []
        ];
    }
    $teacherSubjects[$row['teacher_id']]['subjects'][] = [
        'id' => $row['subject_id'],
        'name' => $row['subject_name'],
        'class_id' => $row['class_id'],
        'class_name' => $row['class_name']
    ];
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-calendar-check"></i> Phân công Lịch dạy</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="bi bi-plus-circle"></i> Thêm lịch dạy
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Ngày dạy</label>
                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($filterDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lớp học</label>
                    <select class="form-select" name="class_id">
                        <option value="">Tất cả lớp học</option>
                        <?php foreach ($classrooms as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($filterClassId == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giáo viên</label>
                    <select class="form-select" name="teacher_id">
                        <option value="">Tất cả giáo viên</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>" <?php echo ($filterTeacherId == $teacher['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-secondary w-100">Lọc</button>
                        <a href="schedule.php" class="btn btn-outline-secondary w-100 mt-2">Bỏ lọc</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Schedules Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ngày dạy</th>
                            <th>Thứ</th>
                            <th>Tiết</th>
                            <th>Lớp học</th>
                            <th>Môn học</th>
                            <th>Giáo viên</th>
                            <th>Phòng học</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Chưa có lịch dạy nào</td>
                        </tr>
                        <?php else: ?>
                        <?php 
                        $daysOfWeek = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                        foreach ($schedules as $schedule): 
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            $dayOfWeekNum = date('w', strtotime($scheduleDate)); // 0=Sunday, 1=Monday, ...
                            $dayOfWeek = $daysOfWeek[$dayOfWeekNum] ?? 'N/A';
                        ?>
                        <tr>
                            <td><?php echo $schedule['id']; ?></td>
                            <td><?php echo $scheduleDate ? date('d/m/Y', strtotime($scheduleDate)) : '-'; ?></td>
                            <td><?php echo $dayOfWeek; ?></td>
                            <td><?php echo $schedule['period']; ?></td>
                            <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['teacher_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>, '<?php echo date('d/m/Y', strtotime($scheduleDate)); ?> - Tiết <?php echo $schedule['period']; ?>')" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm lịch dạy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp học <span class="text-danger">*</span></label>
                            <select class="form-select" name="class_id" id="form_class_id" required onchange="updateSubjectOptions()">
                                <option value="">Chọn lớp...</option>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giáo viên <span class="text-danger">*</span></label>
                            <select class="form-select" name="teacher_id" id="form_teacher_id" required onchange="updateSubjectOptions()">
                                <option value="">Chọn giáo viên...</option>
                                <?php foreach ($teacherSubjects as $tid => $tdata): ?>
                                <option value="<?php echo $tid; ?>"><?php echo htmlspecialchars($tdata['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" id="form_subject_id" required>
                                <option value="">Chọn môn học...</option>
                            </select>
                            <small class="text-muted">Chỉ hiển thị môn học đã được gán cho giáo viên với lớp đã chọn</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày dạy <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="schedule_date" required>
                            <small class="text-muted">Chọn ngày dạy cụ thể</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiết học <span class="text-danger">*</span></label>
                            <select class="form-select" name="period" required>
                                <option value="">Chọn tiết...</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>">Tiết <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phòng học</label>
                            <input type="text" class="form-control" name="room" placeholder="VD: P101">
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Hệ thống sẽ kiểm tra trùng lịch giáo viên (theo ngày và tiết), trùng lịch lớp và trùng phòng học trước khi lưu.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Bạn có chắc chắn muốn xóa lịch dạy <strong id="delete_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const teacherSubjectsData = <?php echo json_encode($teacherSubjects); ?>;

function updateSubjectOptions() {
    const teacherId = document.getElementById('form_teacher_id').value;
    const classId = document.getElementById('form_class_id').value;
    const subjectSelect = document.getElementById('form_subject_id');
    
    // Clear options
    subjectSelect.innerHTML = '<option value="">Chọn môn học...</option>';
    
    if (teacherId && teacherSubjectsData[teacherId] && classId) {
        const subjects = teacherSubjectsData[teacherId].subjects;
        // Filter subjects by class_id
        subjects.forEach(subject => {
            if (subject.class_id == classId) {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name + ' (' + subject.class_name + ')';
                subjectSelect.appendChild(option);
            }
        });
    }
}

function deleteSchedule(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteScheduleModal')).show();
}
</script>
<?php require_once '../includes/footer.php'; ?>

