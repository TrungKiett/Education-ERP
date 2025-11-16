# Hệ thống Quản lý Giáo dục

Hệ thống quản lý giáo dục được xây dựng theo mô hình MVC (Model-View-Controller).

## Cấu trúc thư mục

```
edu/
├── index.php              # Entry point duy nhất - Router
├── config/                # Cấu hình
│   ├── database.php       # Cấu hình database
│   └── session.php        # Quản lý session
├── models/                # Model classes (Data layer)
│   ├── Database.php
│   ├── Model.php
│   ├── User.php
│   ├── Teacher.php
│   ├── Student.php
│   ├── Classroom.php
│   ├── Subject.php
│   ├── Schedule.php
│   └── TeachingAssignment.php
├── controllers/           # Controller classes (Logic layer)
│   ├── BaseController.php
│   ├── AuthController.php
│   ├── AdminController.php
│   ├── TeacherController.php
│   └── StudentController.php
├── views/                 # View templates (Presentation layer)
│   ├── layouts/
│   │   ├── header.php
│   │   └── footer.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── admin/
│   │   └── dashboard.php
│   ├── teacher/
│   │   └── dashboard.php
│   └── student/
│       └── dashboard.php
└── sql/
    └── database.sql       # Database schema
```

## Cách sử dụng

Truy cập hệ thống qua `index.php` với các action:

- `/?action=login` - Đăng nhập
- `/?action=register` - Đăng ký
- `/?action=admin.dashboard` - Trang quản trị
- `/?action=admin.teachers` - Quản lý giáo viên
- `/?action=admin.students` - Quản lý học sinh
- `/?action=admin.classrooms` - Quản lý lớp học
- `/?action=admin.subjects` - Quản lý môn học
- `/?action=admin.schedules` - Phân công lịch dạy
- `/?action=teacher.dashboard` - Lịch dạy giáo viên
- `/?action=student.dashboard` - Thời khóa biểu học sinh

## Tài khoản mặc định

- Username: `admin`
- Password: `123`

## Lưu ý

- Tất cả các file đều nằm trong các folder tương ứng
- Chỉ có `index.php` ở root directory
- Các file cũ trong `admin/`, `teacher/`, `student/` có thể được giữ lại để tham khảo hoặc xóa nếu không cần

