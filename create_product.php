<?php
include __DIR__ . '/config.php';

$message = '';
$categories = [];

try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $error) {
    error_log('Error loading categories for product form: ' . $error->getMessage());
    $message = 'Categories could not be loaded. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $reorderLevel = trim($_POST['reorder_level'] ?? '5');

    if ($name === '') {
        $message = 'Gadget name is required.';
    } elseif (!is_numeric($price) || $price < 0) {
        $message = 'Price must be a valid number.';
    } elseif (!ctype_digit($quantity)) {
        $message = 'Quantity must be a whole number.';
    } elseif (!ctype_digit($reorderLevel)) {
        $message = 'Reorder level must be a whole number.';
    }

    if (!$message) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, category_id, price, quantity, reorder_level)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $categoryId, $price, $quantity, $reorderLevel]);
            $_SESSION['success_message'] = 'Gadget added successfully.';
            header('Location: index.php');
            exit;
        } catch (PDOException $error) {
            error_log('Error adding product: ' . $error->getMessage());
            $message = 'The gadget could not be added. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product</title>
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
    <h1 class="h4 mb-1">Add Gadget</h1>
    <p class="text-muted mb-3">Enter the gadget details below.</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-danger py-2">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <form
        method="post"
        class="card card-body"
        data-submit-feedback
        data-loading-text="Saving..."
    >
        <div class="mb-3">
            <label for="name" class="form-label">Gadget name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?php echo e($_POST['name'] ?? ''); ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select
                id="category_id"
                name="category_id"
                class="form-select"
            >
                <option value="0">No category</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?php echo e($category['id']); ?>"
                        <?php
                        if ((int)($_POST['category_id'] ?? 0) === (int)$category['id']) {
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
                <label for="price" class="form-label">Price (₱) </label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?php echo e($_POST['price'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="col mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    class="form-control"
                    min="0"
                    value="<?php echo e($_POST['quantity'] ?? ''); ?>"
                    required
                >
            </div>
            <div class="col mb-3">
                <label for="reorder_level" class="form-label">Reorder level</label>
                <input
                    type="number"
                    id="reorder_level"
                    name="reorder_level"
                    class="form-control"
                    min="0"
                    value="<?php echo e($_POST['reorder_level'] ?? '5'); ?>"
                >
            </div>
        </div>

        <div class="d-flex gap-2">
            <button
                type="submit"
                class="btn btn-primary"
            >
                Add Gadget
            </button>
            <a
                href="index.php"
                class="btn btn-outline-secondary"
            >
                Cancel
            </a>
        </div>
    </form>
</div>
<script src="Script/script.js" defer></script>
</body>
</html>
