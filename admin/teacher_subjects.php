<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Gán môn dạy cho Giáo viên';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

$teacherId = $_GET['teacher_id'] ?? null;

if (!$teacherId) {
    header('Location: teachers.php');
    exit();
}

// Get teacher info
$teacherStmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$teacherStmt->bind_param("i", $teacherId);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();

if ($teacherResult->num_rows === 0) {
    header('Location: teachers.php');
    exit();
}

$teacher = $teacherResult->fetch_assoc();
$teacherStmt->close();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'assign') {
            $subjectId = $_POST['subject_id'] ?? 0;
            
            if ($subjectId > 0) {
                // Check if already assigned
                $checkStmt = $conn->prepare("SELECT id FROM teaching_assignments WHERE teacher_id = ? AND subject_id = ?");
                $checkStmt->bind_param("ii", $teacherId, $subjectId);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows > 0) {
                    $message = 'Môn học này đã được gán cho giáo viên!';
                    $messageType = 'warning';
                } else {
                    $assignStmt = $conn->prepare("INSERT INTO teaching_assignments (teacher_id, subject_id) VALUES (?, ?)");
                    $assignStmt->bind_param("ii", $teacherId, $subjectId);
                    
                    if ($assignStmt->execute()) {
                        $message = 'Gán môn học thành công!';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi gán môn học!';
                        $messageType = 'danger';
                    }
                    $assignStmt->close();
                }
                $checkStmt->close();
            }
        } elseif ($action === 'remove') {
            $subjectId = $_POST['subject_id'] ?? 0;
            
            if ($subjectId > 0) {
                $removeStmt = $conn->prepare("DELETE FROM teaching_assignments WHERE teacher_id = ? AND subject_id = ?");
                $removeStmt->bind_param("ii", $teacherId, $subjectId);
                
                if ($removeStmt->execute()) {
                    $message = 'Gỡ môn học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi gỡ môn học!';
                    $messageType = 'danger';
                }
                $removeStmt->close();
            }
        }
    }
}

// Get all subjects
$allSubjects = [];
$subjectsResult = $conn->query("SELECT * FROM subjects ORDER BY subject_name");
while ($row = $subjectsResult->fetch_assoc()) {
    $allSubjects[] = $row;
}

// Get assigned subjects
$assignedSubjectIds = [];
$assignedResult = $conn->prepare("SELECT subject_id FROM teaching_assignments WHERE teacher_id = ?");
$assignedResult->bind_param("i", $teacherId);
$assignedResult->execute();
$assignedData = $assignedResult->get_result();
while ($row = $assignedData->fetch_assoc()) {
    $assignedSubjectIds[] = $row['subject_id'];
}
$assignedResult->close();

// Get assigned subjects with details
$assignedSubjects = [];
if (!empty($assignedSubjectIds)) {
    $ids = implode(',', array_map('intval', $assignedSubjectIds));
    $assignedDetailsResult = $conn->query("SELECT s.* FROM subjects s WHERE s.id IN ($ids) ORDER BY s.subject_name");
    while ($row = $assignedDetailsResult->fetch_assoc()) {
        $assignedSubjects[] = $row;
    }
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-book"></i> Gán môn dạy cho: <?php echo htmlspecialchars($teacher['full_name']); ?></h2>
        <a href="teachers.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-list-check"></i> Môn học đã gán</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assignedSubjects)): ?>
                    <p class="text-muted">Chưa có môn học nào được gán</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tên môn học</th>
                                    <th>Mã môn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedSubjects as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['subject_code'] ?? '-'); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn gỡ môn học này?');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Gỡ môn học">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="bi bi-plus-circle"></i> Gán môn học mới</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="assign">
                        <div class="mb-3">
                            <label class="form-label">Chọn môn học</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">-- Chọn môn học --</option>
                                <?php foreach ($allSubjects as $subject): ?>
                                    <?php if (!in_array($subject['id'], $assignedSubjectIds)): ?>
                                    <option value="<?php echo $subject['id']; ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?> 
                                        (<?php echo htmlspecialchars($subject['subject_code'] ?? '-'); ?>)
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> Gán môn học
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

