<?php
/**
 * Staff dashboard - quick counts + shortcut to the kitchen display.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('Staff', 'Admin');

$pending   = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();
$preparing = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Preparing'")->fetchColumn();
$servedToday = (int) $pdo->query(
    "SELECT COUNT(*) FROM orders WHERE status='Served' AND DATE(order_date)=CURDATE()"
)->fetchColumn();

$pageTitle = 'Staff Dashboard';
require_once APP_ROOT . '/includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-person-workspace"></i> Staff Dashboard</h3>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body text-center">
        <div class="display-5 text-warning"><?= $pending ?></div><div class="text-muted">Pending</div>
    </div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body text-center">
        <div class="display-5 text-primary"><?= $preparing ?></div><div class="text-muted">Preparing</div>
    </div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body text-center">
        <div class="display-5 text-success"><?= $servedToday ?></div><div class="text-muted">Served today</div>
    </div></div></div>
</div>
<a class="btn btn-jms btn-lg" href="<?= e(url('features/kitchen-display/index.php')) ?>">
    <i class="bi bi-fire"></i> Open Kitchen Display
</a>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
