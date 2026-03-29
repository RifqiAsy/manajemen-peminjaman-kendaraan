<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 250px; min-height: 100vh;">

    <!-- BRAND -->
    <a href="dashboard.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <i class="bi bi-person-badge-fill me-2"></i>
        <span class="fs-5 fw-bold">Petugas Panel</span>
    </a>
    <hr class="text-secondary">

    <!-- USER INFO -->
    <div class="mb-3">
        <small class="text-secondary">Login sebagai</small><br>
        <strong><?= $_SESSION['username'] ?? 'Petugas' ?></strong>
    </div>

    <!-- MENU -->
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- DASHBOARD -->
        <li class="nav-item">
            <a href="dashboard.php"
               class="nav-link <?= $current_page=='dashboard.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>

        <hr class="text-secondary">

        <small class="text-secondary px-2">Transaksi</small>

        <!-- APPROVAL -->
        <li>
            <a href="approval.php"
               class="nav-link <?= $current_page=='approval.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-check2-square me-2"></i>
                Approval
            </a>
        </li>

        <!-- PENGEMBALIAN -->
        <li>
            <a href="pengembalian.php"
               class="nav-link <?= $current_page=='pengembalian.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-arrow-repeat me-2"></i>
                Pengembalian
            </a>
        </li>

        <!-- DENDA -->
        <li>
            <a href="pembayaran_denda.php"
               class="nav-link <?= $current_page=='pembayaran_denda.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-cash-stack me-2"></i>
                Pembayaran Denda
            </a>
        </li>

        <hr class="text-secondary">

        <small class="text-secondary px-2">Laporan</small>

        <!-- LAPORAN -->
        <li>
            <a href="laporan.php"
               class="nav-link <?= $current_page=='laporan.php' ? 'active bg-primary text-white' : 'text-white' ?>">
                <i class="bi bi-file-earmark-text me-2"></i>
                Laporan
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