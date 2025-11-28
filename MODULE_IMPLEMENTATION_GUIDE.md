# Hướng dẫn Triển khai 3 Module Mới

## Tổng quan

Tài liệu này mô tả cách triển khai 3 module mới dựa trên đặc tả User Stories:

1. **Module 1.2**: Phân lớp & Thời khóa biểu
2. **Module 2**: Tự động hóa Tài chính (Học phí)
3. **Module 3**: Nền tảng Giảng viên (Giao bài tập & Chấm điểm)

## Bước 1: Chạy Database Migrations

Chạy các file SQL theo thứ tự:

```bash
mysql -u root -p edu < sql/module_1_2_schema.sql
mysql -u root -p edu < sql/module_2_schema.sql
mysql -u root -p edu < sql/module_3_schema.sql
```

## Bước 2: Cấu trúc Files đã tạo

### Models (đã tạo)
- `models/Course.php` - Quản lý khóa học
- `models/EnrollmentRecord.php` - Quản lý ghi danh (khác với Enrollment cho tuyển sinh)
- `models/Invoice.php` - Quản lý hóa đơn
- `models/Discount.php` - Quản lý chiết khấu
- `models/Transaction.php` - Quản lý giao dịch
- `models/Assignment.php` - Quản lý bài tập
- `models/Submission.php` - Quản lý bài nộp
- `models/AcademicResult.php` - Quản lý học bạ điện tử

### Controllers (cần thêm methods)
- `controllers/AdminController.php` - Thêm `assignClass()`
- `controllers/StudentController.php` - Thêm `schedule()`, `assignments()`, `invoices()`
- `controllers/TeacherController.php` - Thêm `assignments()`, `gradeSubmissions()`

### Views (cần tạo)
- `views/admin/assign_class.php`
- `views/student/schedule.php`
- `views/student/assignments.php`
- `views/student/invoices.php`
- `views/teacher/assignments.php`
- `views/teacher/grade_submissions.php`

## Bước 3: Routes cần thêm vào index.php

```php
// Module 1.2
'admin.assignClass' => AdminController::assignClass()
'student.schedule' => StudentController::schedule()

// Module 2
'student.invoices' => StudentController::invoices()
'payment.callback' => PaymentController::callback() // Webhook handler

// Module 3
'teacher.assignments' => TeacherController::assignments()
'teacher.gradeSubmissions' => TeacherController::gradeSubmissions()
'student.assignments' => StudentController::assignments()
```

## Tính năng tự động

### Module 2.1 - Tự động tạo Invoice
- **Trigger**: `trg_create_invoice_on_enrollment` (đã tạo trong SQL)
- Tự động tạo hóa đơn khi Enrollment được tạo
- Số hóa đơn: `INV-YYYYMMDD-XXXX`
- Hạn chót: 7 ngày sau ngày ghi danh

### Module 3.2 - Tự động cập nhật Academic Results
- **Trigger**: `trg_update_academic_results_on_grading` (đã tạo trong SQL)
- Tự động cập nhật học bạ khi điểm được chấm
- Tính điểm chữ (A, B, C, D, F) tự động

## Các bước tiếp theo

1. ✅ Database schemas đã tạo
2. ✅ Models đã tạo
3. ⏳ Controllers methods (đang triển khai)
4. ⏳ Views (đang triển khai)
5. ⏳ Email notifications
6. ⏳ Payment gateway integration (VNPAY)
7. ⏳ Webhook handlers

## Lưu ý quan trọng

1. **Enrollment vs EnrollmentRecord**: 
   - `enrollment_applications` = Hồ sơ tuyển sinh (chờ phê duyệt)
   - `enrollments` = Ghi danh (đã được phân lớp)

2. **Foreign Keys**: Tất cả foreign keys đã được định nghĩa trong SQL với CASCADE/SET NULL phù hợp

3. **Indexes**: Đã thêm indexes cho các cột thường query (status, dates, foreign keys)

4. **Triggers**: Cần kiểm tra MySQL version hỗ trợ triggers (5.0.2+)

