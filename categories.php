<?php
include __DIR__ . '/config.php';

$error = '';
$categories = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $error = 'Category name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$name]);
                $_SESSION['success_message'] = 'Category added successfully.';
            } catch (PDOException $ex) {
                error_log('Error adding category: ' . $ex->getMessage());
                if ($ex->getCode() === '23000') {
                    $error = 'That category already exists.';
                } else {
                    $error = 'The category could not be added. Please try again later.';
                }
            }
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            if ($stmt->rowCount() === 0) {
                $error = 'The category could not be found or was already deleted.';
            } else {
                $_SESSION['success_message'] = 'Category deleted successfully.';
            }
        } catch (PDOException $ex) {
            error_log('Error deleting category: ' . $ex->getMessage());
            $error = 'The category could not be deleted. It may still be assigned to products.';
        }
    } else {
        $error = 'The requested category action is invalid.';
    }

    if (!$error) {
        header('Location: categories.php');
        exit;
    }
}

$listError = '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

try {
    $categories = $pdo->query(
        "SELECT c.*, COUNT(p.id) AS product_count
         FROM categories c LEFT JOIN products p ON p.category_id = c.id
         GROUP BY c.id ORDER BY c.name"
    )->fetchAll();
} catch (PDOException $ex) {
    error_log('Error reading categories: ' . $ex->getMessage());
    $listError = 'Categories could not be loaded. Please refresh the page or try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categories</title>
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
    <div
        class="d-flex justify-content-between align-items-center mb-3"
    >
        <h1 class="h4 mb-0">Categories</h1>
        <a
            href="index.php"
            class="btn btn-outline-secondary btn-sm"
        >
            Back
        </a>
    </div>

    <?php if ($successMessage !== ''): ?>
        <div
            class="alert alert-success py-2"
            role="status"
        >
            <?php echo e($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div
            class="alert alert-danger py-2"
            role="alert"
        >
            <?php echo e($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($listError !== ''): ?>
        <div
            class="alert alert-danger py-2"
            role="alert"
        >
            <?php echo e($listError); ?>
        </div>
    <?php endif; ?>

    <?php if ($listError === ''): ?>
        <form
            method="post"
            class="input-group mb-3"
            data-submit-feedback
            data-loading-text="Adding..."
        >
            <input
                type="hidden"
                name="action"
                value="add"
            >
            <input
                type="text"
                name="name"
                class="form-control"
                placeholder="New category name"
                required
            >
            <button
                type="submit"
                class="btn btn-primary"
            >
                Add
            </button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered bg-white align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Products</th>
                        <th class="category-actions-column"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo e($category['name']); ?></td>
                            <td><?php echo e($category['product_count']); ?></td>
                            <td>
                                <form
                                    method="post"
                                    data-confirm="Delete this category?"
                                    data-submit-feedback
                                    data-loading-text="Deleting..."
                                >
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo e($category['id']); ?>"
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
    <?php endif; ?>
</div>
<script src="Script/script.js" defer></script>
</body>
</html>
