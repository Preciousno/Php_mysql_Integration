<?php
include __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];
$product = null;
$categories = [];
$canEdit = true;

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch() ?: null;

    if ($product) {
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }
} catch (PDOException $error) {
    error_log('Error loading product for update: ' . $error->getMessage());
    $errors[] = 'The gadget could not be loaded. Please try again later.';
    $canEdit = false;
}

if (!$product && !$errors) {
    http_response_code(404);
    $errors[] = 'The requested gadget was not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product && $canEdit) {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $reorderLevel = trim($_POST['reorder_level'] ?? '5');
    $errors = [];

    if ($name === '') {
        $errors[] = 'Gadget name is required.';
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = 'Price must be a valid number.';
    }
    if (!ctype_digit($quantity)) {
        $errors[] = 'Quantity must be a whole number.';
    }
    if (!ctype_digit($reorderLevel)) {
        $errors[] = 'Reorder level must be a whole number.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE products
                 SET name = ?, category_id = ?, price = ?, quantity = ?, reorder_level = ?
                 WHERE id = ?"
            );
            $stmt->execute([$name, $categoryId, $price, $quantity, $reorderLevel, $id]);
            $_SESSION['success_message'] = 'Gadget updated successfully.';
            header('Location: index.php');
            exit;
        } catch (PDOException $error) {
            error_log('Error updating product: ' . $error->getMessage());
            $errors[] = 'The gadget could not be updated. Please try again later.';
        }
    }
    $product = array_merge($product, [
        'name' => $name,
        'category_id' => $categoryId,
        'price' => $price,
        'quantity' => $quantity,
        'reorder_level' => $reorderLevel,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="Style/style.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light inventory-app">
<div class="container py-4 narrow-page">
    <h1 class="h4 mb-3">Edit Gadget</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2" role="alert">
            <?php echo e($err); ?>
        </div>
    <?php endforeach; ?>

    <?php if (!$product || !$canEdit): ?>
        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            Back to inventory
        </a>
    <?php else: ?>
        <form
            method="post"
            class="card card-body"
            data-submit-feedback
            data-loading-text="Updating..."
        >
            <input
                type="hidden"
                name="id"
                value="<?php echo $id; ?>"
            >
            <div class="mb-3">
                <label
                    for="name"
                    class="form-label"
                >
                    Name
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?php echo e($product['name']); ?>"
                    required
                >
            </div>
            <div class="mb-3">
                <label
                    for="category_id"
                    class="form-label"
                >
                    Category
                </label>
                <select
                    id="category_id"
                    name="category_id"
                    class="form-select"
                >
                    <option value="0">
                        -- None --
                    </option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?php echo e($category['id']); ?>"
                            <?php
                            if ($product['category_id'] == $category['id']) {
                                echo 'selected';
                            }
                            ?>
                        >
                            <?php echo e($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label
                        for="price"
                        class="form-label"
                    >
                        Price (₱)
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-control"
                        step="0.01"
                        min="0"
                        value="<?php echo e($product['price']); ?>"
                        required
                    >
                </div>
                <div class="col mb-3">
                    <label
                        for="quantity"
                        class="form-label"
                    >
                        Quantity
                    </label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        class="form-control"
                        min="0"
                        value="<?php echo e($product['quantity']); ?>"
                        required
                    >
                </div>
                <div class="col mb-3">
                    <label
                        for="reorder_level"
                        class="form-label"
                    >
                        Reorder level
                    </label>
                    <input
                        type="number"
                        id="reorder_level"
                        name="reorder_level"
                        class="form-control"
                        min="0"
                        value="<?php echo e($product['reorder_level']); ?>"
                    >
                </div>
            </div>
            <div class="d-flex gap-2">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update
                </button>
                <a
                    href="index.php"
                    class="btn btn-outline-secondary"
                >
                    Cancel
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>
<script src="Script/script.js" defer></script>
</body>
</html>
