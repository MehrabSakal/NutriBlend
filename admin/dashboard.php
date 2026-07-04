<?php
/**
 * Admin dashboard - overview of the whole operation.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('Admin');

$stats = [
    'products'  => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'customers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='Customer'")->fetchColumn(),
    'orders'    => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue'   => (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status<>'Cancelled'")->fetchColumn(),
];

$lowStock = $pdo->query(
    'SELECT ingredient_name, stock_level, alert_threshold, unit
     FROM inventory WHERE stock_level <= alert_threshold ORDER BY stock_level ASC'
)->fetchAll();

$recentOrders = $pdo->query(
    'SELECT o.id, o.order_date, o.total_amount, o.status, u.name AS customer
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.order_date DESC LIMIT 8'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once APP_ROOT . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-speedometer2"></i> Admin Dashboard</h3>
    <a class="btn btn-jms" href="<?= e(url('features/kitchen-display/index.php')) ?>">
        <i class="bi bi-fire"></i> Open Kitchen
    </a>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Products',  $stats['products'],  'cup-straw',  'success', url('features/menu-management/index.php')],
        ['Customers', $stats['customers'], 'people',     'primary', '#'],
        ['Orders',    $stats['orders'],    'receipt',    'info',    url('features/reporting/index.php')],
        ['Revenue',   money($stats['revenue']), 'cash-stack', 'warning', url('features/reporting/index.php')],
    ];
    foreach ($cards as [$label, $value, $icon, $color, $link]): ?>
        <div class="col-6 col-md-3">
            <a href="<?= e($link) ?>" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3 fs-2 text-<?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></div>
                        <div>
                            <div class="h4 mb-0 text-dark"><?= is_string($value) ? e($value) : (int) $value ?></div>
                            <div class="small text-muted"><?= e($label) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong><i class="bi bi-exclamation-triangle text-warning"></i> Low stock alerts</strong>
                <a class="small" href="<?= e(url('features/inventory/index.php')) ?>">Manage</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (!$lowStock): ?>
                    <li class="list-group-item text-muted">All ingredients are well stocked.</li>
                <?php endif; ?>
                <?php foreach ($lowStock as $l): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($l['ingredient_name']) ?></span>
                        <span class="badge bg-danger">
                            <?= rtrim(rtrim(number_format($l['stock_level'], 2), '0'), '.') ?> <?= e($l['unit']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong><i class="bi bi-clock-history"></i> Recent orders</strong></div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    <?php if (!$recentOrders): ?>
                        <tr><td colspan="6" class="text-muted text-center py-3">No orders yet.</td></tr>
                    <?php endif; ?>
                    <?php
                    foreach ($recentOrders as $o): ?>
                        <tr>
                            <td><a href="<?= e(url('features/billing/receipt.php?id=' . $o['id'])) ?>">#<?= (int) $o['id'] ?></a></td>
                            <td><?= e($o['customer']) ?></td>
                            <td class="small"><?= e($o['order_date']) ?></td>
                            <td><?= money($o['total_amount']) ?></td>
                            <td><span class="badge bg-<?= order_status_badge($o['status']) ?>"><?= e($o['status']) ?></span></td>
                            <td class="text-end">
                                <?php if ($o['status'] === 'Pending' || $o['status'] === 'Preparing'): ?>
                                    <form method="post" action="<?= e(url('features/kitchen-display/update_status.php')) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                        <input type="hidden" name="return" value="admin">
                                        <?php if ($o['status'] === 'Pending'): ?>
                                            <button name="status" value="Preparing" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-play-fill"></i> Prepare
                                            </button>
                                        <?php endif; ?>
                                        <button name="status" value="Served" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i> Done
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
