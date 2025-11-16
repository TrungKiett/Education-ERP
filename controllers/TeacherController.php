<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Teacher.php';

class TeacherController extends BaseController {
    private $scheduleModel;
    private $teacherModel;
    
    public function __construct() {
        $this->requireTeacher();
        $this->scheduleModel = new Schedule();
        $this->teacherModel = new Teacher();
    }
    
    public function dashboard() {
        require_once __DIR__ . '/../models/Database.php';
        $userId = getCurrentUserId();
        $db = Database::getInstance()->getConnection();
        
        // Get teacher ID from profile_id
        $userStmt = $db->prepare("SELECT profile_id FROM users WHERE id = ? AND role = 'teacher'");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $teacherId = $userData['profile_id'] ?? null;
        $userStmt->close();
        
        if (!$teacherId) {
            $this->redirect('index.php?action=logout');
            return;
        }
        
        $teacher = $this->teacherModel->getByProfileId($teacherId);
        
        // Get view parameter
        $view = $_GET['view'] ?? 'today';
        $currentDate = $_GET['date'] ?? date('Y-m-d');
        
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
        $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));
        $monthStart = date('Y-m-01', strtotime($currentDate));
        $monthEnd = date('Y-m-t', strtotime($currentDate));
        
        $filters = [];
        if ($view === 'today') {
            $filters['date'] = $today;
        } elseif ($view === 'week') {
            $filters['date_from'] = $weekStart;
            $filters['date_to'] = $weekEnd;
        } elseif ($view === 'month') {
            $filters['date_from'] = $monthStart;
            $filters['date_to'] = $monthEnd;
        }
        
        $schedules = $this->scheduleModel->getByTeacherId($teacherId, $filters);
        
        $this->setPageTitle('Trang Giáo viên');
        $this->render('teacher/dashboard', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'view' => $view,
            'currentDate' => $currentDate,
            'today' => $today,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd
        ]);
    }
}

