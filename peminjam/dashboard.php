<?php
include "../middleware/auth.php";
cekLogin();
cekRole('peminjam');
include "../config/database.php";

$id_user = (int) $_SESSION['id_user'];

// ============================
// TOTAL PEMINJAMAN
// ============================
$qTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM peminjaman 
    WHERE id_user = $id_user
");
$total_peminjaman = mysqli_fetch_assoc($qTotal)['total'] ?? 0;

// ============================
// DATA TERAKHIR
// ============================
$qLastPeminjaman = mysqli_query($conn, "
    SELECT id_peminjaman, status
    FROM peminjaman
    WHERE id_user = $id_user
    ORDER BY id_peminjaman DESC
    LIMIT 1
");

$last_status = '-';
$last_vehicle = '-';

if ($qLastPeminjaman && mysqli_num_rows($qLastPeminjaman) > 0) {

    $last = mysqli_fetch_assoc($qLastPeminjaman);
    $last_status = $last['status'] ?? '-';
    $id_last = (int)$last['id_peminjaman'];

    $qLastKendaraan = mysqli_query($conn, "
        SELECT k.nama_kendaraan
        FROM detail_peminjaman d
        JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
        WHERE d.id_peminjaman = $id_last
    ");

    $kendaraan = [];

    while ($row = mysqli_fetch_assoc($qLastKendaraan)) {
        $kendaraan[] = $row['nama_kendaraan'];
    }

    $last_vehicle = !empty($kendaraan) ? implode(', ', $kendaraan) : '-';
}

// ============================
// TOTAL DENDA
// ============================
$qTotalDenda = mysqli_query($conn, "
    SELECT COALESCE(SUM(d.jumlah),0) AS total_denda
    FROM denda d
    JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    WHERE p.id_user = $id_user
    AND d.status = 'belum_dibayar'
");
$total_denda = mysqli_fetch_assoc($qTotalDenda)['total_denda'] ?? 0;

// ============================
// DETAIL DENDA
// ============================
$qDenda = mysqli_query($conn, "
    SELECT 
        dn.id_denda,
        dn.jenis_denda,
        dn.jumlah,
        dn.keterangan,
        dn.status,
        pg.created_at,
        GROUP_CONCAT(DISTINCT k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
    FROM denda dn
    JOIN pengembalian pg ON dn.id_pengembalian = pg.id_pengembalian
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN kendaraan k ON dp.id_kendaraan = k.id_kendaraan
    WHERE p.id_user = $id_user
    GROUP BY dn.id_denda
    ORDER BY pg.created_at DESC
");

// ============================
// RIWAYAT PEMINJAMAN
// ============================
$list = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        MAX(p.status) AS status,
        MAX(p.tanggal_pinjam) AS tanggal_pinjam,
        MAX(pg.tanggal_kembali) AS tanggal_kembali,
        GROUP_CONCAT(DISTINCT k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
    FROM peminjaman p
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
    WHERE p.id_user = $id_user
    GROUP BY p.id_peminjaman
    ORDER BY p.id_peminjaman DESC
");
?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<div class="col-auto p-0">
    <?php include "../partials/sidebar_peminjam.php"; ?>
</div>

<!-- CONTENT -->
<div class="col p-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Dashboard Peminjam</h4>
        <small class="text-muted">Ringkasan peminjaman kendaraan</small>
    </div>
    <div>👤 <?= htmlspecialchars($_SESSION['nama']) ?></div>
</div>

<!-- SUMMARY -->
<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Status Terakhir</small>
                <h5 class="fw-bold mt-2"><?= ucfirst($last_status) ?></h5>
            </div>
        </div>
    </div>

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
                <small class="text-muted">Kendaraan Terakhir</small>
                <h5 class="fw-bold"><?= htmlspecialchars($last_vehicle) ?></h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-danger">
            <div class="card-body">
                <small class="text-muted">Total Denda</small>
                <h4 class="fw-bold text-danger">
                    Rp <?= number_format($total_denda) ?>
                </h4>
            </div>
        </div>
    </div>

</div>

<!-- DETAIL DENDA -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <strong>Rincian Denda</strong>
    </div>
    <div class="card-body">

    <?php if ($qDenda && mysqli_num_rows($qDenda) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kendaraan</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($d = mysqli_fetch_assoc($qDenda)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['daftar_kendaraan'] ?? '-') ?></td>
                        <td><?= ucfirst($d['jenis_denda']) ?></td>
                        <td class="text-danger fw-bold">
                            Rp <?= number_format($d['jumlah']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $d['status']=='lunas' ? 'bg-success' : 'bg-warning' ?>">
                                <?= str_replace('_',' ', $d['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($d['keterangan'] ?: '-') ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-success mb-0">
            Tidak ada denda
        </div>
    <?php endif; ?>

    </div>
</div>

<!-- RIWAYAT -->
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Riwayat Peminjaman</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kendaraan</th>
                        <th>Status</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Pengembalian</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                if ($list && mysqli_num_rows($list) > 0):
                    while ($row = mysqli_fetch_assoc($list)):
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['daftar_kendaraan'] ?? '-') ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['tanggal_pinjam'] ?></td>
                        <td><?= $row['tanggal_kembali'] ?: '-' ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Belum ada riwayat peminjaman
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