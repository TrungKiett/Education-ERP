# Tóm tắt các Bảng Dữ liệu Mới

## 📊 Tổng quan

Bạn cần tạo **8 bảng mới** được chia thành 3 module. Tất cả đã được viết sẵn trong 3 file SQL.

## 🗂️ Module 1.2: Phân lớp & Thời khóa biểu

### 1. `courses` - Bảng Khóa học
**Mục đích**: Lưu thông tin các khóa học (tên, giá, thời gian, trạng thái)

**Các cột chính**:
- `id` - ID khóa học
- `name` - Tên khóa học
- `code` - Mã khóa học
- `price` - Giá khóa học (decimal)
- `start_date`, `end_date` - Thời gian khóa học
- `status` - Trạng thái (active/inactive/completed)

**File SQL**: `sql/module_1_2_schema.sql`

---

### 2. `enrollments` - Bảng Ghi danh
**Mục đích**: Lưu thông tin học sinh đã được gán vào lớp và khóa học

**Lưu ý**: Khác với `enrollment_applications` (hồ sơ tuyển sinh chờ phê duyệt)

**Các cột chính**:
- `id` - ID ghi danh
- `student_id` - ID học sinh (FK → students)
- `class_id` - ID lớp học (FK → classrooms)
- `course_id` - ID khóa học (FK → courses)
- `enrollment_date` - Ngày ghi danh
- `status` - Trạng thái (active/completed/cancelled)

**File SQL**: `sql/module_1_2_schema.sql`

---

## 💰 Module 2: Tự động hóa Tài chính (Học phí)

### 3. `discounts` - Bảng Chiết khấu/Giảm giá
**Mục đích**: Quản lý các mã giảm giá, chiết khấu

**Các cột chính**:
- `id` - ID chiết khấu
- `code` - Mã giảm giá (unique)
- `name` - Tên chương trình
- `type` - Loại (percentage/fixed)
- `value` - Giá trị giảm
- `min_amount` - Số tiền tối thiểu
- `usage_limit` - Giới hạn số lần dùng
- `start_date`, `end_date` - Thời gian hiệu lực

**File SQL**: `sql/module_2_schema.sql`

---

### 4. `invoices` - Bảng Hóa đơn
**Mục đích**: Lưu thông tin hóa đơn học phí

**Các cột chính**:
- `id` - ID hóa đơn
- `invoice_number` - Số hóa đơn (unique, format: INV-YYYYMMDD-XXXX)
- `enrollment_id` - ID ghi danh (FK → enrollments)
- `student_id` - ID học sinh (FK → students)
- `course_id` - ID khóa học (FK → courses)
- `subtotal` - Tổng tiền trước giảm giá
- `discount_id` - ID chiết khấu (FK → discounts)
- `discount_amount` - Số tiền được giảm
- `total_amount` - Tổng tiền sau giảm giá
- `status` - Trạng thái (unpaid/paid/cancelled/refunded)
- `due_date` - Hạn chót thanh toán
- `paid_date` - Ngày thanh toán

**Tự động tạo**: Có trigger tự động tạo hóa đơn khi Enrollment được tạo

**File SQL**: `sql/module_2_schema.sql`

---

### 5. `transactions` - Bảng Giao dịch Thanh toán
**Mục đích**: Lưu lịch sử giao dịch thanh toán

**Các cột chính**:
- `id` - ID giao dịch
- `invoice_id` - ID hóa đơn (FK → invoices)
- `transaction_code` - Mã giao dịch từ cổng thanh toán (unique)
- `amount` - Số tiền
- `payment_method` - Phương thức (VNPAY, BANK_TRANSFER, etc.)
- `payment_gateway` - Tên cổng thanh toán
- `status` - Trạng thái (pending/success/failed/cancelled)
- `gateway_response` - JSON response từ cổng
- `webhook_data` - JSON data từ webhook

**File SQL**: `sql/module_2_schema.sql`

---

## 🧑‍🏫 Module 3: Nền tảng Giảng viên (Giao bài tập & Chấm điểm)

### 6. `assignments` - Bảng Bài tập
**Mục đích**: Lưu thông tin bài tập do giáo viên tạo

**Các cột chính**:
- `id` - ID bài tập
- `title` - Tiêu đề bài tập
- `description` - Mô tả
- `class_id` - ID lớp học (FK → classrooms)
- `teacher_id` - ID giáo viên (FK → teachers)
- `subject_id` - ID môn học (FK → subjects)
- `max_score` - Điểm tối đa
- `due_date` - Hạn nộp bài
- `status` - Trạng thái (draft/published/closed)

**File SQL**: `sql/module_3_schema.sql`

---

### 7. `submissions` - Bảng Bài nộp
**Mục đích**: Lưu thông tin bài nộp của học sinh

**Các cột chính**:
- `id` - ID bài nộp
- `assignment_id` - ID bài tập (FK → assignments)
- `student_id` - ID học sinh (FK → students)
- `file_url` - URL file từ Supabase Storage
- `file_name` - Tên file
- `file_size` - Kích thước file
- `submitted_at` - Thời gian nộp
- `status` - Trạng thái (submitted/graded/late)
- `score` - Điểm số
- `feedback` - Phản hồi từ giáo viên
- `graded_at` - Thời gian chấm
- `graded_by` - ID giáo viên chấm (FK → teachers)

**Unique constraint**: Mỗi học sinh chỉ nộp 1 lần cho 1 bài tập

**File SQL**: `sql/module_3_schema.sql`

---

### 8. `academic_results` - Bảng Học bạ Điện tử
**Mục đích**: Lưu kết quả học tập của học sinh

**Các cột chính**:
- `id` - ID kết quả
- `student_id` - ID học sinh (FK → students)
- `class_id` - ID lớp học (FK → classrooms)
- `subject_id` - ID môn học (FK → subjects)
- `assignment_id` - ID bài tập (FK → assignments, nullable)
- `score` - Điểm số
- `max_score` - Điểm tối đa
- `grade` - Điểm chữ (A, B, C, D, F)
- `semester` - Học kỳ (HK1, HK2, HK3)
- `academic_year` - Năm học

**Tự động cập nhật**: Có trigger tự động cập nhật khi điểm được chấm

**File SQL**: `sql/module_3_schema.sql`

---

## 🔄 Triggers (Tự động)

### 1. `trg_create_invoice_on_enrollment`
**Kích hoạt**: Sau khi INSERT vào bảng `enrollments`
**Chức năng**: Tự động tạo hóa đơn với:
- Số hóa đơn: `INV-YYYYMMDD-XXXX`
- Hạn chót: 7 ngày sau ngày ghi danh
- Trạng thái: `unpaid`

**File SQL**: `sql/module_2_schema.sql`

---

### 2. `trg_update_academic_results_on_grading`
**Kích hoạt**: Sau khi UPDATE điểm trong bảng `submissions`
**Chức năng**: Tự động cập nhật học bạ với:
- Tính điểm chữ (A, B, C, D, F)
- Xác định học kỳ và năm học
- Insert hoặc update vào `academic_results`

**File SQL**: `sql/module_3_schema.sql`

---

## 📝 Cách tạo các bảng

### Cách 1: Chạy từng file SQL
```bash
# Vào thư mục dự án
cd C:\xampp\htdocs\edu

# Chạy từng file (theo thứ tự)
mysql -u root -p edu < sql/module_1_2_schema.sql
mysql -u root -p edu < sql/module_2_schema.sql
mysql -u root -p edu < sql/module_3_schema.sql
```

### Cách 2: Chạy trong phpMyAdmin
1. Mở phpMyAdmin
2. Chọn database `edu`
3. Vào tab "SQL"
4. Copy nội dung từng file SQL và chạy:
   - `sql/module_1_2_schema.sql`
   - `sql/module_2_schema.sql`
   - `sql/module_3_schema.sql`

### Cách 3: Tạo file tổng hợp
Tôi có thể tạo 1 file SQL duy nhất chứa tất cả các bảng nếu bạn muốn.

---

## ⚠️ Lưu ý quan trọng

1. **Thứ tự chạy SQL**: Phải chạy theo thứ tự vì có Foreign Keys:
   - Module 1.2 trước (courses, enrollments)
   - Module 2 sau (vì cần enrollments)
   - Module 3 cuối (vì cần classrooms, teachers, students, subjects)

2. **Foreign Keys**: Tất cả bảng đều có foreign keys đến các bảng hiện có:
   - `students`, `classrooms`, `teachers`, `subjects` (đã có sẵn)

3. **Unique Constraints**:
   - `enrollments`: Mỗi học sinh chỉ ghi danh 1 lần vào 1 lớp + 1 khóa học
   - `submissions`: Mỗi học sinh chỉ nộp 1 lần cho 1 bài tập
   - `invoices`: Số hóa đơn là unique
   - `transactions`: Mã giao dịch là unique

4. **CASCADE/SET NULL**:
   - Xóa học sinh → Xóa enrollments, invoices, submissions, academic_results
   - Xóa lớp → Xóa enrollments, assignments
   - Xóa giáo viên → Xóa assignments (CASCADE), submissions.graded_by = NULL

---

## ✅ Checklist

- [ ] Chạy `sql/module_1_2_schema.sql` → Tạo 2 bảng: `courses`, `enrollments`
- [ ] Chạy `sql/module_2_schema.sql` → Tạo 3 bảng: `discounts`, `invoices`, `transactions` + 1 trigger
- [ ] Chạy `sql/module_3_schema.sql` → Tạo 3 bảng: `assignments`, `submissions`, `academic_results` + 1 trigger
- [ ] Kiểm tra tất cả bảng đã được tạo thành công
- [ ] Kiểm tra triggers đã được tạo thành công

---

## 🔍 Kiểm tra sau khi tạo

Chạy query sau để xem tất cả bảng mới:

```sql
SHOW TABLES LIKE '%courses%';
SHOW TABLES LIKE '%enrollments%';
SHOW TABLES LIKE '%discounts%';
SHOW TABLES LIKE '%invoices%';
SHOW TABLES LIKE '%transactions%';
SHOW TABLES LIKE '%assignments%';
SHOW TABLES LIKE '%submissions%';
SHOW TABLES LIKE '%academic_results%';
```

Hoặc xem tất cả triggers:

```sql
SHOW TRIGGERS;
```

