<?php
$pageTitle = 'Phân công Lịch dạy';
require_once __DIR__ . '/../layouts/header.php';

$daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
$monthNames = ['', 'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
               'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];

// Calculate calendar dates
$firstDayOfMonth = strtotime("$year-$month-01");
$firstDayWeekday = date('w', $firstDayOfMonth); // 0=Sunday, 1=Monday, etc.
$daysInMonth = date('t', $firstDayOfMonth);
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Message is passed from controller
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-calendar-check"></i> Quản lý lịch</p>
            <h2><?php echo $monthNames[$month]; ?> <?php echo $year; ?></h2>
            <p class="mb-0 text-muted">Xem và quản lý lịch dạy theo tháng</p>
        </div>
        <button class="btn btn-lg btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="bi bi-plus-circle"></i> Thêm lịch dạy
        </button>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Month Navigation -->
    <div class="card glass-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <a href="?action=admin.schedules&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-chevron-left"></i> Tháng trước
                    </a>
                    <a href="?action=admin.schedules&month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-calendar-today"></i> Hôm nay
                    </a>
                    <a href="?action=admin.schedules&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" 
                       class="btn btn-outline-primary">
                        Tháng sau <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="text-muted">
                    <i class="bi bi-info-circle"></i> Click vào ngày để xem chi tiết lịch dạy
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar -->
    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="calendar-container">
                <!-- Calendar Header -->
                <div class="calendar-header">
                    <?php foreach ($daysOfWeek as $day): ?>
                    <div class="calendar-day-header"><?php echo $day; ?></div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <?php
                    // Empty cells for days before month starts
                    for ($i = 0; $i < $firstDayWeekday; $i++):
                    ?>
                    <div class="calendar-day empty"></div>
                    <?php endfor; ?>
                    
                    <?php
                    // Days of the month
                    for ($day = 1; $day <= $daysInMonth; $day++):
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $dateFormatted = sprintf('%02d/%02d/%04d', $day, $month, $year);
                        $daySchedules = $schedules[$dateStr] ?? [];
                        $isToday = ($year == date('Y') && $month == date('n') && $day == date('j'));
                        $dayOfWeek = date('w', strtotime($dateStr));
                        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                    ?>
                    <div class="calendar-day <?php echo $isToday ? 'today' : ''; ?> <?php echo $isWeekend ? 'weekend' : ''; ?>" 
                         data-date="<?php echo $dateStr; ?>"
                         data-bs-toggle="modal" 
                         data-bs-target="#dayScheduleModal"
                         onclick="loadDaySchedules('<?php echo $dateStr; ?>', '<?php echo $dateFormatted; ?>')">
                        <div class="calendar-day-number"><?php echo $day; ?></div>
                        <?php if (!empty($daySchedules)): ?>
                        <div class="calendar-day-schedules">
                            <?php 
                            $scheduleCount = count($daySchedules);
                            $displayCount = min($scheduleCount, 3);
                            for ($i = 0; $i < $displayCount; $i++):
                                $sched = $daySchedules[$i];
                            ?>
                            <div class="schedule-item" style="background: <?php 
                                $colors = ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                                echo $colors[$sched['period'] % count($colors)];
                            ?>;">
                                <small class="text-white fw-semibold">T<?php echo $sched['period']; ?></small>
                                <small class="text-white d-block"><?php echo htmlspecialchars($sched['class_name']); ?></small>
                            </div>
                            <?php endfor; ?>
                            <?php if ($scheduleCount > 3): ?>
                            <div class="schedule-more text-center">
                                <small class="text-muted">+<?php echo $scheduleCount - 3; ?> nữa</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Schedule Detail Modal -->
<div class="modal fade" id="dayScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayScheduleModalTitle">Lịch dạy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayScheduleModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="month" value="<?php echo $month; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm lịch dạy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp học <span class="text-danger">*</span></label>
                            <select class="form-select" name="class_id" id="form_class_id" required>
                                <option value="">Chọn lớp...</option>
                                <?php foreach ($classrooms as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giáo viên <span class="text-danger">*</span></label>
                            <select class="form-select" name="teacher_id" id="form_teacher_id" required>
                                <option value="">Chọn giáo viên...</option>
                                <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" id="form_subject_id" required>
                                <option value="">Chọn môn học...</option>
                                <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                    <?php if (!empty($subject['code'])): ?>
                                        (<?php echo htmlspecialchars($subject['code']); ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày dạy <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="schedule_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiết học <span class="text-danger">*</span></label>
                            <select class="form-select" name="period" required>
                                <option value="">Chọn tiết...</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>">Tiết <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phòng học</label>
                            <input type="text" class="form-control" name="room" placeholder="VD: P101">
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Hệ thống sẽ kiểm tra trùng lịch giáo viên, lớp và phòng học trước khi lưu.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="month" value="<?php echo $month; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Bạn có chắc chắn muốn xóa lịch dạy <strong id="delete_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.calendar-container {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: rgba(37, 99, 235, 0.08);
    border-bottom: 2px solid var(--border-color);
}

.calendar-day-header {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--border-color);
}

.calendar-day {
    min-height: 120px;
    background: white;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}

.calendar-day:hover {
    background: rgba(37, 99, 235, 0.05);
    transform: scale(1.02);
    z-index: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.calendar-day.empty {
    background: #f8f9fa;
    cursor: default;
}

.calendar-day.empty:hover {
    transform: none;
    box-shadow: none;
}

.calendar-day.weekend {
    background: #fef3f2;
}

.calendar-day.today {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.08));
    border: 2px solid #2563eb;
}

.calendar-day.today .calendar-day-number {
    background: #2563eb;
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.calendar-day-number {
    font-weight: 600;
    font-size: 0.95rem;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.calendar-day-schedules {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    overflow: hidden;
}

.schedule-item {
    padding: 0.25rem 0.35rem;
    border-radius: 0.35rem;
    font-size: 0.7rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.schedule-more {
    padding: 0.15rem;
    font-size: 0.65rem;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 0.35rem;
    }
    
    .calendar-day-header {
        padding: 0.5rem 0.25rem;
        font-size: 0.75rem;
    }
    
    .schedule-item {
        font-size: 0.6rem;
        padding: 0.2rem 0.25rem;
    }
}
</style>

<script>
const schedulesData = <?php echo json_encode($schedules); ?>;

function loadDaySchedules(dateStr, dateFormatted) {
    document.getElementById('dayScheduleModalTitle').textContent = 'Lịch dạy ngày ' + dateFormatted;
    const modalBody = document.getElementById('dayScheduleModalBody');
    
    const daySchedules = schedulesData[dateStr] || [];
    
    if (daySchedules.length === 0) {
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="text-muted mt-3">Chưa có lịch dạy nào trong ngày này</p>
                <button class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#addScheduleModal" onclick="document.querySelector('input[name=schedule_date]').value='${dateStr}'">
                    <i class="bi bi-plus-circle"></i> Thêm lịch dạy
                </button>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover align-middle">';
    html += '<thead><tr><th>Tiết</th><th>Lớp</th><th>Môn học</th><th>Giáo viên</th><th>Phòng</th><th>Thao tác</th></tr></thead>';
    html += '<tbody>';
    
    daySchedules.forEach(schedule => {
        html += `
            <tr>
                <td><span class="badge bg-primary">Tiết ${schedule.period}</span></td>
                <td>${schedule.class_name}</td>
                <td>${schedule.subject_name}</td>
                <td>${schedule.teacher_name}</td>
                <td>${schedule.room || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteSchedule(${schedule.id}, '${dateFormatted} - Tiết ${schedule.period}')" title="Xóa">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    html += `
        <div class="mt-3 text-center">
            <button class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#addScheduleModal" onclick="document.querySelector('input[name=schedule_date]').value='${dateStr}'">
                <i class="bi bi-plus-circle"></i> Thêm lịch dạy khác
            </button>
        </div>
    `;
    
    modalBody.innerHTML = html;
}

function deleteSchedule(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteScheduleModal')).show();
}
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
