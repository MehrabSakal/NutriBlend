<?php
/**
 * Advance an order's status (Pending -> Preparing -> Served).
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('Staff', 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('features/kitchen-display/index.php');
}
csrf_check();

$id     = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($status === 'Cancelled') {
    [$ok, $msg] = cancel_order($pdo, $id);
    flash($ok ? 'success' : 'error', $msg);
} elseif (in_array($status, ['Preparing', 'Served'], true)) {
    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    flash('success', "Order #$id marked as $status.");
}

$allowedReturns = [
    'admin'   => 'admin/dashboard.php',
    'kitchen' => 'features/kitchen-display/index.php',
];
$return = $allowedReturns[$_POST['return'] ?? 'kitchen'] ?? 'features/kitchen-display/index.php';
redirect($return);
