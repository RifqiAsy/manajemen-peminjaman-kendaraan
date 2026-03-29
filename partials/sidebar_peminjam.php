<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 250px; min-height: 100vh;">

    <!-- BRAND -->
    <a href="dashboard.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <i class="bi bi-person-circle me-2"></i>
        <span class="fs-5 fw-bold">Peminjam Panel</span>
    </a>
    <hr class="text-secondary">

    <!-- USER INFO -->
    <div class="mb-3">
        <small class="text-secondary">Login sebagai</small><br>
        <strong><?= $_SESSION['username'] ?? 'User' ?></strong>
    </div>

    <!-- MENU -->
    <ul class="nav nav-pills flex-column mb-auto">

        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current_page=='dashboard.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-house-door-fill me-2"></i> Dashboard
            </a>
        </li>

        <hr class="text-secondary">

        <small class="text-secondary px-2">Aktivitas</small>

        <li>
            <a href="ajukan.php" class="nav-link <?= $current_page=='ajukan.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-plus-square me-2"></i> Ajukan
            </a>
        </li>

        <li>
            <a href="pengembalian.php" class="nav-link <?= $current_page=='pengembalian.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Pengembalian
            </a>
        </li>

    </ul>

    <hr class="text-secondary">

    <!-- LOGOUT -->
    <a href="../auth/logout.php" class="btn btn-outline-light w-100">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">