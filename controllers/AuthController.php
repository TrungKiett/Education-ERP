<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';

class AuthController extends BaseController {
    private $userModel;
    private $studentModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->studentModel = new Student();
    }
    
    public function login() {
        if (isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ thông tin!';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    $this->redirectToDashboard();
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
                }
            }
        }
        
        $this->setPageTitle('Đăng nhập');
        $this->render('auth/login', ['error' => $error]);
    }
    
    public function register() {
        if (isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        $error = '';
        $success = '';
        
        require_once __DIR__ . '/../models/Classroom.php';
        $classroomModel = new Classroom();
        $classrooms = $classroomModel->findAll('', [], 'name');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $code = $_POST['code'] ?? '';
            $classId = $_POST['class_id'] ?? null;
            
            if (empty($fullName) || empty($username) || empty($password)) {
                $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
            } else {
                // Check if username exists
                if ($this->userModel->findByUsername($username)) {
                    $error = 'Tên đăng nhập đã tồn tại!';
                } else {
                    // Create student record first
                    $studentData = [
                        'code' => $code,
                        'full_name' => $fullName,
                        'class_id' => $classId ?: null,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'username' => $username
                    ];
                    
                    $studentId = $this->studentModel->create($studentData);
                    
                    if ($studentId) {
                        // Create user account
                        $userId = $this->userModel->createUser($username, $password, 'student', $studentId, $email);
                        
                        if ($userId) {
                            $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        } else {
                            // Rollback
                            $this->studentModel->delete($studentId);
                            $error = 'Lỗi khi tạo tài khoản! Vui lòng thử lại.';
                        }
                    } else {
                        $error = 'Lỗi khi tạo hồ sơ học sinh! Vui lòng thử lại.';
                    }
                }
            }
        }
        
        $this->setPageTitle('Đăng ký');
        $this->render('auth/register', [
            'error' => $error,
            'success' => $success,
            'classrooms' => $classrooms
        ]);
    }
    
    public function logout() {
        session_start();
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        $this->redirect('index.php?action=login');
    }
    
    private function redirectToDashboard() {
        $role = getCurrentRole();
        if ($role === 'admin') {
            $this->redirect('index.php?action=admin.dashboard');
        } elseif ($role === 'teacher') {
            $this->redirect('index.php?action=teacher.dashboard');
        } elseif ($role === 'student') {
            $this->redirect('index.php?action=student.dashboard');
        }
    }
}

