<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../config/supabase.php';

class EnrollmentController extends BaseController {
    private $enrollmentModel;
    private $studentModel;
    private $userModel;
    
    public function __construct() {
        $this->enrollmentModel = new Enrollment();
        $this->studentModel = new Student();
        $this->userModel = new User();
    }
    
    /**
     * Public enrollment form - USER STORY 1.1
     * Phụ huynh/Học sinh có thể điền và nộp đơn đăng ký trực tuyến
     */
    public function enrollmentForm() {
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $dateOfBirth = $_POST['date_of_birth'] ?? '';
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            // Validation
            if (empty($fullName) || empty($dateOfBirth) || empty($address) || empty($phone)) {
                $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                $messageType = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
                $message = 'Email không hợp lệ!';
                $messageType = 'danger';
            } else {
                // Handle file uploads
                $documents = [];
                $uploadDir = __DIR__ . '/../uploads/enrollments/';
                
                // Create upload directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Process uploaded files
                if (!empty($_FILES['documents']['name'][0])) {
                    $fileCount = count($_FILES['documents']['name']);
                    
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $_FILES['documents']['tmp_name'][$i];
                            $originalName = $_FILES['documents']['name'][$i];
                            $fileSize = $_FILES['documents']['size'][$i];
                            
                            // Validate file size (max 5MB per file)
                            if ($fileSize > 5 * 1024 * 1024) {
                                $message = "File {$originalName} vượt quá 5MB!";
                                $messageType = 'danger';
                                break;
                            }
                            
                            // Validate file type
                            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                            $fileType = $_FILES['documents']['type'][$i];
                            
                            if (!in_array($fileType, $allowedTypes)) {
                                $message = "File {$originalName} không đúng định dạng (chỉ chấp nhận PDF, JPG, PNG)!";
                                $messageType = 'danger';
                                break;
                            }
                            
                            // Upload to Supabase Storage
                            $uploadedUrl = uploadToSupabase($tmpName, $originalName);
                            
                            if ($uploadedUrl) {
                                $documents[] = [
                                    'name' => $originalName,
                                    'url' => $uploadedUrl,
                                    'size' => $fileSize
                                ];
                            } else {
                                // Fallback: save locally if Supabase fails
                                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                                $newFileName = uniqid() . '_' . time() . '.' . $extension;
                                $destination = $uploadDir . $newFileName;
                                
                                if (move_uploaded_file($tmpName, $destination)) {
                                    $documents[] = [
                                        'name' => $originalName,
                                        'url' => '/uploads/enrollments/' . $newFileName,
                                        'size' => $fileSize
                                    ];
                                }
                            }
                        }
                    }
                }
                
                // If no validation errors, create enrollment application
                if (empty($message)) {
                    $enrollmentData = [
                        'full_name' => $fullName,
                        'date_of_birth' => $dateOfBirth,
                        'address' => $address,
                        'phone' => $phone,
                        'email' => $email ?: null,
                        'documents' => !empty($documents) ? json_encode($documents, JSON_UNESCAPED_UNICODE) : null,
                        'status' => 'pending'
                    ];
                    
                    $applicationId = $this->enrollmentModel->create($enrollmentData);
                    
                    if ($applicationId) {
                        // Send confirmation email
                        $emailToSend = $email ?: 'no-email@example.com';
                        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            sendEnrollmentConfirmationEmail($email, $fullName, $applicationId);
                        }
                        
                        $message = 'Nộp hồ sơ thành công! Mã hồ sơ của bạn: #' . $applicationId . '. Chúng tôi sẽ liên hệ với bạn sớm nhất.';
                        $messageType = 'success';
                        
                        // Clear form data
                        $_POST = [];
                    } else {
                        $message = 'Lỗi khi nộp hồ sơ! Vui lòng thử lại.';
                        $messageType = 'danger';
                    }
                }
            }
        }
        
        $this->setPageTitle('Đăng ký tuyển sinh');
        $this->render('enrollment/form', [
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
    
    /**
     * Admin dashboard for enrollment management - USER STORY 1.2
     * Ban Quản lý xem xét và phê duyệt hồ sơ tuyển sinh
     */
    public function adminEnrollments() {
        $this->requireAdmin();
        
        $message = '';
        $messageType = '';
        
        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id = intval($_POST['id'] ?? 0);
            
            if ($action === 'approve' && $id > 0) {
                $application = $this->enrollmentModel->findById($id);
                
                if ($application && $application['status'] === 'pending') {
                    // Update status to approved
                    $notes = trim($_POST['notes'] ?? '');
                    $this->enrollmentModel->updateStatus($id, 'approved', $notes);
                    
                    // Automatically create Student record
                    // Note: Adjust fields based on your actual students table structure
                    $studentData = [
                        'full_name' => $application['full_name'],
                        'phone' => $application['phone'],
                        'email' => $application['email'] ?: null
                    ];
                    
                    // Add optional fields if they exist in students table
                    // Uncomment if your students table has these columns:
                    // $studentData['date_of_birth'] = $application['date_of_birth'];
                    // $studentData['address'] = $application['address'];
                    
                    $studentId = $this->studentModel->create($studentData);
                    
                    if ($studentId) {
                        $message = 'Phê duyệt hồ sơ thành công và đã tạo bản ghi học sinh mới!';
                        $messageType = 'success';
                    } else {
                        $message = 'Phê duyệt hồ sơ thành công nhưng có lỗi khi tạo bản ghi học sinh!';
                        $messageType = 'warning';
                    }
                } else {
                    $message = 'Hồ sơ không tồn tại hoặc đã được xử lý!';
                    $messageType = 'danger';
                }
            } elseif ($action === 'reject' && $id > 0) {
                $notes = trim($_POST['notes'] ?? '');
                if (empty($notes)) {
                    $message = 'Vui lòng nhập lý do từ chối!';
                    $messageType = 'danger';
                } else {
                    if ($this->enrollmentModel->updateStatus($id, 'rejected', $notes)) {
                        $message = 'Từ chối hồ sơ thành công!';
                        $messageType = 'success';
                    } else {
                        $message = 'Lỗi khi từ chối hồ sơ!';
                        $messageType = 'danger';
                    }
                }
            }
        }
        
        // Get filter
        $statusFilter = $_GET['status'] ?? 'pending';
        if (!in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $statusFilter = 'pending';
        }
        
        // Get enrollments
        $enrollments = $this->enrollmentModel->findByStatus($statusFilter);
        
        // Parse documents JSON for each enrollment
        foreach ($enrollments as &$enrollment) {
            if (!empty($enrollment['documents'])) {
                $enrollment['documents'] = json_decode($enrollment['documents'], true);
            } else {
                $enrollment['documents'] = [];
            }
        }
        
        // Get statistics
        $stats = [
            'pending' => $this->enrollmentModel->findByStatus('pending'),
            'approved' => $this->enrollmentModel->findByStatus('approved'),
            'rejected' => $this->enrollmentModel->findByStatus('rejected')
        ];
        
        $stats['pending_count'] = count($stats['pending']);
        $stats['approved_count'] = count($stats['approved']);
        $stats['rejected_count'] = count($stats['rejected']);
        
        $this->setPageTitle('Quản lý Hồ sơ Tuyển sinh');
        $this->render('admin/enrollments', [
            'enrollments' => $enrollments,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
}

