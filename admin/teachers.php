<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireAdmin();

$pageTitle = 'Quản lý Giáo viên';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $fullName = $_POST['full_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
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
                    // Create teacher record first (không có user_id trong teachers)
                    $code = $_POST['code'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $specialization = $_POST['specialization'] ?? '';
                    $note = $_POST['note'] ?? '';
                    
                    $teacherStmt = $conn->prepare("INSERT INTO teachers (code, full_name, email, phone, specialization, note) VALUES (?, ?, ?, ?, ?, ?)");
                    $teacherStmt->bind_param("ssssss", $code, $fullName, $email, $phone, $specialization, $note);
                    
                    if ($teacherStmt->execute()) {
                        $teacherId = $conn->insert_id;
                        
                        // Create user account with profile_id = teacher.id
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $userStmt = $conn->prepare("INSERT INTO users (username, password_hash, role, profile_id) VALUES (?, ?, 'teacher', ?)");
                        $userStmt->bind_param("ssi", $username, $hashedPassword, $teacherId);
                        
                        if ($userStmt->execute()) {
                            $message = 'Thêm giáo viên thành công!';
                            $messageType = 'success';
                        } else {
                            // Rollback: delete teacher if user creation fails
                            $conn->query("DELETE FROM teachers WHERE id = $teacherId");
                            $message = 'Lỗi khi tạo tài khoản!';
                            $messageType = 'danger';
                        }
                        $userStmt->close();
                    } else {
                        $message = 'Lỗi khi tạo giáo viên!';
                        $messageType = 'danger';
                    }
                    $teacherStmt->close();
                }
                $checkStmt->close();
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? 0;
            $fullName = $_POST['full_name'] ?? '';
            $code = $_POST['code'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $specialization = $_POST['specialization'] ?? '';
            $note = $_POST['note'] ?? '';
            
            if (empty($fullName)) {
                $message = 'Vui lòng điền đầy đủ thông tin!';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE teachers SET code = ?, full_name = ?, email = ?, phone = ?, specialization = ?, note = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $code, $fullName, $email, $phone, $specialization, $note, $id);
                
                if ($stmt->execute()) {
                    $message = 'Cập nhật giáo viên thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật!';
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            // Delete user first (where profile_id = teacher.id)
            $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE profile_id = ? AND role = 'teacher'");
            $deleteUserStmt->bind_param("i", $id);
            $deleteUserStmt->execute();
            $deleteUserStmt->close();
            
            // Delete teacher
            $deleteStmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                $message = 'Xóa giáo viên thành công!';
                $messageType = 'success';
            } else {
                $message = 'Lỗi khi xóa!';
                $messageType = 'danger';
            }
            $deleteStmt->close();
        }
    }
}

// Get all teachers
$teachers = [];
$result = $conn->query("
    SELECT t.*, u.username 
    FROM teachers t 
    LEFT JOIN users u ON u.profile_id = t.id AND u.role = 'teacher'
    ORDER BY t.full_name
");
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

closeDBConnection($conn);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-people"></i> Quản lý Giáo viên</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="bi bi-plus-circle"></i> Thêm giáo viên
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
                            <th>Mã</th>
                            <th>Họ và tên</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Chưa có giáo viên nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td><?php echo $teacher['id']; ?></td>
                            <td><?php echo htmlspecialchars($teacher['code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['username'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($teacher['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone'] ?? '-'); ?></td>
                            <td>
                                <a href="../index.php?action=admin.teacherSubjects&teacher_id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-info" title="Gán môn dạy">
                                    <i class="bi bi-book"></i>
                                </a>
                                <button class="btn btn-sm btn-warning" onclick="editTeacher(<?php echo htmlspecialchars(json_encode($teacher)); ?>)" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteTeacher(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['full_name']); ?>')" title="Xóa">
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

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm giáo viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Mã giáo viên</label>
                        <input type="text" class="form-control" name="code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chuyên môn</label>
                        <input type="text" class="form-control" name="specialization">
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

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa giáo viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Mã giáo viên</label>
                        <input type="text" class="form-control" name="code" id="edit_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chuyên môn</label>
                        <input type="text" class="form-control" name="specialization" id="edit_specialization">
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
<div class="modal fade" id="deleteTeacherModal" tabindex="-1">
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
                    <p>Bạn có chắc chắn muốn xóa giáo viên <strong id="delete_name"></strong>?</p>
                    <p class="text-danger"><small>Thao tác này sẽ xóa tài khoản đăng nhập của giáo viên!</small></p>
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
function editTeacher(teacher) {
    document.getElementById('edit_id').value = teacher.id;
    document.getElementById('edit_code').value = teacher.code || '';
    document.getElementById('edit_full_name').value = teacher.full_name;
    document.getElementById('edit_email').value = teacher.email || '';
    document.getElementById('edit_phone').value = teacher.phone || '';
    document.getElementById('edit_specialization').value = teacher.specialization || '';
    document.getElementById('edit_note').value = teacher.note || '';
    new bootstrap.Modal(document.getElementById('editTeacherModal')).show();
}

function deleteTeacher(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteTeacherModal')).show();
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

