<?php
$pageTitle = 'Thời khóa biểu Học sinh';
require_once __DIR__ . '/../layouts/header.php';

if (isset($error)): ?>
<div class="container-fluid mt-4">
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
</div>
<?php else:
$daysOfWeek = ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><i class="bi bi-calendar-week"></i> Thời khóa biểu của: <?php echo htmlspecialchars($student['full_name']); ?></h2>
            <p class="text-muted mb-0">Lớp: <?php echo htmlspecialchars($classroom['name']); ?></p>
        </div>
        <div>
            <a href="?action=student.invoices" class="btn btn-primary">
                <i class="bi bi-receipt"></i> Hóa đơn Học phí
            </a>
        </div>
    </div>
    <hr>
    
    <!-- Timetable -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($schedules)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Chưa có lịch học trong tuần này.
            </div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 80px;">Tiết</th>
                            <?php
                            // Display Monday to Friday
                            for ($day = 1; $day <= 5; $day++):
                            ?>
                            <th class="text-center">
                                <div><?php echo $daysOfWeek[$day]; ?></div>
                            </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Organize schedules by day and period
                        $weekSchedule = [];
                        foreach ($schedules as $schedule) {
                            $scheduleDate = $schedule['schedule_date'] ?? '';
                            if ($scheduleDate) {
                                $day = date('N', strtotime($scheduleDate)); // weekday: 1=Monday, 2=Tuesday, ...
                                $period = $schedule['period'];
                                if (!isset($weekSchedule[$day])) {
                                    $weekSchedule[$day] = [];
                                }
                                $weekSchedule[$day][$period] = $schedule;
                            }
                        }
                        
                        // Display periods 1-10
                        for ($period = 1; $period <= 10; $period++):
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $period; ?></td>
                            <?php
                            // Display Monday to Friday (1-5)
                            for ($day = 1; $day <= 5; $day++):
                            ?>
                            <td class="align-middle">
                                <?php if (isset($weekSchedule[$day][$period])): 
                                    $sched = $weekSchedule[$day][$period];
                                ?>
                                <div class="p-2 bg-light rounded">
                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($sched['subject_name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($sched['subject_code']); ?></div>
                                    <div class="small mt-1">
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($sched['teacher_name']); ?>
                                    </div>
                                    <?php if (!empty($sched['room'])): ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-door-open"></i> <?php echo htmlspecialchars($sched['room']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted">-</div>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

