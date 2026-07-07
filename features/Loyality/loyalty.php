<?php
/**
 * Loyalty programme (Epic 002 - US007/US008).
 * Shows the customer's points balance, how redemption works and their
 * order history with points earned / redeemed.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_login();

$user = current_user();

// Live balance.
$stmt = $pdo->prepare('SELECT loyalty_points FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$points = (int) $stmt->fetchColumn();
$_SESSION['user']['loyalty_points'] = $points;

// Order history.
$orders = $pdo->prepare(
    'SELECT id, order_date, total_amount, points_earned, points_redeemed, status
     FROM orders WHERE user_id = ? ORDER BY order_date DESC'
);
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();

$totalEarned = array_sum(array_column($orders, 'points_earned'));
$totalSpent  = array_sum(array_column($orders, 'points_redeemed'));

$pageTitle = 'Loyalty & Rewards';
require_once APP_ROOT . '/includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-star-fill text-warning"></i> Loyalty &amp; Rewards</h3>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-white" style="background:var(--jms-green)">
            <div class="card-body">
                <div class="small text-uppercase">Current balance</div>
                <div class="display-6 fw-bold"><?= $points ?> <small class="fs-6">pts</small></div>
                <div class="small">Worth <?= money($points / POINTS_PER_DOLLAR_REDEEM) ?> off your next order</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body">
            <div class="small text-muted text-uppercase">Total earned</div>
            <div class="display-6 fw-bold text-success"><?= (int) $totalEarned ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body">
            <div class="small text-muted text-uppercase">Total redeemed</div>
            <div class="display-6 fw-bold text-danger"><?= (int) $totalSpent ?></div>
        </div></div>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Earn <strong>1 point</strong> for every <strong><?= money(1) ?></strong> spent.
    Redeem <strong><?= POINTS_PER_DOLLAR_REDEEM ?> points</strong> for <strong><?= money(1) ?></strong> off,
    right from the checkout page.
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Your order history</strong></div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Order</th><th>Date</th><th>Total</th><th>Earned</th><th>Redeemed</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No orders yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['id'] ?></td>
                    <td class="small"><?= e($o['order_date']) ?></td>
                    <td><?= money($o['total_amount']) ?></td>
                    <td class="text-success">+<?= (int) $o['points_earned'] ?></td>
                    <td class="text-danger">-<?= (int) $o['points_redeemed'] ?></td>
                    <td><span class="badge bg-secondary"><?= e($o['status']) ?></span></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="<?= e(url('features/billing/receipt.php?id=' . $o['id'])) ?>">Receipt</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
