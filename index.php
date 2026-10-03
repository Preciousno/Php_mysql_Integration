<?php
include __DIR__ . '/config.php';

$stats = null;
$statsError = '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

try {
    $stats = $pdo->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(price * quantity), 0) AS stock_value,
                COALESCE(SUM(quantity <= reorder_level), 0) AS low_stock
         FROM products"
    )->fetch();
} catch (PDOException $e) {
    error_log('Error loading inventory summary: ' . $e->getMessage());
    $statsError = 'The inventory summary could not be loaded. Please refresh the page or try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gadget Inventory</title>
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
<body class="inventory-app">
<div class="app-layout">
    <aside class="app-sidebar">
        <a
            href="index.php"
            class="app-brand"
        >
            <span class="brand-mark" aria-hidden="true">G</span>
            <span>
                Gadget
                <span class="brand-subtitle">Inventory</span>
            </span>
        </a>
        <div class="sidebar-label">
            WORKSPACE
        </div>
        <nav
            class="sidebar-nav"
            aria-label="Main navigation"
        >
            <a
                href="index.php"
                class="sidebar-link active"
                aria-current="page"
            >
                <span class="nav-icon" aria-hidden="true">▦</span>
                Dashboard
            </a>
            <a
                href="categories.php"
                class="sidebar-link"
            >
                <span class="nav-icon" aria-hidden="true">◇</span>
                Categories
            </a>
            <a
                href="create_product.php"
                class="sidebar-link"
            >
                <span class="nav-icon" aria-hidden="true">＋</span>
                Add product
            </a>
        </nav>
        <div class="sidebar-footer">
            Inventory management
        </div>
    </aside>

    <main class="app-main">
        <header class="topbar">
            <span class="topbar-caption">
                Workspace
                <span aria-hidden="true">/</span>
                Dashboard
            </span>
            <a
                href="categories.php"
                class="topbar-link"
            >
                Manage categories
            </a>
        </header>

        <div class="dashboard-content">
            <div
                class="page-header d-flex justify-content-between align-items-center"
            >
                <div>
                    <div class="eyebrow">
                        OVERVIEW
                    </div>
                    <h1 class="h3 mb-1">
                        Dashboard
                    </h1>
                    <p class="page-description mb-0">
                        Keep track of your gadgets and stock levels.
                    </p>
                </div>
                <div>
                    <a
                        href="create_product.php"
                        class="btn btn-primary"
                    >
                        ＋
                        <span>Add product</span>
                    </a>
                </div>
            </div>

            <?php if ($statsError !== ''): ?>
                <div
                    class="alert alert-danger py-2"
                    role="alert"
                >
                    <?php echo e($statsError); ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div
                    class="alert alert-success py-2"
                    role="status"
                >
                    <?php echo e($successMessage); ?>
                </div>
            <?php endif; ?>

            <section
                class="concept-banner"
                aria-label="Gadget inventory concept"
            >
                <div class="concept-banner-copy">
                    <div class="eyebrow">
                        YOUR GADGET STORE, IN SYNC
                    </div>
                    <h2>
                        Power up your inventory.
                    </h2>
                    <p>
                        All your tech, stock levels, and product details in one smart workspace.
                    </p>
                </div>
                <div
                    class="concept-device"
                    aria-hidden="true"
                >
                    ⌁
                </div>
            </section>

            <section
                class="row g-3 summary-cards"
                aria-label="Inventory summary"
            >
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div
                            class="stat-icon stat-icon-blue"
                            aria-hidden="true"
                        >
                            ▦
                        </div>
                        <div>
                            <small>
                                Total products
                            </small>
                            <div class="stat-value">
                                <?php echo e($stats['total'] ?? '—'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div
                            class="stat-icon stat-icon-green"
                            aria-hidden="true"
                        >
                            ₱
                        </div>
                        <div>
                            <small>
                                Total stock value
                            </small>
                            <div class="stat-value">
                                <?php
                                if ($stats) {
                                    echo '₱' . number_format($stats['stock_value'], 2);
                                } else {
                                    echo '—';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div
                            class="stat-icon stat-icon-orange"
                            aria-hidden="true"
                        >
                            !
                        </div>
                        <div>
                            <small>
                                Low-stock items
                            </small>
                            <div class="stat-value">
                                <?php echo e($stats['low_stock'] ?? '—'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="products-panel"
                aria-label="Product inventory"
            >
                <?php include __DIR__ . '/read.php'; ?>
            </section>
            <footer class="app-footer">
                Gadget Inventory
                <span>·</span>
                Simple stock management
            </footer>
        </div>
    </main>
</div>
<script src="Script/script.js" defer></script>
</body>
</html>
