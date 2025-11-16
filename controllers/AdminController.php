<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Classroom.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/TeachingAssignment.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends BaseController {
    private $teacherModel;
    private $studentModel;
    private $classroomModel;
    private $subjectModel;
    private $scheduleModel;
    private $teachingAssignmentModel;
    private $userModel;
    
    public function __construct() {
        $this->requireAdmin();
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->classroomModel = new Classroom();
        $this->subjectModel = new Subject();
        $this->scheduleModel = new Schedule();
        $this->teachingAssignmentModel = new TeachingAssignment();
        $this->userModel = new User();
    }
    
    public function dashboard() {
        require_once __DIR__ . '/../models/Database.php';
        $db = Database::getInstance()->getConnection();
        
        // Get statistics
        $stats = [];
        $result = $db->query("SELECT COUNT(*) as count FROM teachers");
        $stats['teachers'] = $result->fetch_assoc()['count'];
        
        $result = $db->query("SELECT COUNT(*) as count FROM students");
        $stats['students'] = $result->fetch_assoc()['count'];
        
        $result = $db->query("SELECT COUNT(*) as count FROM classrooms");
        $stats['classrooms'] = $result->fetch_assoc()['count'];
        
        $result = $db->query("SELECT COUNT(*) as count FROM subjects");
        $stats['subjects'] = $result->fetch_assoc()['count'];
        
        $this->setPageTitle('Trang quản trị');
        $this->render('admin/dashboard', ['stats' => $stats]);
    }
    
    // Teachers management
    public function teachers() {
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add') {
                $fullName = $_POST['full_name'] ?? '';
                $code = $_POST['code'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($fullName) || empty($username) || empty($password)) {
                    $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                    $messageType = 'danger';
                } else {
                    if ($this->userModel->findByUsername($username)) {
                        $message = 'Tên đăng nhập đã tồn tại!';
                        $messageType = 'danger';
                    } else {
                        $teacherData = [
                            'code' => $code,
                            'full_name' => $fullName,
                            'email' => $email ?: null,
                            'phone' => $phone ?: null,
                            'username' => $username
                        ];
                        
                        $teacherId = $this->teacherModel->create($teacherData);
                        
                        if ($teacherId) {
                            $userId = $this->userModel->createUser($username, $password, 'teacher', $teacherId, $email);
                            if ($userId) {
                                $message = 'Thêm giáo viên thành công!';
                                $messageType = 'success';
                            } else {
                                $this->teacherModel->delete($teacherId);
                                $message = 'Lỗi khi tạo tài khoản!';
                                $messageType = 'danger';
                            }
                        } else {
                            $message = 'Lỗi khi tạo giáo viên!';
                            $messageType = 'danger';
                        }
                    }
                }
            } elseif ($action === 'edit') {
                $id = $_POST['id'] ?? 0;
                $fullName = $_POST['full_name'] ?? '';
                $code = $_POST['code'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                
                if (empty($fullName)) {
                    $message = 'Vui lòng điền đầy đủ thông tin!';
                    $messageType = 'danger';
                } else {
                    $teacherData = [
                        'code' => $code,
                        'full_name' => $fullName,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null
                    ];
                    
                    if ($this->teacherModel->update($id, $teacherData)) {
                        $message = 'Cập nhật giáo viên thành công!';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi cập nhật!';
                        $messageType = 'danger';
                    }
                }
            } elseif ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                
                // Delete user first
                $db = Database::getInstance()->getConnection();
                $deleteUserStmt = $db->prepare("DELETE FROM users WHERE profile_id = ? AND role = 'teacher'");
                $deleteUserStmt->bind_param("i", $id);
                $deleteUserStmt->execute();
                $deleteUserStmt->close();
                
                if ($this->teacherModel->delete($id)) {
                    $message = 'Xóa giáo viên thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa!';
                    $messageType = 'danger';
                }
            }
        }
        
        $teachers = $this->teacherModel->getWithUser();
        $this->render('admin/teachers', [
            'teachers' => $teachers,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
    
    // Similar methods for students, classrooms, subjects, schedules...
    // For brevity, I'll create the structure and you can expand
    
    public function students() {
        // Similar to teachers method
        $this->render('admin/students', []);
    }
    
    public function classrooms() {
        $this->render('admin/classrooms', []);
    }
    
    public function subjects() {
        $this->render('admin/subjects', []);
    }
    
    public function schedules() {
        $this->render('admin/schedules', []);
    }
    
    public function teacherSubjects() {
        $this->render('admin/teacher_subjects', []);
    }
}

