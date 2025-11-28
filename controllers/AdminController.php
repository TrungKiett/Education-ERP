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
        require_once __DIR__ . '/../models/Enrollment.php';
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
        
        // Get pending enrollments count
        $enrollmentModel = new Enrollment();
        $stats['pending_enrollments'] = $enrollmentModel->getPendingCount();
        
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
                            'phone' => $phone ?: null
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
        require_once __DIR__ . '/../models/Database.php';
        $db = Database::getInstance()->getConnection();
        
        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add') {
                $teacherId = $_POST['teacher_id'] ?? 0;
                $classId = $_POST['class_id'] ?? 0;
                $subjectId = $_POST['subject_id'] ?? 0;
                $scheduleDate = $_POST['schedule_date'] ?? '';
                $period = $_POST['period'] ?? 0;
                $room = $_POST['room'] ?? '';
                
                if (empty($teacherId) || empty($classId) || empty($subjectId) || empty($scheduleDate) || empty($period)) {
                    $_SESSION['schedule_message'] = 'Vui lòng điền đầy đủ thông tin!';
                    $_SESSION['schedule_message_type'] = 'danger';
                } else {
                    // Validate date format
                    $dateObj = DateTime::createFromFormat('Y-m-d', $scheduleDate);
                    if (!$dateObj || $dateObj->format('Y-m-d') !== $scheduleDate) {
                        $_SESSION['schedule_message'] = 'Ngày dạy không hợp lệ!';
                        $_SESSION['schedule_message_type'] = 'danger';
                    } else {
                        // Check conflicts
                        $conflicts = [];
                        
                        // Check teacher schedule conflict
                        $teacherCheckStmt = $db->prepare("SELECT id FROM schedules WHERE teacher_id = ? AND schedule_date = ? AND period = ?");
                        $teacherCheckStmt->bind_param("isi", $teacherId, $scheduleDate, $period);
                        $teacherCheckStmt->execute();
                        if ($teacherCheckStmt->get_result()->num_rows > 0) {
                            $conflicts[] = 'Giáo viên đã có lịch dạy trong thời gian này';
                        }
                        $teacherCheckStmt->close();
                        
                        // Check class schedule conflict
                        $classCheckStmt = $db->prepare("SELECT id FROM schedules WHERE class_id = ? AND schedule_date = ? AND period = ?");
                        $classCheckStmt->bind_param("isi", $classId, $scheduleDate, $period);
                        $classCheckStmt->execute();
                        if ($classCheckStmt->get_result()->num_rows > 0) {
                            $conflicts[] = 'Lớp học đã có lịch học trong thời gian này';
                        }
                        $classCheckStmt->close();
                        
                        // Check room conflict
                        if (!empty($room)) {
                            $roomCheckStmt = $db->prepare("SELECT id FROM schedules WHERE room = ? AND schedule_date = ? AND period = ?");
                            $roomCheckStmt->bind_param("ssi", $room, $scheduleDate, $period);
                            $roomCheckStmt->execute();
                            if ($roomCheckStmt->get_result()->num_rows > 0) {
                                $conflicts[] = 'Phòng học đã được sử dụng trong thời gian này';
                            }
                            $roomCheckStmt->close();
                        }
                        
                        // Note: Removed teaching assignment check - allow any teacher to teach any subject for any class
                        
                        if (!empty($conflicts)) {
                            $_SESSION['schedule_message'] = 'Lỗi: ' . implode(', ', $conflicts);
                            $_SESSION['schedule_message_type'] = 'danger';
                        } else {
                            // Insert schedule
                            $insertStmt = $db->prepare("INSERT INTO schedules (teacher_id, class_id, subject_id, schedule_date, period, room) VALUES (?, ?, ?, ?, ?, ?)");
                            $insertStmt->bind_param("iiisis", $teacherId, $classId, $subjectId, $scheduleDate, $period, $room);
                            
                            if ($insertStmt->execute()) {
                                $_SESSION['schedule_message'] = 'Phân công lịch dạy thành công!';
                                $_SESSION['schedule_message_type'] = 'success';
                                $insertMonth = date('n', strtotime($scheduleDate));
                                $insertYear = date('Y', strtotime($scheduleDate));
                                $this->redirect("?action=admin.schedules&month=$insertMonth&year=$insertYear");
                                return;
                            } else {
                                $_SESSION['schedule_message'] = 'Lỗi khi phân công lịch dạy: ' . $db->error;
                                $_SESSION['schedule_message_type'] = 'danger';
                            }
                            $insertStmt->close();
                        }
                    }
                }
            } elseif ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                $deleteMonth = isset($_POST['month']) ? (int)$_POST['month'] : date('n');
                $deleteYear = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
                if ($id > 0) {
                    $deleteStmt = $db->prepare("DELETE FROM schedules WHERE id = ?");
                    $deleteStmt->bind_param("i", $id);
                    if ($deleteStmt->execute()) {
                        $_SESSION['schedule_message'] = 'Xóa lịch dạy thành công!';
                        $_SESSION['schedule_message_type'] = 'success';
                        $this->redirect("?action=admin.schedules&month=$deleteMonth&year=$deleteYear");
                        return;
                    } else {
                        $_SESSION['schedule_message'] = 'Lỗi khi xóa!';
                        $_SESSION['schedule_message_type'] = 'danger';
                    }
                    $deleteStmt->close();
                }
            }
        }
        
        // Get month/year from query params, default to current month
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        // Validate month/year
        if ($month < 1 || $month > 12) $month = date('n');
        if ($year < 2020 || $year > 2100) $year = date('Y');
        
        // Get first and last day of month
        $firstDay = date('Y-m-01', strtotime("$year-$month-01"));
        $lastDay = date('Y-m-t', strtotime("$year-$month-01"));
        
        // Get all schedules for this month
        $sql = "SELECT ts.*, 
                       t.full_name as teacher_name, 
                       c.name as class_name, 
                       s.name as subject_name 
                FROM schedules ts
                JOIN teachers t ON ts.teacher_id = t.id
                JOIN classrooms c ON ts.class_id = c.id
                JOIN subjects s ON ts.subject_id = s.id
                WHERE ts.schedule_date >= ? AND ts.schedule_date <= ?
                ORDER BY ts.schedule_date, ts.period";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $firstDay, $lastDay);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $date = $row['schedule_date'];
            if (!isset($schedules[$date])) {
                $schedules[$date] = [];
            }
            $schedules[$date][] = $row;
        }
        $stmt->close();
        
        // Get all classrooms and teachers for filters
        $classrooms = [];
        $classResult = $db->query("SELECT id, name FROM classrooms ORDER BY name");
        while ($row = $classResult->fetch_assoc()) {
            $classrooms[] = $row;
        }
        
        $teachers = [];
        $teacherResult = $db->query("SELECT id, full_name FROM teachers ORDER BY full_name");
        while ($row = $teacherResult->fetch_assoc()) {
            $teachers[] = $row;
        }
        
        // Get all subjects for add form
        $subjects = [];
        $subjectResult = $db->query("SELECT id, name, code FROM subjects ORDER BY name");
        while ($row = $subjectResult->fetch_assoc()) {
            $subjects[] = $row;
        }
        
        // Get teacher subjects for add form (keep for backward compatibility if needed)
        $teacherSubjects = [];
        $tsResult = $db->query("
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
        
        // Get message from session if exists
        $message = $_SESSION['schedule_message'] ?? '';
        $messageType = $_SESSION['schedule_message_type'] ?? '';
        unset($_SESSION['schedule_message'], $_SESSION['schedule_message_type']);
        
        $this->setPageTitle('Phân công Lịch dạy');
        $this->render('admin/schedules', [
            'schedules' => $schedules,
            'month' => $month,
            'year' => $year,
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'classrooms' => $classrooms,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'teacherSubjects' => $teacherSubjects,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
    
    public function teacherSubjects() {
        $this->render('admin/teacher_subjects', []);
    }
    
    /**
     * Module 1.2 - USER STORY 1.3
     * Phân lớp học sinh vào lớp học và khóa học
     */
    public function assignClass() {
        $this->requireAdmin();
        
        require_once __DIR__ . '/../models/EnrollmentRecord.php';
        require_once __DIR__ . '/../models/Course.php';
        
        $enrollmentModel = new EnrollmentRecord();
        $courseModel = new Course();
        $message = '';
        $messageType = '';
        
        // Handle POST - Assign student to class and course
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = intval($_POST['student_id'] ?? 0);
            $classId = intval($_POST['class_id'] ?? 0);
            $courseId = intval($_POST['course_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            
            if ($studentId > 0 && $classId > 0 && $courseId > 0) {
                // Check if already enrolled
                if ($enrollmentModel->checkExisting($studentId, $classId, $courseId)) {
                    $message = 'Học sinh đã được gán vào lớp và khóa học này!';
                    $messageType = 'warning';
                } else {
                    $enrollmentData = [
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'course_id' => $courseId,
                        'enrollment_date' => date('Y-m-d'),
                        'status' => 'active',
                        'notes' => $notes ?: null
                    ];
                    
                    $enrollmentId = $enrollmentModel->create($enrollmentData);
                    
                    if ($enrollmentId) {
                        // Update student's class_id if not set
                        $student = $this->studentModel->findById($studentId);
                        if (!$student['class_id']) {
                            $this->studentModel->update($studentId, ['class_id' => $classId]);
                        }
                        
                        $message = 'Phân lớp thành công! Hóa đơn sẽ được tạo tự động.';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi phân lớp!';
                        $messageType = 'danger';
                    }
                }
            } else {
                $message = 'Vui lòng chọn đầy đủ Học sinh, Lớp học và Khóa học!';
                $messageType = 'danger';
            }
        }
        
        // Get approved students (from enrollment_applications)
        require_once __DIR__ . '/../models/Enrollment.php';
        $enrollmentAppModel = new Enrollment();
        $approvedApplications = $enrollmentAppModel->findByStatus('approved');
        
        // Get all students (already in system)
        $allStudents = $this->studentModel->getWithClassroom();
        
        // Combine: students without class assignment
        $studentsToAssign = [];
        foreach ($allStudents as $student) {
            if (!$student['class_id']) {
                $studentsToAssign[] = $student;
            }
        }
        
        // Add approved applications that haven't been assigned
        foreach ($approvedApplications as $app) {
            // Check if student already exists
            $exists = false;
            foreach ($allStudents as $student) {
                if ($student['full_name'] === $app['full_name'] && 
                    $student['phone'] === $app['phone']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $studentsToAssign[] = [
                    'id' => null,
                    'full_name' => $app['full_name'],
                    'phone' => $app['phone'],
                    'email' => $app['email'],
                    'date_of_birth' => $app['date_of_birth'],
                    'address' => $app['address'],
                    'from_application' => true,
                    'application_id' => $app['id']
                ];
            }
        }
        
        // Get classrooms and courses
        $classrooms = $this->classroomModel->findAll('', [], 'name ASC');
        $courses = $courseModel->getActiveCourses();
        
        // Get existing enrollments
        $existingEnrollments = [];
        if (!empty($allStudents)) {
            foreach ($allStudents as $student) {
                if ($student['id']) {
                    $enrollments = $enrollmentModel->getByStudentId($student['id']);
                    if (!empty($enrollments)) {
                        $existingEnrollments[$student['id']] = $enrollments;
                    }
                }
            }
        }
        
        $this->setPageTitle('Phân lớp Học sinh');
        $this->render('admin/assign_class', [
            'students' => $studentsToAssign,
            'classrooms' => $classrooms,
            'courses' => $courses,
            'existingEnrollments' => $existingEnrollments,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
}

