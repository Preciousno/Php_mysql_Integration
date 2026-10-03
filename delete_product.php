<?php
include __DIR__ . '/config.php';

$errorMessage = '';
$statusCode = 400;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errorMessage = 'Please use the delete button to remove a product.';
} else {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $errorMessage = 'Product ID is missing or invalid.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                $errorMessage = 'The product could not be found or was already deleted.';
            } else {
                $_SESSION['success_message'] = 'Gadget deleted successfully.';
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $error) {
            error_log('Error deleting product: ' . $error->getMessage());
            $statusCode = 500;
            $errorMessage = 'The product could not be deleted. Please try again later.';
        }
    }
}

http_response_code($statusCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error deleting product</title>
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
<main class="container py-4 narrow-page">
    <h1 class="h4 mb-3">Could not delete product</h1>
    <div
        class="alert alert-danger"
        role="alert"
    >
        <?php echo e($errorMessage); ?>
    </div>
    <a
        href="index.php"
        class="btn btn-outline-secondary"
    >
        Back to inventory
    </a>
</main>
</body>
</html>
