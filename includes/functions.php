<?php
/**
 * Shared helper functions for JMS.
 */

/** Escape output for safe HTML rendering. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build an absolute URL from the project root. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Redirect helper. */
function redirect(string $path): void
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Format a money value. */
function money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

/* ------------------------------------------------------------------ *
 *  Authentication helpers
 * ------------------------------------------------------------------ */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function has_role(string ...$roles): bool
{
    $user = current_user();
    return $user && in_array($user['role'], $roles, true);
}

/** Require any logged-in user, otherwise send to login. */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('features/auth/login.php');
    }
}

/** Require a specific role, otherwise 403. */
function require_role(string ...$roles): void
{
    require_login();
    if (!has_role(...$roles)) {
        http_response_code(403);
        die('<h2>403 - Access denied</h2><p>You do not have permission to view this page.</p>'
            . '<p><a href="' . e(url('index.php')) . '">Back to menu</a></p>');
    }
}

/* ------------------------------------------------------------------ *
 *  Flash messages (one-shot session messages)
 * ------------------------------------------------------------------ */

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function render_flash(): void
{
    foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $type) {
        if ($msg = flash($key)) {
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
                . e($msg)
                . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
    }
}

/* ------------------------------------------------------------------ *
 *  Cart helpers (session based)
 * ------------------------------------------------------------------ */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_column(cart(), 'quantity'));
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

/**
 * CSRF token helpers - basic protection for state-changing forms.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('Invalid or expired form token. Please go back and try again.');
    }
}

/* ------------------------------------------------------------------ *
 *  Order helpers
 * ------------------------------------------------------------------ */

/** Bootstrap badge colour class for a given order status. */
function order_status_badge(string $status): string
{
    return [
        'Pending'   => 'warning text-dark',
        'Preparing' => 'primary',
        'Served'    => 'success',
        'Cancelled' => 'danger',
    ][$status] ?? 'secondary';
}

/** Ordered steps a normal order moves through (used for the tracker UI). */
function order_status_steps(): array
{
    return ['Pending', 'Preparing', 'Served'];
}

/**
 * Cancel an order and reverse its side-effects (stock + loyalty points).
 *
 * Only Pending or Preparing orders can be cancelled. If $ownerId is provided
 * the order must belong to that user (customer self-service cancel); pass null
 * for a staff/admin cancel. Returns [success(bool), message(string)].
 */
function cancel_order(PDO $pdo, int $orderId, ?int $ownerId = null): array
{
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            $pdo->rollBack();
            return [false, 'Order not found.'];
        }
        if ($ownerId !== null && (int) $order['user_id'] !== $ownerId) {
            $pdo->rollBack();
            return [false, 'You cannot cancel this order.'];
        }
        if (!in_array($order['status'], ['Pending', 'Preparing'], true)) {
            $pdo->rollBack();
            return [false, 'Only pending or preparing orders can be cancelled.'];
        }

        // Restore ingredient stock consumed by the order's items.
        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $items->execute([$orderId]);

        $recipeStmt = $pdo->prepare(
            'SELECT ingredient_id, quantity_used FROM product_ingredients WHERE product_id = ?'
        );
        $restock = $pdo->prepare('UPDATE inventory SET stock_level = stock_level + ? WHERE id = ?');

        foreach ($items->fetchAll() as $it) {
            if (!$it['product_id']) {
                continue;
            }
            $recipeStmt->execute([$it['product_id']]);
            foreach ($recipeStmt->fetchAll() as $r) {
                $restock->execute([$r['quantity_used'] * $it['quantity'], $r['ingredient_id']]);
            }
        }

        // Reverse loyalty: give back redeemed points, take back the earned ones.
        $newPoints = (int) $order['points_redeemed'] - (int) $order['points_earned'];
        if ($newPoints !== 0) {
            $pdo->prepare('UPDATE users SET loyalty_points = GREATEST(0, loyalty_points + ?) WHERE id = ?')
                ->execute([$newPoints, $order['user_id']]);
        }

        $pdo->prepare('UPDATE orders SET status = "Cancelled" WHERE id = ?')->execute([$orderId]);

        $pdo->commit();

        // Keep the session balance in sync if the current user owns the order.
        $current = current_user();
        if ($current && (int) $current['id'] === (int) $order['user_id']) {
            $bal = $pdo->prepare('SELECT loyalty_points FROM users WHERE id = ?');
            $bal->execute([$order['user_id']]);
            $_SESSION['user']['loyalty_points'] = (int) $bal->fetchColumn();
        }

        return [true, "Order #$orderId cancelled."];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, 'Could not cancel the order: ' . $e->getMessage()];
    }
}
