<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Quản lý Môn học';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $periodsPerWeek = $_POST['periods_per_week'] ?? 0;
            $description = $_POST['description'] ?? '';
            
            if (empty($name)) {
                $message = 'Vui lòng điền tên môn học!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO subjects (name, code, periods_per_week, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $name, $code, $periodsPerWeek, $description);
                
                if ($stmt->execute()) {
                    $message = 'Thêm môn học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi thêm môn học!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $periodsPerWeek = $_POST['periods_per_week'] ?? 0;
            $description = $_POST['description'] ?? '';
            
            if (empty($name)) {
                $message = 'Vui lòng điền tên môn học!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE subjects SET name = ?, code = ?, periods_per_week = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssisi", $name, $code, $periodsPerWeek, $description, $id);
                
                if ($stmt->execute()) {
                    $message = 'Cập nhật môn học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            // Check if subject is used in schedules
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE subject_id = ?");
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $message = 'Không thể xóa môn học vì đang được sử dụng trong lịch dạy!';
                $messageType = 'danger';
            } else {
                $deleteStmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
                $deleteStmt->bind_param("i", $id);
                
                if ($deleteStmt->execute()) {
                    $message = 'Xóa môn học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa!';
                    $messageType = 'danger';
                }
                $deleteStmt->close();
            }
            $checkStmt->close();
        }
    }
}

// Get all subjects
$subjects = [];
$result = $conn->query("SELECT * FROM subjects ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-book"></i> Quản lý Môn học</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="bi bi-plus-circle"></i> Thêm môn học
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên môn học</th>
                            <th>Mã môn học</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Chưa có môn học nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo $subject['id']; ?></td>
                            <td><?php echo htmlspecialchars($subject['name']); ?></td>
                            <td><?php echo htmlspecialchars($subject['code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($subject['description'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSubject(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['name']); ?>')" title="Xóa">
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

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm môn học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Tên môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã môn học</label>
                        <input type="text" class="form-control" name="code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số tiết/tuần</label>
                        <input type="number" class="form-control" name="periods_per_week" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
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

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa môn học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Tên môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã môn học</label>
                        <input type="text" class="form-control" name="code" id="edit_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số tiết/tuần</label>
                        <input type="number" class="form-control" name="periods_per_week" id="edit_periods_per_week" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
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
                    <p>Bạn có chắc chắn muốn xóa môn học <strong id="delete_name"></strong>?</p>
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
function editSubject(subject) {
    document.getElementById('edit_id').value = subject.id;
    document.getElementById('edit_name').value = subject.name;
    document.getElementById('edit_code').value = subject.code || '';
    document.getElementById('edit_periods_per_week').value = subject.periods_per_week || 0;
    document.getElementById('edit_description').value = subject.description || '';
    new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
}

function deleteSubject(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
}
</script>
<?php require_once '../includes/footer.php'; ?>

