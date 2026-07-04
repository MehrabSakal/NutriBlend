<?php
/**
 * One-time setup / installer helper.
 *
 * 1. Confirms the app can connect to the jms_db database.
 * 2. Creates the three demo accounts with correctly hashed passwords.
 *
 * Open this page in your browser ONCE after importing database/schema.sql:
 *     http://localhost/ISD_Project/setup.php
 *
 * You can safely delete this file afterwards.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$demo = [
    ['System Admin', 'admin@freshsip.test', 'Admin',    0],
    ['Bar Staff',    'staff@freshsip.test', 'Staff',    0],
    ['Alice Green',  'alice@freshsip.test', 'Customer', 120],
];
$defaultPassword = 'password123';

$results = [];
foreach ($demo as [$name, $email, $role, $points]) {
    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // Always reset demo passwords so login works even after a bad import.
        $pdo->prepare(
            'UPDATE users SET name = ?, password = ?, role = ?, loyalty_points = ? WHERE email = ?'
        )->execute([$name, $hash, $role, $points, $email]);
        $results[] = "Reset $role account: $email (password set to password123)";
        continue;
    }

    $pdo->prepare('INSERT INTO users (name, email, password, role, loyalty_points) VALUES (?,?,?,?,?)')
        ->execute([$name, $email, $hash, $role, $points]);
    $results[] = "Created $role account: $email";
}

$counts = [
    'Products'    => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Ingredients' => (int) $pdo->query('SELECT COUNT(*) FROM inventory')->fetchColumn(),
    'Users'       => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JMS Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:640px">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-3">FreshSip JMS &middot; Setup complete</h3>
            <div class="alert alert-success">Database connection OK.</div>

            <h6>Seeding demo accounts</h6>
            <ul>
                <?php foreach ($results as $r): ?><li><?= e($r) ?></li><?php endforeach; ?>
            </ul>

            <h6 class="mt-3">Current data</h6>
            <ul>
                <?php foreach ($counts as $label => $n): ?>
                    <li><?= e($label) ?>: <strong><?= $n ?></strong></li>
                <?php endforeach; ?>
            </ul>

            <h6 class="mt-3">Demo logins (password: <code>password123</code>)</h6>
            <table class="table table-sm">
                <tr><td>Admin</td><td>admin@freshsip.test</td></tr>
                <tr><td>Staff</td><td>staff@freshsip.test</td></tr>
                <tr><td>Customer</td><td>alice@freshsip.test</td></tr>
            </table>

            <a class="btn btn-success" href="<?= e(url('index.php')) ?>">Go to the app &raquo;</a>
            <p class="text-muted small mt-3 mb-0">For security you may delete <code>setup.php</code> now.</p>
        </div>
    </div>
</div>
</body>
</html>
