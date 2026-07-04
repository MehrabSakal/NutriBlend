<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('Admin');

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$icons = ['cup-straw', 'cup', 'cup-hot', 'flower1', 'lightning-charge', 'droplet', 'egg-fried'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float) ($_POST['price'] ?? 0);
    $category_id = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
    $icon        = $_POST['icon'] ?? 'cup-straw';
    $available   = isset($_POST['is_available']) ? 1 : 0;

    if ($name === '' || $price <= 0) {
        flash('error', 'Name and a positive price are required.');
        redirect('features/menu-management/add_product.php');
    }

    $pdo->prepare(
        'INSERT INTO products (name, description, price, category_id, icon, is_available)
         VALUES (?,?,?,?,?,?)'
    )->execute([$name, $description, $price, $category_id, $icon, $available]);

    flash('success', 'Product added. Set its recipe so stock is deducted correctly.');
    redirect('features/menu-management/index.php');
}

$pageTitle = 'Add product';
require_once APP_ROOT . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <h3 class="mb-3"><i class="bi bi-plus-circle"></i> Add product</h3>
        <div class="card shadow-sm"><div class="card-body">
            <form method="post" action="<?= e(url('features/menu-management/add_product.php')) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" maxlength="255">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— none —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <?php foreach ($icons as $ic): ?>
                                <option value="<?= e($ic) ?>"><?= e($ic) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_available" id="avail" checked>
                    <label class="form-check-label" for="avail">Available on the menu</label>
                </div>
                <button class="btn btn-jms">Save product</button>
                <a class="btn btn-link" href="<?= e(url('features/menu-management/index.php')) ?>">Cancel</a>
            </form>
        </div></div>
    </div>
</div>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
