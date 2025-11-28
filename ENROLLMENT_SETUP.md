# Hướng dẫn thiết lập Hệ thống Quản lý Hồ sơ Tuyển sinh

## Tổng quan

Hệ thống quản lý hồ sơ tuyển sinh đã được tích hợp vào hệ thống quản lý giáo dục với 2 user stories:

1. **USER STORY 1.1**: Phụ huynh/Học sinh có thể điền và nộp đơn đăng ký trực tuyến
2. **USER STORY 1.2**: Ban Quản lý có thể xem xét và phê duyệt hồ sơ tuyển sinh

## Cài đặt Database

1. Chạy file SQL để tạo bảng `enrollment_applications`:

```sql
-- Chạy file: sql/enrollment_applications.sql
```

Hoặc import trực tiếp vào MySQL:

```bash
mysql -u root -p edu < sql/enrollment_applications.sql
```

## Cấu hình Supabase Storage (Tùy chọn)

Để sử dụng Supabase Storage cho việc lưu trữ tài liệu:

1. Tạo tài khoản Supabase tại https://supabase.com
2. Tạo một project mới
3. Tạo Storage bucket tên `enrollment-documents` (hoặc tên khác)
4. Cập nhật file `config/supabase.php`:

```php
define('SUPABASE_URL', 'https://your-project.supabase.co');
define('SUPABASE_KEY', 'your-anon-key');
define('SUPABASE_BUCKET', 'enrollment-documents');
```

Hoặc sử dụng biến môi trường:

```bash
# .env file hoặc trong config
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_BUCKET=enrollment-documents
```

**Lưu ý**: Nếu không cấu hình Supabase, hệ thống sẽ tự động lưu file vào thư mục `uploads/enrollments/` (fallback).

## Cấu hình Email

File `config/email.php` sử dụng hàm `mail()` mặc định của PHP. 

Để sử dụng trong production, bạn nên:

1. **Cấu hình SMTP** (khuyến nghị sử dụng PHPMailer):
   - Cài đặt: `composer require phpmailer/phpmailer`
   - Cập nhật hàm `sendEnrollmentConfirmationEmail()` trong `config/email.php`

2. **Hoặc sử dụng dịch vụ email** như:
   - SendGrid
   - Mailgun
   - Amazon SES

## Tạo thư mục Uploads

Đảm bảo thư mục uploads tồn tại và có quyền ghi:

```bash
mkdir -p uploads/enrollments
chmod 755 uploads/enrollments
```

## Routes

### Public Routes (Không cần đăng nhập)
- `/?action=enrollment` - Form đăng ký tuyển sinh công khai
- `/?action=enrollment.form` - Tương tự như trên

### Admin Routes (Cần đăng nhập với quyền admin)
- `/?action=enrollment.adminEnrollments` - Dashboard quản lý hồ sơ tuyển sinh
- `/?action=enrollment.adminEnrollments&status=pending` - Xem hồ sơ chờ xét duyệt
- `/?action=enrollment.adminEnrollments&status=approved` - Xem hồ sơ đã phê duyệt
- `/?action=enrollment.adminEnrollments&status=rejected` - Xem hồ sơ đã từ chối

## Tính năng

### USER STORY 1.1 - Form đăng ký công khai
- ✅ Form đăng ký với đầy đủ thông tin: Tên, Ngày sinh, Địa chỉ, SĐT, Email
- ✅ Upload nhiều tài liệu đính kèm (PDF, JPG, PNG, tối đa 5MB/file)
- ✅ Lưu trữ tài liệu trên Supabase Storage (hoặc local fallback)
- ✅ Gửi email xác nhận tự động sau khi nộp thành công
- ✅ Validation đầy đủ các trường bắt buộc

### USER STORY 1.2 - Dashboard quản lý (Admin)
- ✅ Dashboard hiển thị thống kê: Chờ xét duyệt, Đã phê duyệt, Đã từ chối
- ✅ Filter theo trạng thái (Pending/Approved/Rejected)
- ✅ Danh sách hồ sơ với đầy đủ thông tin
- ✅ Xem và tải tài liệu đính kèm
- ✅ Nút Phê duyệt (Approve) - tự động tạo bản ghi Student mới
- ✅ Nút Từ chối (Reject) - yêu cầu nhập lý do
- ✅ Hiển thị badge số lượng hồ sơ chờ xét duyệt trên Admin Dashboard

## Cấu trúc Files

```
edu/
├── config/
│   ├── email.php          # Cấu hình gửi email
│   └── supabase.php       # Cấu hình Supabase Storage
├── controllers/
│   └── EnrollmentController.php  # Controller xử lý enrollment
├── models/
│   └── Enrollment.php     # Model cho enrollment_applications
├── views/
│   ├── enrollment/
│   │   └── form.php       # Form đăng ký công khai
│   └── admin/
│       └── enrollments.php # Dashboard quản lý (admin)
├── sql/
│   └── enrollment_applications.sql  # SQL tạo bảng
└── uploads/
    └── enrollments/       # Thư mục lưu file (fallback)
```

## Lưu ý quan trọng

1. **Bảng students**: Khi phê duyệt hồ sơ, hệ thống tự động tạo bản ghi Student. Đảm bảo bảng `students` có các cột: `full_name`, `phone`, `email` (và tùy chọn: `date_of_birth`, `address`).

2. **Quyền truy cập**: 
   - Form đăng ký công khai: Không cần đăng nhập
   - Dashboard quản lý: Chỉ admin mới truy cập được

3. **Bảo mật**: 
   - File upload được validate về loại và kích thước
   - SQL injection được bảo vệ bằng prepared statements
   - XSS được bảo vệ bằng `htmlspecialchars()`

4. **Performance**: 
   - Có thể thêm index cho các cột thường query: `status`, `created_at`
   - Cân nhắc giới hạn số lượng file upload

## Testing

1. Test form đăng ký:
   - Truy cập `/?action=enrollment`
   - Điền đầy đủ thông tin và submit
   - Kiểm tra email xác nhận (nếu có cấu hình)

2. Test admin dashboard:
   - Đăng nhập với tài khoản admin
   - Truy cập `/?action=enrollment.adminEnrollments`
   - Test phê duyệt và từ chối hồ sơ
   - Kiểm tra bản ghi Student được tạo tự động

## Troubleshooting

1. **Lỗi upload file**: 
   - Kiểm tra quyền ghi của thư mục `uploads/enrollments/`
   - Kiểm tra cấu hình `upload_max_filesize` và `post_max_size` trong php.ini

2. **Email không gửi được**:
   - Kiểm tra cấu hình SMTP hoặc mail server
   - Kiểm tra spam folder
   - Sử dụng dịch vụ email thứ 3 như SendGrid

3. **Supabase upload lỗi**:
   - Kiểm tra SUPABASE_URL và SUPABASE_KEY
   - Kiểm tra bucket đã được tạo và có quyền public
   - Hệ thống sẽ tự động fallback về local storage nếu Supabase lỗi

