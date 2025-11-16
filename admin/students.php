<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pageTitle = 'Quản lý Học sinh';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

$filterClassId = $_GET['class_id'] ?? null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $fullName = $_POST['full_name'] ?? '';
            $code = $_POST['code'] ?? '';
            $classId = $_POST['class_id'] ?? null;
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($fullName) || empty($username) || empty($password)) {
                $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                $messageType = 'danger';
            } else {
                // Check if username exists
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $checkStmt->bind_param("s", $username);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows > 0) {
                    $message = 'Tên đăng nhập đã tồn tại!';
                    $messageType = 'danger';
                } else {
                    // Create student record first (không có user_id trong students)
                    $studentStmt = $conn->prepare("INSERT INTO students (code, full_name, class_id, email, phone, username) VALUES (?, ?, ?, ?, ?, ?)");
                    $studentStmt->bind_param("ssisss", $code, $fullName, $classId, $email, $phone, $username);
                    
                    if ($studentStmt->execute()) {
                        $studentId = $conn->insert_id;
                        
                        // Create user account with profile_id = student.id
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $userStmt = $conn->prepare("INSERT INTO users (username, password_hash, role, profile_id) VALUES (?, ?, 'student', ?)");
                        $userStmt->bind_param("ssi", $username, $hashedPassword, $studentId);
                        
                        if ($userStmt->execute()) {
                            $message = 'Thêm học sinh thành công!';
                            $messageType = 'success';
                        } else {
                            // Rollback: delete student if user creation fails
                            $conn->query("DELETE FROM students WHERE id = $studentId");
                            $message = 'Lỗi khi tạo tài khoản!';
                            $messageType = 'danger';
                        }
                        $userStmt->close();
                    } else {
                        $message = 'Lỗi khi tạo học sinh!';
                        $messageType = 'danger';
                    }
                    $studentStmt->close();
                }
                $checkStmt->close();
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? 0;
            $fullName = $_POST['full_name'] ?? '';
            $code = $_POST['code'] ?? '';
            $classId = $_POST['class_id'] ?? null;
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            if (empty($fullName)) {
                $message = 'Vui lòng điền đầy đủ thông tin!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE students SET code = ?, full_name = ?, class_id = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("ssissi", $code, $fullName, $classId, $email, $phone, $id);
                
                if ($stmt->execute()) {
                    $message = 'Cập nhật học sinh thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            // Delete user first (where profile_id = student.id)
            $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE profile_id = ? AND role = 'student'");
            $deleteUserStmt->bind_param("i", $id);
            $deleteUserStmt->execute();
            $deleteUserStmt->close();
            
            // Delete student
            $deleteStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                $message = 'Xóa học sinh thành công!';
                $messageType = 'success';
            } else {
                $message = 'Lỗi khi xóa!';
                $messageType = 'danger';
            }
            $deleteStmt->close();
        }
    }
}

// Get all classrooms for filter
$classrooms = [];
$classResult = $conn->query("SELECT id, name FROM classrooms ORDER BY name");
while ($row = $classResult->fetch_assoc()) {
    $classrooms[] = $row;
}

// Get all students
$students = [];
$sql = "SELECT s.*, c.name as class_name 
        FROM students s 
        LEFT JOIN classrooms c ON s.class_id = c.id";
        
if ($filterClassId) {
    $sql .= " WHERE s.class_id = " . intval($filterClassId);
}

$sql .= " ORDER BY s.full_name";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-badge"></i> Quản lý Học sinh</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="bi bi-plus-circle"></i> Thêm học sinh
        </button>
    </div>
    
    <div class="mb-3">
        <form method="GET" class="d-inline-flex">
            <select name="class_id" class="form-select me-2">
                <option value="">Tất cả lớp học</option>
                <?php foreach ($classrooms as $class): ?>
                <option value="<?php echo $class['id']; ?>" <?php echo ($filterClassId == $class['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Lọc</button>
            <?php if ($filterClassId): ?>
            <a href="students.php" class="btn btn-outline-secondary ms-2">Bỏ lọc</a>
            <?php endif; ?>
        </form>
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
                            <th>Họ và tên</th>
                            <th>Mã học sinh</th>
                            <th>Tên đăng nhập</th>
                            <th>Lớp</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Chưa có học sinh nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['username'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['class_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')" title="Xóa">
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm học sinh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã học sinh</label>
                            <input type="text" class="form-control" name="code">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp học</label>
                            <select class="form-select" name="class_id">
                                <option value="">Chọn lớp...</option>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
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

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa học sinh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã học sinh</label>
                            <input type="text" class="form-control" name="code" id="edit_code">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp học</label>
                            <select class="form-select" name="class_id" id="edit_class_id">
                                <option value="">Chọn lớp...</option>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone">
                        </div>
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
<div class="modal fade" id="deleteStudentModal" tabindex="-1">
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
                    <p>Bạn có chắc chắn muốn xóa học sinh <strong id="delete_name"></strong>?</p>
                    <p class="text-danger"><small>Thao tác này sẽ xóa tài khoản đăng nhập của học sinh!</small></p>
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
function editStudent(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_full_name').value = student.full_name;
    document.getElementById('edit_code').value = student.code || '';
    document.getElementById('edit_class_id').value = student.class_id || '';
    document.getElementById('edit_email').value = student.email || '';
    document.getElementById('edit_phone').value = student.phone || '';
    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
}

function deleteStudent(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
}
</script>
<?php require_once '../includes/footer.php'; ?>

