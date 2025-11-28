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
    
    /**
     * Module 1.2 - USER STORY 1.4
     * Xem thời khóa biểu chi tiết
     */
    public function schedule() {
        require_once __DIR__ . '/../models/Database.php';
        $userId = getCurrentUserId();
        $db = Database::getInstance()->getConnection();
        
        // Get student ID
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
        
        if (!$student || !$student['class_id']) {
            $this->setPageTitle('Thời khóa biểu');
            $this->render('student/schedule', [
                'error' => 'Bạn chưa được phân vào lớp học nào.'
            ]);
            return;
        }
        
        // Get view parameter (day/week)
        $view = $_GET['view'] ?? 'week';
        $currentDate = $_GET['date'] ?? date('Y-m-d');
        
        // Calculate date range
        if ($view === 'day') {
            $dateFrom = $currentDate;
            $dateTo = $currentDate;
        } else { // week
            $dateFrom = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
            $dateTo = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));
        }
        
        // Get schedules
        $filters = [
            'class_id' => $student['class_id'],
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $schedules = $this->scheduleModel->getWithDetails($filters);
        
        // Group by date
        $schedulesByDate = [];
        foreach ($schedules as $schedule) {
            $date = $schedule['schedule_date'];
            if (!isset($schedulesByDate[$date])) {
                $schedulesByDate[$date] = [];
            }
            $schedulesByDate[$date][] = $schedule;
        }
        
        // Sort by date
        ksort($schedulesByDate);
        
        $this->setPageTitle('Thời khóa biểu');
        $this->render('student/schedule', [
            'student' => $student,
            'schedules' => $schedulesByDate,
            'view' => $view,
            'currentDate' => $currentDate,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }
    
    /**
     * Module 2 - USER STORY 2.2
     * Xem danh sách hóa đơn học phí
     */
    public function invoices() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Invoice.php';
        
        $userId = getCurrentUserId();
        $db = Database::getInstance()->getConnection();
        
        // Get student ID
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
        
        $invoiceModel = new Invoice();
        
        // Get filter
        $statusFilter = $_GET['status'] ?? 'all';
        if (!in_array($statusFilter, ['all', 'unpaid', 'paid', 'cancelled'])) {
            $statusFilter = 'all';
        }
        
        // Get invoices
        if ($statusFilter === 'all') {
            $invoices = $invoiceModel->getByStudentId($studentId);
        } else {
            $invoices = $invoiceModel->findAll("student_id = ? AND status = ?", [$studentId, $statusFilter], "created_at DESC");
        }
        
        // Get statistics
        $allInvoices = $invoiceModel->getByStudentId($studentId);
        $stats = [
            'total' => count($allInvoices),
            'unpaid' => 0,
            'paid' => 0,
            'total_amount_unpaid' => 0,
            'total_amount_paid' => 0
        ];
        
        foreach ($allInvoices as $invoice) {
            if ($invoice['status'] === 'unpaid') {
                $stats['unpaid']++;
                $stats['total_amount_unpaid'] += floatval($invoice['total_amount']);
            } elseif ($invoice['status'] === 'paid') {
                $stats['paid']++;
                $stats['total_amount_paid'] += floatval($invoice['total_amount']);
            }
        }
        
        $this->setPageTitle('Hóa đơn Học phí');
        $this->render('student/invoices', [
            'invoices' => $invoices,
            'statusFilter' => $statusFilter,
            'stats' => $stats
        ]);
    }
}

