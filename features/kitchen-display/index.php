<?php
/**
 * Kitchen Display System (Epic 001 - US004/US005).
 * Bar staff see live incoming orders and advance them through
 * Pending -> Preparing -> Served. The board is rendered server-side on
 * first load and then auto-refreshes via AJAX so it is never left blank.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('Staff', 'Admin');

$stats = [
    'pending'   => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn(),
    'preparing' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Preparing'")->fetchColumn(),
    'served'    => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Served' AND DATE(order_date)=CURDATE()")->fetchColumn(),
];

$pageTitle = 'Kitchen Display';
require_once APP_ROOT . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-fire text-danger"></i> Kitchen Display</h3>
    <span class="text-muted small"><i class="bi bi-arrow-repeat"></i> Auto-refreshes every 5s</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="small text-muted text-uppercase">Pending</div>
            <div class="h2 mb-0 text-warning"><?= $stats['pending'] ?></div>
        </div></div>
    </div>
    <div class="col-4">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="small text-muted text-uppercase">Preparing</div>
            <div class="h2 mb-0 text-primary"><?= $stats['preparing'] ?></div>
        </div></div>
    </div>
    <div class="col-4">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="small text-muted text-uppercase">Served today</div>
            <div class="h2 mb-0 text-success"><?= $stats['served'] ?></div>
        </div></div>
    </div>
</div>

<div id="kitchenBoard" data-endpoint="<?= e(url('features/kitchen-display/fetch_orders.php')) ?>">
    <?php include __DIR__ . '/fetch_orders.php'; ?>
</div>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
