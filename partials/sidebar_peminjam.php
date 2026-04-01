<?php
$current_page = basename($_SERVER['PHP_SELF']);

$menu = [
    [
        'label' => 'Dashboard',
        'icon' => 'bi-house-door-fill',
        'url' => 'dashboard.php'
    ],
    [
        'divider' => true
    ],
    [
        'section' => 'Aktivitas'
    ],
    [
        'label' => 'Ajukan',
        'icon' => 'bi-plus-square',
        'url' => 'ajukan.php'
    ],
    [
        'label' => 'Pengembalian',
        'icon' => 'bi-arrow-counterclockwise',
        'url' => 'pengembalian.php'
    ],
    [
        'label' => 'Riwayat Peminjaman',
        'icon' => 'bi-clock-history',
        'url' => 'riwayat.php'
    ],
];
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width:250px; min-height:100vh;">

    <!-- BRAND -->
    <a href="dashboard.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <i class="bi bi-person-circle me-2"></i>
        <span class="fs-5 fw-bold">Peminjam Panel</span>
    </a>

    <hr class="text-secondary">

    <!-- USER -->
    <div class="mb-3">
        <small class="text-secondary">Login sebagai</small><br>
        <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
    </div>

    <!-- MENU -->
    <ul class="nav nav-pills flex-column mb-auto">

        <?php foreach ($menu as $item): ?>

            <?php if (isset($item['divider'])): ?>
                <hr class="text-secondary">

            <?php elseif (isset($item['section'])): ?>
                <small class="text-secondary px-2"><?= $item['section'] ?></small>

            <?php else: ?>
                <?php
                $active = ($current_page === $item['url'])
                    ? 'active bg-primary text-white'
                    : 'text-white';
                ?>

                <li>
                    <a href="<?= $item['url'] ?>" class="nav-link <?= $active ?>">
                        <i class="bi <?= $item['icon'] ?> me-2"></i>
                        <?= $item['label'] ?>
                    </a>
                </li>

            <?php endif; ?>

        <?php endforeach; ?>

    </ul>

    <hr class="text-secondary">

    <!-- LOGOUT -->
    <a href="../auth/logout.php" class="btn btn-outline-light w-100">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">