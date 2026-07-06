<?php
/**
 * Returns the kitchen board HTML fragment (active orders).
 * Rendered server-side on first load by index.php and then refreshed
 * by AJAX every few seconds.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('Staff', 'Admin');

$orders = $pdo->query(
    "SELECT o.id, o.order_date, o.status, o.total_amount, u.name AS customer
     FROM orders o JOIN users u ON u.id = o.user_id
     WHERE o.status IN ('Pending','Preparing')
     ORDER BY o.order_date ASC"
)->fetchAll();

$itemStmt = $pdo->prepare('SELECT product_name, quantity, customizations FROM order_items WHERE order_id = ?');

if (!$orders) {
    echo '<div class="card shadow-sm"><div class="card-body text-center text-muted py-5">'
       . '<i class="bi bi-check2-circle display-4 text-success"></i>'
       . '<p class="mt-2 mb-0">No active orders. All caught up!</p></div></div>';
    return;
}
?>
<div class="row g-3">
<?php foreach ($orders as $o):
    $itemStmt->execute([$o['id']]);
    $items      = $itemStmt->fetchAll();
    $waitMins   = max(0, (int) floor((time() - strtotime($o['order_date'])) / 60));
    $urgent     = $waitMins >= 10;
?>
    <div class="col-md-4 col-lg-3">
        <div class="card ticket status-<?= e($o['status']) ?><?= $urgent ? ' wait-urgent' : '' ?> shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <strong>#<?= (int) $o['id'] ?></strong>
                    <span class="badge bg-<?= $o['status'] === 'Preparing' ? 'primary' : 'warning text-dark' ?>">
                        <?= e($o['status']) ?>
                    </span>
                </div>
                <div class="small text-muted mb-1">
                    <i class="bi bi-person"></i> <?= e($o['customer']) ?> &middot;
                    <?= e(date('H:i', strtotime($o['order_date']))) ?>
                </div>
                <div class="small mb-2">
                    <span class="badge wait-pill bg-<?= $urgent ? 'danger' : 'light text-dark border' ?>">
                        <i class="bi bi-clock"></i> <?= $waitMins ?>m waiting
                    </span>
                    <span class="text-muted"><?= money($o['total_amount']) ?></span>
                </div>
                <ul class="list-unstyled small mb-3">
                    <?php foreach ($items as $it): ?>
                        <li class="mb-1">
                            <span class="badge bg-dark"><?= (int) $it['quantity'] ?></span>
                            <?= e($it['product_name']) ?>
                            <?php if ($it['customizations']): ?>
                                <div class="ms-4">
                                    <?php foreach (explode(', ', $it['customizations']) as $c): ?>
                                        <span class="customization-tag"><?= e($c) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-grid gap-1">
                    <form method="post" action="<?= e(url('features/kitchen-display/update_status.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                        <input type="hidden" name="return" value="kitchen">
                        <?php if ($o['status'] === 'Pending'): ?>
                            <button name="status" value="Preparing" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-play-fill"></i> Start preparing
                            </button>
                        <?php else: ?>
                            <button name="status" value="Served" class="btn btn-sm btn-success w-100">
                                <i class="bi bi-check-lg"></i> Mark served
                            </button>
                        <?php endif; ?>
                    </form>
                    <form method="post" action="<?= e(url('features/kitchen-display/update_status.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                        <input type="hidden" name="return" value="kitchen">
                        <button name="status" value="Cancelled" class="btn btn-sm btn-outline-danger w-100"
                                data-confirm="Cancel order #<?= (int) $o['id'] ?>? Stock and points will be restored.">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
