<?php
$pageTitle = 'Hóa đơn Học phí';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid py-4">
    <div class="page-heading">
        <div>
            <p class="text-uppercase text-muted mb-1 fw-semibold"><i class="bi bi-receipt"></i> Học phí</p>
            <h2>Hóa đơn Học phí</h2>
            <p class="mb-0 text-muted">Xem và thanh toán hóa đơn học phí</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card text-white bg-gradient-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Tổng hóa đơn</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['total']; ?></h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-file-earmark-text fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card text-white bg-gradient-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Chưa thanh toán</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['unpaid']; ?></h2>
                            <small class="opacity-75"><?php echo number_format($stats['total_amount_unpaid'], 0, ',', '.'); ?> đ</small>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card text-white bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Đã thanh toán</p>
                            <h2 class="fw-bold mb-0"><?php echo $stats['paid']; ?></h2>
                            <small class="opacity-75"><?php echo number_format($stats['total_amount_paid'], 0, ',', '.'); ?> đ</small>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card text-white bg-gradient-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small mb-1">Tổng tiền</p>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_amount_unpaid'] + $stats['total_amount_paid'], 0, ',', '.'); ?> đ</h2>
                        </div>
                        <div class="stat-icon text-white">
                            <i class="bi bi-currency-exchange fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <ul class="nav nav-tabs custom-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" 
               href="?action=student.invoices&status=all">
                <i class="bi bi-list-ul"></i> Tất cả
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'unpaid' ? 'active' : ''; ?>" 
               href="?action=student.invoices&status=unpaid">
                <i class="bi bi-clock-history"></i> Chưa thanh toán
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $statusFilter === 'paid' ? 'active' : ''; ?>" 
               href="?action=student.invoices&status=paid">
                <i class="bi bi-check-circle"></i> Đã thanh toán
            </a>
        </li>
    </ul>
    
    <!-- Invoices Table -->
    <div class="card glass-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern table-hover">
                    <thead>
                        <tr>
                            <th>Số hóa đơn</th>
                            <th>Khóa học</th>
                            <th>Tổng tiền</th>
                            <th>Giảm giá</th>
                            <th>Thành tiền</th>
                            <th>Hạn chót</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Không có hóa đơn nào</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                <br><small class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($invoice['course_name'] ?? 'N/A'); ?></td>
                            <td><?php echo number_format($invoice['subtotal'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <?php if ($invoice['discount_amount'] > 0): ?>
                                    <span class="text-success">-<?php echo number_format($invoice['discount_amount'], 0, ',', '.'); ?> đ</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-primary">
                                    <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> đ
                                </strong>
                            </td>
                            <td>
                                <?php 
                                $dueDate = strtotime($invoice['due_date']);
                                $today = time();
                                $daysLeft = floor(($dueDate - $today) / (60 * 60 * 24));
                                ?>
                                <?php echo date('d/m/Y', $dueDate); ?>
                                <?php if ($invoice['status'] === 'unpaid'): ?>
                                    <?php if ($daysLeft < 0): ?>
                                        <br><small class="text-danger">Quá hạn <?php echo abs($daysLeft); ?> ngày</small>
                                    <?php elseif ($daysLeft <= 3): ?>
                                        <br><small class="text-warning">Còn <?php echo $daysLeft; ?> ngày</small>
                                    <?php else: ?>
                                        <br><small class="text-muted">Còn <?php echo $daysLeft; ?> ngày</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'unpaid' => 'warning',
                                    'paid' => 'success',
                                    'cancelled' => 'danger',
                                    'refunded' => 'info'
                                ];
                                $statusText = [
                                    'unpaid' => 'Chưa thanh toán',
                                    'paid' => 'Đã thanh toán',
                                    'cancelled' => 'Đã hủy',
                                    'refunded' => 'Đã hoàn tiền'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $statusClass[$invoice['status']] ?? 'secondary'; ?>">
                                    <?php echo $statusText[$invoice['status']] ?? $invoice['status']; ?>
                                </span>
                                <?php if ($invoice['status'] === 'paid' && $invoice['paid_date']): ?>
                                    <br><small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($invoice['paid_date'])); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=student.invoiceDetail&id=<?php echo $invoice['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Chi tiết
                                </a>
                                <?php if ($invoice['status'] === 'unpaid'): ?>
                                <button class="btn btn-sm btn-success" 
                                        onclick="payInvoice(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['invoice_number']); ?>')">
                                    <i class="bi bi-credit-card"></i> Thanh toán
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function payInvoice(invoiceId, invoiceNumber) {
    if (confirm('Bạn có chắc muốn thanh toán hóa đơn ' + invoiceNumber + '?')) {
        // Redirect to payment page (sẽ triển khai sau)
        window.location.href = '?action=payment.process&invoice_id=' + invoiceId;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

