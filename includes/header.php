<?php
/**
 * Shared page header + navigation bar.
 * Expects an optional $pageTitle variable set before inclusion.
 */
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/bootstrap.php';
}
$user = current_user();
$pageTitle = $pageTitle ?? 'Menu';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark jms-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(url('index.php')) ?>">
            <i class="bi bi-cup-straw"></i> FreshSip
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('index.php')) ?>">Menu</a></li>
                <?php if (has_role('Customer')): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/order-management/my_orders.php')) ?>">My Orders</a></li>
                <?php endif; ?>
                <?php if (has_role('Admin')): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/dashboard.php')) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/menu-management/index.php')) ?>">Menu Mgmt</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/inventory/index.php')) ?>">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/reporting/index.php')) ?>">Reports</a></li>
                <?php endif; ?>
                <?php if (has_role('Staff', 'Admin')): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/kitchen-display/index.php')) ?>">Kitchen</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= e(url('features/order-management/cart.php')) ?>">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="badge rounded-pill bg-warning text-dark position-absolute top-0 start-100 translate-middle" id="cartBadge">
                            <?= cart_count() ?>
                        </span>
                    </a>
                </li>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'Customer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e(url('features/loyalty/loyalty.php')) ?>">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?= (int) $user['loyalty_points'] ?> pts
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($user['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= e($user['role']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= e(url('features/auth/profile.php')) ?>"><i class="bi bi-person-gear"></i> My profile</a></li>
                            <?php if ($user['role'] === 'Customer'): ?>
                                <li><a class="dropdown-item" href="<?= e(url('features/order-management/my_orders.php')) ?>"><i class="bi bi-bag-check"></i> My orders</a></li>
                                <li><a class="dropdown-item" href="<?= e(url('features/loyalty/loyalty.php')) ?>"><i class="bi bi-star"></i> Loyalty &amp; rewards</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= e(url('features/auth/logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Log out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('features/auth/login.php')) ?>">Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning btn-sm ms-lg-2" href="<?= e(url('features/auth/register.php')) ?>">Sign up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php render_flash(); ?>
