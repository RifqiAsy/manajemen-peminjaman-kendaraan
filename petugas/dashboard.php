<?php
include "../middleware/auth.php";
cekLogin();
cekRole('petugas');
include "../config/database.php";

// ============================
// TOTAL DATA (REAL TIME)
// ============================

// Total peminjaman
$qTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM peminjaman
");
$total_peminjaman = mysqli_fetch_assoc($qTotal)['total'];

// Menunggu persetujuan
$qMenunggu = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'menunggu'
");
$total_menunggu = mysqli_fetch_assoc($qMenunggu)['total'];

// Sedang dipinjam
$qDisetujui = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'disetujui'
");
$total_disetujui = mysqli_fetch_assoc($qDisetujui)['total'];

// Menunggu pengembalian diverifikasi
$qKembali = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE status = 'menunggu_kembali'
");
$total_kembali = mysqli_fetch_assoc($qKembali)['total'];

// ============================
// DATA TERBARU
// ============================
$qLast = mysqli_query($conn, "
    SELECT 
        p.status,
        p.tanggal_pinjam,
        k.nama_kendaraan,
        u.nama
    FROM peminjaman p
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan

    JOIN users u ON p.id_user = u.id_user
    ORDER BY p.id_peminjaman DESC
    LIMIT 5
");
?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-auto p-0">
        <?php include "../partials/sidebar_petugas.php"; ?>
    </div>

    <!-- CONTENT -->
    <div class="col p-4">

        <!-- TOPBAR -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Dashboard Petugas</h4>
                <small class="text-muted">Ringkasan aktivitas peminjaman kendaraan</small>
            </div>
            <div>👤 <?= $_SESSION['nama']; ?></div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Total Peminjaman</small>
                        <h3 class="fw-bold"><?= $total_peminjaman ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Menunggu Persetujuan</small>
                        <h3 class="fw-bold text-warning"><?= $total_menunggu ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Sedang Dipinjam</small>
                        <h3 class="fw-bold text-primary"><?= $total_disetujui ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Menunggu Pengembalian</small>
                        <h3 class="fw-bold text-danger"><?= $total_kembali ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL DATA TERBARU -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Peminjaman Terbaru</h5>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Kendaraan</th>
                                <th>Status</th>
                                <th>Tgl Pinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($qLast) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($r = mysqli_fetch_assoc($qLast)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= htmlspecialchars($r['nama_kendaraan']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada data peminjaman
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
