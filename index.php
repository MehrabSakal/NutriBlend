<?php
/**
 * Customer landing page / menu (Epic 001).
 * Browse available products by category and add them to the cart with
 * customizations (US001-US003).
 */
require_once __DIR__ . '/includes/bootstrap.php';

$activeCat = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

if ($activeCat) {
    $stmt = $pdo->prepare(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_available = 1 AND p.category_id = ?
         ORDER BY p.name'
    );
    $stmt->execute([$activeCat]);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo->query(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_available = 1 ORDER BY p.name'
    )->fetchAll();
}

$customOptions = ['No Sugar', 'Extra Ice', 'Add Protein', 'Less Ice', 'Extra Sweet'];

$pageTitle = 'Menu';
require_once __DIR__ . '/includes/header.php';
?>
<div class="jms-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold">Freshly squeezed, made to order 🥤</h1>
            <p class="mb-0 lead">Pick your favourite juice, customise it your way, and earn loyalty points on every sip.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <?php if (!is_logged_in()): ?>
                <a class="btn btn-light btn-lg" href="<?= e(url('features/auth/register.php')) ?>">Join &amp; earn points</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Category filter -->
<div class="mb-4">
    <a href="<?= e(url('index.php')) ?>" class="btn btn-sm <?= $activeCat === 0 ? 'btn-jms' : 'btn-outline-secondary' ?> mb-1">All</a>
    <?php foreach ($categories as $c): ?>
        <a href="<?= e(url('index.php?cat=' . $c['id'])) ?>"
           class="btn btn-sm <?= $activeCat === (int) $c['id'] ? 'btn-jms' : 'btn-outline-secondary' ?> mb-1">
            <?= e($c['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <?php if (!$products): ?>
        <div class="col-12"><div class="alert alert-light text-center">No products available right now.</div></div>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card product-card">
                <div class="product-thumb"><i class="bi bi-<?= e($p['icon']) ?>"></i></div>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title mb-1"><?= e($p['name']) ?></h6>
                    <p class="small text-muted flex-grow-1"><?= e($p['description']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="price-tag"><?= money($p['price']) ?></span>
                        <button class="btn btn-sm btn-jms" data-bs-toggle="modal" data-bs-target="#addModal<?= (int) $p['id'] ?>">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customization modal -->
        <div class="modal fade" id="addModal<?= (int) $p['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="post" action="<?= e(url('features/order-management/add_to_cart.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= e($p['name']) ?> &middot; <?= money($p['price']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Customise</label>
                        <div class="mb-3">
                            <?php foreach ($customOptions as $opt): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="customizations[]" value="<?= e($opt) ?>"
                                           id="opt<?= (int) $p['id'] ?>_<?= e(str_replace(' ', '', $opt)) ?>">
                                    <label class="form-check-label" for="opt<?= (int) $p['id'] ?>_<?= e(str_replace(' ', '', $opt)) ?>">
                                        <?= e($opt) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <label class="form-label fw-semibold">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:120px">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-jms"><i class="bi bi-cart-plus"></i> Add to cart</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
