<?php
include '../middleware/auth.php';
cekLogin();
cekRole('admin');
include '../config/database.php';

// ============================
// STATISTIK
// ============================

$total_user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users")
)['total'];

$total_kendaraan = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM kendaraan")
)['total'];

$total_peminjaman = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM peminjaman")
)['total'];

$peminjaman_aktif = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM peminjaman WHERE status='disetujui'")
)['total'];

$menunggu_kembali = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM peminjaman WHERE status='menunggu_kembali'")
)['total'];

// ============================
// DATA TERBARU
// ============================
$list = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        u.nama,
        k.nama_kendaraan,
        p.status,
        p.tanggal_pinjam
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    ORDER BY p.id_peminjaman DESC
    LIMIT 5
");
?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container-fluid">
<div class="row">

<div class="col-auto p-0">
    <?php include "../partials/sidebar_admin.php"; ?>
</div>

<div class="col p-4">

    <div class="d-flex justify-content-between mb-4">
        <div>
            <h4>Dashboard Admin</h4>
            <small class="text-muted">Ringkasan sistem</small>
        </div>
        <div>👤 <?= $_SESSION['nama']; ?></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Total User</small>
                    <h3><?= $total_user ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Total Kendaraan</small>
                    <h3><?= $total_kendaraan ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Peminjaman Aktif</small>
                    <h3 class="text-success"><?= $peminjaman_aktif ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Menunggu Pengembalian</small>
                    <h3 class="text-warning"><?= $menunggu_kembali ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Peminjaman Terbaru</h5>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kendaraan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['nama_kendaraan']) ?></td>
                        <td>
                            <?php
                            $badge = [
                                'menunggu' => 'warning',
                                'disetujui' => 'success',
                                'menunggu_kembali' => 'info',
                                'selesai' => 'secondary',
                                'ditolak' => 'danger'
                            ][$r['status']] ?? 'dark';
                            ?>
                            <span class="badge bg-<?= $badge ?>">
                                <?= ucfirst(str_replace('_',' ',$r['status'])) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>
</div>
</div>
</body>
</html>
