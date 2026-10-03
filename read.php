<?php
include_once __DIR__ . '/config.php';

$search = trim($_GET['search'] ?? '');
$cat    = (int)($_GET['category'] ?? 0);

$products = [];
$categories = [];
$readError = '';

try {
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ?";
    $params = ['%' . $search . '%'];

    if ($cat > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $cat;
    }

    $stmt = $pdo->prepare($sql . " ORDER BY p.created_at DESC");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $error) {
    error_log('Error reading product inventory: ' . $error->getMessage());
    $readError = 'The product inventory could not be loaded. Please refresh the page or try again later.';
}
?>
<?php if ($readError !== ''): ?>
    <div
        class="alert alert-danger py-2"
        role="alert"
    >
        <?php echo e($readError); ?>
    </div>
<?php endif; ?>
<div class="products-heading">
    <div>
        <h2>Product inventory</h2>
        <p>
            <?php echo count($products); ?>
            <?php echo count($products) === 1 ? 'product' : 'products'; ?>
            found
        </p>
    </div>
</div>

<form method="get" class="row g-2 mb-3 inventory-filters">
    <div class="col-md-5">
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search products..."
            aria-label="Search products"
            value="<?php echo e($search); ?>"
        >
    </div>
    <div class="col-md-4">
        <select
            name="category"
            class="form-select"
        >
            <option value="0">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option
                    value="<?php echo e($c['id']); ?>"
                    <?php
                    if ($cat == $c['id']) {
                        echo 'selected';
                    }
                    ?>
                >
                    <?php echo e($c['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 d-grid">
        <button
            type="submit"
            class="btn btn-outline-secondary"
        >
            Apply filters
        </button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Status</th>
                <th class="actions-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$products && $readError === ''): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        No products found.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo e($product['id']); ?></td>
                    <td><?php echo e($product['name']); ?></td>
                    <td><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></td>
                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo e($product['quantity']); ?></td>
                    <td>
                        <?php if ($product['quantity'] <= $product['reorder_level']): ?>
                            <span class="badge bg-danger">Low stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">In stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a
                            href="update_product.php?id=<?php echo e($product['id']); ?>"
                            class="btn btn-sm btn-warning"
                        >
                            Edit
                        </a>
                        <form
                            action="delete_product.php"
                            method="post"
                            class="d-inline"
                            data-confirm="Delete this product?"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?php echo e($product['id']); ?>"
                            >
                            <button
                                type="submit"
                                class="btn btn-sm btn-danger"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
