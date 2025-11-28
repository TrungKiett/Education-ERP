# Danh sách Roles trong Hệ thống

## 📋 Tổng quan

Hệ thống hiện tại có **3 roles** chính:

1. **`admin`** - Quản trị viên
2. **`teacher`** - Giáo viên
3. **`student`** - Học sinh

---

## 👤 Chi tiết từng Role

### 1. **`admin`** - Quản trị viên

**Quyền hạn**:
- ✅ Quản lý toàn bộ hệ thống
- ✅ Quản lý giáo viên (thêm, sửa, xóa)
- ✅ Quản lý học sinh (thêm, sửa, xóa)
- ✅ Quản lý lớp học
- ✅ Quản lý môn học
- ✅ Phân công lịch dạy
- ✅ Phân lớp học sinh vào khóa học
- ✅ Xem xét và phê duyệt hồ sơ tuyển sinh
- ✅ Xem dashboard với thống kê tổng quan

**Controllers**:
- `AdminController` - Tất cả các chức năng quản trị

**Routes**:
- `/?action=admin.dashboard` - Dashboard quản trị
- `/?action=admin.teachers` - Quản lý giáo viên
- `/?action=admin.students` - Quản lý học sinh
- `/?action=admin.classrooms` - Quản lý lớp học
- `/?action=admin.subjects` - Quản lý môn học
- `/?action=admin.schedules` - Phân công lịch dạy
- `/?action=admin.assignClass` - Phân lớp học sinh
- `/?action=enrollment.adminEnrollments` - Quản lý hồ sơ tuyển sinh

**Profile**:
- Không có bảng profile riêng
- Lưu trực tiếp trong bảng `users` với `role = 'admin'`
- `profile_id` = NULL

---

### 2. **`teacher`** - Giáo viên

**Quyền hạn**:
- ✅ Xem thời khóa biểu của mình
- ✅ Tạo và quản lý bài tập cho lớp học
- ✅ Chấm điểm bài nộp của học sinh
- ✅ Xem danh sách học sinh trong lớp
- ✅ Xem lịch dạy theo ngày/tuần/tháng

**Controllers**:
- `TeacherController` - Các chức năng của giáo viên

**Routes**:
- `/?action=teacher.dashboard` - Dashboard giáo viên
- `/?action=teacher.assignments` - Quản lý bài tập (sẽ triển khai)
- `/?action=teacher.gradeSubmissions` - Chấm điểm (sẽ triển khai)

**Profile**:
- Có bảng `teachers` riêng
- Liên kết qua `users.profile_id = teachers.id`
- `users.role = 'teacher'`

**Thông tin lưu trong bảng `teachers`**:
- `id`, `code`, `full_name`, `email`, `phone`

---

### 3. **`student`** - Học sinh

**Quyền hạn**:
- ✅ Xem thời khóa biểu của mình
- ✅ Xem danh sách bài tập
- ✅ Nộp bài tập (upload file)
- ✅ Xem điểm số và phản hồi
- ✅ Xem hóa đơn học phí
- ✅ Thanh toán học phí trực tuyến
- ✅ Xem học bạ điện tử

**Controllers**:
- `StudentController` - Các chức năng của học sinh

**Routes**:
- `/?action=student.dashboard` - Dashboard học sinh
- `/?action=student.schedule` - Xem thời khóa biểu
- `/?action=student.assignments` - Xem và nộp bài tập (sẽ triển khai)
- `/?action=student.invoices` - Xem hóa đơn (sẽ triển khai)

**Profile**:
- Có bảng `students` riêng
- Liên kết qua `users.profile_id = students.id`
- `users.role = 'student'`

**Thông tin lưu trong bảng `students`**:
- `id`, `code`, `full_name`, `email`, `phone`, `class_id`

---

## 🔐 Cơ chế Authentication & Authorization

### Bảng `users`

Cấu trúc bảng `users`:
```sql
- id (PK)
- username (unique)
- password_hash
- role (enum: 'admin', 'teacher', 'student')
- profile_id (FK → teachers.id hoặc students.id, NULL cho admin)
- email
- created_at
- updated_at
```

### Session Management

Khi đăng nhập, hệ thống lưu vào `$_SESSION`:
- `user_id` - ID từ bảng users
- `username` - Tên đăng nhập
- `role` - Role của user

### Helper Functions (trong `config/session.php`)

```php
// Kiểm tra đăng nhập
isLoggedIn()

// Kiểm tra role cụ thể
hasRole($role)
isAdmin()
isTeacher()
isStudent()

// Yêu cầu đăng nhập/role
requireLogin()
requireRole($role)
requireAdmin()
requireTeacher()
requireStudent()

// Lấy thông tin hiện tại
getCurrentUserId()
getCurrentUsername()
getCurrentRole()
```

### BaseController Methods

```php
// Trong controllers
$this->requireLogin()
$this->requireRole($role)
$this->requireAdmin()
$this->requireTeacher()
$this->requireStudent()
```

---

## 📊 Sơ đồ Quan hệ

```
users
├── role = 'admin'
│   └── profile_id = NULL
│
├── role = 'teacher'
│   └── profile_id → teachers.id
│
└── role = 'student'
    └── profile_id → students.id
```

---

## 🆕 Role mới (nếu cần thêm)

Nếu muốn thêm role mới (ví dụ: `parent` - Phụ huynh), cần:

1. **Cập nhật bảng `users`**:
   - Thêm giá trị mới vào enum `role` (nếu dùng enum)
   - Hoặc chỉ cần thêm giá trị mới vào cột `role` (nếu dùng varchar)

2. **Cập nhật `config/session.php`**:
   ```php
   function isParent() {
       return hasRole('parent');
   }
   
   function requireParent() {
       requireRole('parent');
   }
   ```

3. **Cập nhật `controllers/BaseController.php`**:
   ```php
   protected function requireParent() {
       $this->requireRole('parent');
   }
   ```

4. **Cập nhật routing trong `index.php`**:
   ```php
   elseif ($role === 'parent') {
       $action = 'parent.dashboard';
   }
   ```

5. **Tạo Controller mới**:
   - `controllers/ParentController.php`

6. **Tạo Views**:
   - `views/parent/dashboard.php`
   - Các view khác cho parent

---

## ✅ Checklist Roles hiện tại

- [x] `admin` - Đã triển khai đầy đủ
- [x] `teacher` - Đã triển khai cơ bản, cần thêm assignments & grading
- [x] `student` - Đã triển khai cơ bản, cần thêm assignments & invoices

---

## 🔍 Kiểm tra Role trong Database

Để xem tất cả roles hiện có:

```sql
SELECT DISTINCT role FROM users;
```

Để xem số lượng user theo role:

```sql
SELECT role, COUNT(*) as count 
FROM users 
GROUP BY role;
```

