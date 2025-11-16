<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Classroom.php';

class StudentController extends BaseController {
    private $scheduleModel;
    private $studentModel;
    private $classroomModel;
    
    public function __construct() {
        $this->requireStudent();
        $this->scheduleModel = new Schedule();
        $this->studentModel = new Student();
        $this->classroomModel = new Classroom();
    }
    
    public function dashboard() {
        require_once __DIR__ . '/../models/Database.php';
        $userId = getCurrentUserId();
        $db = Database::getInstance()->getConnection();
        
        // Get student ID from profile_id
        $userStmt = $db->prepare("SELECT profile_id FROM users WHERE id = ? AND role = 'student'");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $studentId = $userData['profile_id'] ?? null;
        $userStmt->close();
        
        if (!$studentId) {
            $this->redirect('?action=logout');
            return;
        }
        
        $student = $this->studentModel->getByProfileId($studentId);
        
        $this->setPageTitle('Thời khóa biểu Học sinh');
        
        if (!$student || !$student['class_id']) {
            $this->render('student/dashboard', [
                'student' => $student,
                'error' => 'Bạn chưa được phân vào lớp học nào. Vui lòng liên hệ quản trị viên.'
            ]);
            return;
        }
        
        $classroom = $this->classroomModel->findById($student['class_id']);
        
        // Get schedules for the week (Monday to Friday)
        $filters = [
            'date_from' => date('Y-m-d', strtotime('monday this week')),
            'date_to' => date('Y-m-d', strtotime('friday this week'))
        ];
        
        $schedules = $this->scheduleModel->getByClassId($student['class_id'], $filters);
        
        $this->render('student/dashboard', [
            'student' => $student,
            'classroom' => $classroom,
            'schedules' => $schedules
        ]);
    }
}

