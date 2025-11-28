<?php
$pageTitle = 'Trang Giáo viên';
require_once __DIR__ . '/../layouts/header.php';

$daysOfWeek = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-calendar-event"></i> Lịch dạy</p>
            <h2>Lịch dạy của <?php echo htmlspecialchars($teacher['full_name']); ?></h2>
            <p class="mb-0 text-muted">Cập nhật ngày <?php echo date('d/m/Y'); ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                <i class="bi bi-calendar-day"></i> Hôm nay: <?php echo date('d/m', strtotime($today)); ?>
            </span>
            <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                <i class="bi bi-calendar-week"></i> Tuần: <?php echo date('d/m', strtotime($weekStart)); ?> - <?php echo date('d/m', strtotime($weekEnd)); ?>
            </span>
        </div>
    </div>
    
    <!-- View Tabs -->
    <ul class="nav nav-tabs custom-tabs mb-4 flex-wrap">
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'today' ? 'active' : ''; ?>" href="?action=teacher.dashboard&view=today&date=<?php echo $today; ?>">
                <i class="bi bi-calendar-day"></i> Hôm nay
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'week' ? 'active' : ''; ?>" href="?action=teacher.dashboard&view=week&date=<?php echo $currentDate; ?>">
                <i class="bi bi-calendar-week"></i> Tuần này
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'month' ? 'active' : ''; ?>" href="?action=teacher.dashboard&view=month&date=<?php echo $currentDate; ?>">
                <i class="bi bi-calendar-month"></i> Tháng này
            </a>
        </li>
    </ul>
    
    <!-- Date Navigation -->
    <?php if ($view !== 'today'): ?>
    <div class="glass-card p-3 mb-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="action" value="teacher.dashboard">
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <label class="text-muted small mb-0">Chọn ngày</label>
            <input type="date" class="form-control" name="date" value="<?php echo $currentDate; ?>" onchange="this.form.submit()">
        </form>
        <div class="text-muted fw-semibold">
            <?php if ($view === 'week'): ?>
            <i class="bi bi-arrow-left-right"></i> <?php echo date('d/m/Y', strtotime($weekStart)); ?> - <?php echo date('d/m/Y', strtotime($weekEnd)); ?>
            <?php elseif ($view === 'month'): ?>
            <i class="bi bi-calendar3"></i> Tháng <?php echo date('m/Y', strtotime($currentDate)); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Schedules Display -->
    <div class="card glass-card">
        <div class="card-body">
            <?php if (empty($schedules)): ?>
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="bi bi-info-circle fs-4"></i>
                <div>
                    <strong>Chưa có lịch dạy</strong>
                    <p class="mb-0 text-muted">Vui lòng chọn khoảng thời gian khác hoặc liên hệ phòng đào tạo.</p>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($view === 'today'): ?>
            <!-- Today View -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="mb-0"><?php echo date('d/m/Y', strtotime($currentDate)); ?> - <?php echo $daysOfWeek[date('w', strtotime($currentDate))]; ?></h5>
                <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo count($schedules); ?> tiết trong ngày</span>
            </div>
            <div class="table-responsive table-modern">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tiết</th>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><strong>Tiết <?php echo $schedule['period']; ?></strong></td>
                            <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['subject_name']); ?> (<?php echo htmlspecialchars($schedule['subject_code']); ?>)</td>
                            <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($view === 'week'): ?>
            <!-- Week View -->
            <div class="table-responsive table-modern">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Thứ</th>
                            <th>Ngày</th>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <th class="text-center">Tiết <?php echo $i; ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $weekSchedule = [];
                        foreach ($schedules as $schedule) {
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            if ($scheduleDate) {
                                $day = date('N', strtotime($scheduleDate)); // 1=Monday, 2=Tuesday, ..., 7=Sunday
                                $period = $schedule['period'];
                                if (!isset($weekSchedule[$day])) {
                                    $weekSchedule[$day] = [];
                                }
                                $weekSchedule[$day][$period] = $schedule;
                            }
                        }
                        
                        // Display Monday to Friday (1-5)
                        for ($day = 1; $day <= 5; $day++):
                            $date = date('Y-m-d', strtotime($weekStart . ' +' . ($day - 1) . ' days'));
                        ?>
                        <tr>
                            <td><strong><?php echo $daysOfWeek[$day]; ?></strong></td>
                            <td><?php echo date('d/m', strtotime($date)); ?></td>
                            <?php for ($period = 1; $period <= 10; $period++): ?>
                            <td class="text-center">
                                <?php if (isset($weekSchedule[$day][$period])): 
                                    $sched = $weekSchedule[$day][$period];
                                ?>
                                <div class="small fw-semibold text-primary">
                                    <?php echo htmlspecialchars($sched['class_name']); ?><br>
                                    <span class="text-dark"><?php echo htmlspecialchars($sched['subject_name']); ?></span><br>
                                    <?php if (!empty($sched['room'])): ?>
                                    <small class="text-muted"><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($sched['room']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($view === 'month'): ?>
            <!-- Month View -->
            <div class="table-responsive table-modern">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Thứ</th>
                            <th>Ngày</th>
                            <th>Tiết</th>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Phòng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): 
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            $dayOfWeekNum = $scheduleDate ? date('w', strtotime($scheduleDate)) : 0; // 0=Sunday, 1=Monday, ...
                            $dayOfWeek = $daysOfWeek[$dayOfWeekNum] ?? 'N/A';
                        ?>
                        <tr>
                            <td><?php echo $dayOfWeek; ?></td>
                            <td><?php echo $scheduleDate ? date('d/m/Y', strtotime($scheduleDate)) : '-'; ?></td>
                            <td><strong>Tiết <?php echo $schedule['period']; ?></strong></td>
                            <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['subject_name']); ?> (<?php echo htmlspecialchars($schedule['subject_code']); ?>)</td>
                            <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

