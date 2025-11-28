# Trạng thái Triển khai 3 Module Mới

## ✅ Đã hoàn thành

### Database Schemas
- ✅ `sql/module_1_2_schema.sql` - Courses, Enrollments
- ✅ `sql/module_2_schema.sql` - Invoices, Discounts, Transactions + Trigger tự động tạo Invoice
- ✅ `sql/module_3_schema.sql` - Assignments, Submissions, Academic_Results + Trigger tự động cập nhật học bạ

### Models
- ✅ `models/Course.php`
- ✅ `models/EnrollmentRecord.php`
- ✅ `models/Invoice.php`
- ✅ `models/Discount.php`
- ✅ `models/Transaction.php`
- ✅ `models/Assignment.php`
- ✅ `models/Submission.php`
- ✅ `models/AcademicResult.php`
- ✅ Cập nhật `models/Schedule.php` - Thêm hỗ trợ date_from/date_to

### Controllers
- ✅ `controllers/AdminController.php` - Thêm method `assignClass()` (USER STORY 1.3)
- ✅ `controllers/StudentController.php` - Thêm method `schedule()` (USER STORY 1.4)

### Views
- ✅ `views/admin/assign_class.php` - Form phân lớp học sinh

## ⏳ Đang triển khai / Cần hoàn thiện

### Views cần tạo
- ⏳ `views/student/schedule.php` - Thời khóa biểu responsive (USER STORY 1.4)
- ⏳ `views/student/invoices.php` - Danh sách hóa đơn (USER STORY 2.2)
- ⏳ `views/student/assignments.php` - Danh sách bài tập và nộp bài (USER STORY 3.2)
- ⏳ `views/teacher/assignments.php` - Tạo và quản lý bài tập (USER STORY 3.1)
- ⏳ `views/teacher/grade_submissions.php` - Chấm điểm bài nộp (USER STORY 3.3)

### Controllers cần thêm methods
- ⏳ `controllers/StudentController.php`:
  - `invoices()` - Xem hóa đơn (USER STORY 2.2)
  - `assignments()` - Xem và nộp bài tập (USER STORY 3.2)
  
- ⏳ `controllers/TeacherController.php`:
  - `assignments()` - Tạo và quản lý bài tập (USER STORY 3.1)
  - `gradeSubmissions()` - Chấm điểm (USER STORY 3.3)
  
- ⏳ `controllers/PaymentController.php` (mới):
  - `processPayment()` - Xử lý thanh toán VNPAY (USER STORY 2.3)
  - `callback()` - Webhook handler (USER STORY 2.4)

### Email Notifications
- ⏳ Cập nhật `config/email.php`:
  - `sendInvoiceEmail()` - Gửi email hóa đơn (USER STORY 2.2)
  - `sendGradeNotification()` - Thông báo điểm số (USER STORY 3.4)
  - `sendAssignmentNotification()` - Thông báo bài tập mới (USER STORY 3.1)

### Payment Gateway Integration
- ⏳ Tạo `config/vnpay.php` - Cấu hình VNPAY
- ⏳ Tạo helper functions cho VNPAY payment
- ⏳ Tạo webhook handler cho callback từ VNPAY

### Routes cần thêm vào index.php
```php
// Module 1.2
'student.schedule' => StudentController::schedule()

// Module 2
'student.invoices' => StudentController::invoices()
'payment.process' => PaymentController::processPayment()
'payment.callback' => PaymentController::callback()

// Module 3
'teacher.assignments' => TeacherController::assignments()
'teacher.gradeSubmissions' => TeacherController::gradeSubmissions()
'student.assignments' => StudentController::assignments()
```

## 📋 Checklist theo User Stories

### Module 1.2: Phân lớp & Thời khóa biểu

#### USER STORY 1.3 (Admin) - Phân lớp
- ✅ Tìm kiếm và lọc học sinh đã phê duyệt
- ✅ Chọn lớp học và khóa học từ dropdown
- ✅ Tạo bản ghi Enrollment khi gán thành công
- ✅ Tự động tạo Invoice (qua trigger)

#### USER STORY 1.4 (HS/Phụ huynh) - Thời khóa biểu
- ✅ Hiển thị lịch học với Tên môn, Giảng viên, Thời gian, Phòng
- ⏳ Filter theo tuần (cần view)
- ⏳ Responsive mobile (cần view)

### Module 2: Tự động hóa Tài chính

#### USER STORY 2.1 (Hệ thống) - Tự động tạo hóa đơn
- ✅ Trigger tự động tạo Invoice khi Enrollment được tạo
- ✅ Tính toán tổng tiền dựa trên giá khóa học
- ✅ Hỗ trợ chiết khấu (Discount model đã có)
- ✅ Tạo với trạng thái Unpaid

#### USER STORY 2.2 (Phụ huynh) - Xem hóa đơn
- ⏳ Email thông báo hóa đơn
- ⏳ Hiển thị trên cổng thông tin
- ⏳ Link thanh toán

#### USER STORY 2.3 (Phụ huynh) - Thanh toán trực tuyến
- ⏳ Tích hợp VNPAY API
- ⏳ Form thanh toán
- ⏳ Xử lý callback

#### USER STORY 2.4 (Hệ thống) - Cập nhật trạng thái
- ⏳ Webhook handler xác minh
- ⏳ Cập nhật Invoice status
- ⏳ Tạo Transaction record

### Module 3: Nền tảng Giảng viên

#### USER STORY 3.1 (Giảng viên) - Tạo bài tập
- ⏳ Form tạo bài tập (tiêu đề, mô tả, lớp, hạn nộp, điểm tối đa)
- ⏳ Gửi thông báo cho học sinh

#### USER STORY 3.2 (Học sinh) - Nộp bài tập
- ⏳ Upload file lên Supabase Storage
- ⏳ Hiển thị trạng thái Submitted

#### USER STORY 3.3 (Giảng viên) - Chấm điểm
- ⏳ Danh sách bài nộp chờ chấm
- ⏳ Nhập điểm (0 - điểm tối đa)
- ⏳ Phản hồi (Feedback)

#### USER STORY 3.4 (Hệ thống) - Cập nhật kết quả
- ✅ Trigger tự động cập nhật Academic_Results
- ⏳ Gửi thông báo đến học sinh/phụ huynh

## 🚀 Các bước tiếp theo

1. **Hoàn thiện Views** - Tạo các view còn thiếu
2. **Thêm Controller Methods** - Hoàn thiện các methods trong controllers
3. **Email Notifications** - Tích hợp email cho các sự kiện
4. **Payment Gateway** - Tích hợp VNPAY
5. **Testing** - Test toàn bộ workflow
6. **Documentation** - Cập nhật tài liệu hướng dẫn

## 📝 Lưu ý

- Tất cả database triggers đã được tạo và sẽ tự động chạy
- Foreign keys đã được thiết lập với CASCADE/SET NULL phù hợp
- Models đã có đầy đủ methods cần thiết
- Cần cấu hình Supabase Storage cho file uploads
- Cần cấu hình email server cho notifications
- Cần đăng ký tài khoản VNPAY để test payment

