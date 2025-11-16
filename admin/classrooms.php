<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Quản lý Lớp học';
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
            $grade = $_POST['grade'] ?? '';
            $capacity = $_POST['capacity'] ?? 0;
            $note = $_POST['note'] ?? '';
            
            if (empty($name)) {
                $message = 'Vui lòng điền tên lớp!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO classrooms (name, grade, capacity, note) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $name, $grade, $capacity, $note);
                
                if ($stmt->execute()) {
                    $message = 'Thêm lớp học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi thêm lớp học!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $grade = $_POST['grade'] ?? '';
            $capacity = $_POST['capacity'] ?? 0;
            $note = $_POST['note'] ?? '';
            
            if (empty($name)) {
                $message = 'Vui lòng điền tên lớp!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE classrooms SET name = ?, grade = ?, capacity = ?, note = ? WHERE id = ?");
                $stmt->bind_param("ssisi", $name, $grade, $capacity, $note, $id);
                
                if ($stmt->execute()) {
                    $message = 'Cập nhật lớp học thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            // Check if class has students
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE class_id = ?");
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $message = 'Không thể xóa lớp học vì còn học sinh trong lớp!';
                $messageType = 'danger';
            } else {
                $deleteStmt = $conn->prepare("DELETE FROM classrooms WHERE id = ?");
                $deleteStmt->bind_param("i", $id);
                
                if ($deleteStmt->execute()) {
                    $message = 'Xóa lớp học thành công!';
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

// Get all classrooms
$classrooms = [];
$result = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count
    FROM classrooms c 
    ORDER BY c.name
");
while ($row = $result->fetch_assoc()) {
    $classrooms[] = $row;
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-building"></i> Quản lý Lớp học</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassroomModal">
            <i class="bi bi-plus-circle"></i> Thêm lớp học
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
                            <th>Tên lớp</th>
                            <th>Khối</th>
                            <th>Sức chứa</th>
                            <th>Số học sinh</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classrooms)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Chưa có lớp học nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($classrooms as $classroom): ?>
                        <tr>
                            <td><?php echo $classroom['id']; ?></td>
                            <td><?php echo htmlspecialchars($classroom['name']); ?></td>
                            <td><?php echo htmlspecialchars($classroom['grade'] ?? '-'); ?></td>
                            <td><?php echo $classroom['capacity'] ?? 0; ?></td>
                            <td><?php echo $classroom['student_count']; ?></td>
                            <td><?php echo htmlspecialchars($classroom['note'] ?? '-'); ?></td>
                            <td>
                                <a href="students.php?class_id=<?php echo $classroom['id']; ?>" class="btn btn-sm btn-info" title="Xem học sinh">
                                    <i class="bi bi-people"></i>
                                </a>
                                <button class="btn btn-sm btn-warning" onclick="editClassroom(<?php echo htmlspecialchars(json_encode($classroom)); ?>)" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteClassroom(<?php echo $classroom['id']; ?>, '<?php echo htmlspecialchars($classroom['name']); ?>')" title="Xóa">
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

<!-- Add Classroom Modal -->
<div class="modal fade" id="addClassroomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm lớp học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Tên lớp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Khối</label>
                        <input type="text" class="form-control" name="grade" placeholder="VD: 10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sức chứa</label>
                        <input type="number" class="form-control" name="capacity" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="2"></textarea>
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

<!-- Edit Classroom Modal -->
<div class="modal fade" id="editClassroomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa lớp học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Tên lớp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Khối</label>
                        <input type="text" class="form-control" name="grade" id="edit_grade">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sức chứa</label>
                        <input type="number" class="form-control" name="capacity" id="edit_capacity" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" id="edit_note" rows="2"></textarea>
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
<div class="modal fade" id="deleteClassroomModal" tabindex="-1">
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
                    <p>Bạn có chắc chắn muốn xóa lớp học <strong id="delete_name"></strong>?</p>
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
function editClassroom(classroom) {
    document.getElementById('edit_id').value = classroom.id;
    document.getElementById('edit_name').value = classroom.name;
    document.getElementById('edit_grade').value = classroom.grade || '';
    document.getElementById('edit_capacity').value = classroom.capacity || 0;
    document.getElementById('edit_note').value = classroom.note || '';
    new bootstrap.Modal(document.getElementById('editClassroomModal')).show();
}

function deleteClassroom(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteClassroomModal')).show();
}
</script>
<?php require_once '../includes/footer.php'; ?>

