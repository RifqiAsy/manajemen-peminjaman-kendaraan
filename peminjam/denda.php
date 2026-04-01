<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');
include '../config/database.php';

$id_user = $_SESSION['id_user'];

// =============================
// DATA DENDA PEMINJAM
// =============================
$data = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        k.nama_kendaraan,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        pg.tanggal_kembali,

        SUM(CASE WHEN dn.jenis_denda='terlambat' THEN dn.jumlah ELSE 0 END) AS denda_terlambat,
        SUM(CASE WHEN dn.jenis_denda='kerusakan' THEN dn.jumlah ELSE 0 END) AS denda_rusak,
        SUM(dn.jumlah) AS total_denda

    FROM peminjaman p
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN kendaraan k ON dp.id_kendaraan = k.id_kendaraan
    LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
    LEFT JOIN denda dn ON pg.id_pengembalian = dn.id_pengembalian

    WHERE p.id_user = '$id_user'
    AND p.status = 'dikembalikan'

    GROUP BY p.id_peminjaman
    ORDER BY pg.tanggal_kembali DESC
");
?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container mt-5">

<a href="dashboard.php" class="btn btn-secondary mb-3">← Dashboard</a>

<h3 class="mb-4">Riwayat Pengembalian & Denda</h3>

<?php if (mysqli_num_rows($data) > 0): ?>
<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
<thead class="table-light">
<tr>
    <th>No</th>
    <th>Kendaraan</th>
    <th>Tgl Pinjam</th>
    <th>Tgl Kembali</th>
    <th>Terlambat</th>
    <th>Total Denda</th>
    <th>Detail</th>
</tr>
</thead>
<tbody>
<?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['nama_kendaraan']) ?></td>
    <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
    <td>
    <?= $r['tanggal_kembali'] 
        ? date('d M Y', strtotime($r['tanggal_kembali'])) 
        : '-' ?>
    </td>

   <?php
    $terlambat = 0;

    if ($r['tanggal_kembali'] && $r['tanggal_jatuh_tempo']) {
        $tgl1 = new DateTime($r['tanggal_jatuh_tempo']);
        $tgl2 = new DateTime($r['tanggal_kembali']);

        if ($tgl2 > $tgl1) {
            $terlambat = $tgl1->diff($tgl2)->days;
        }
    }
    ?>

    <td>
        <?= $terlambat > 0
            ? $terlambat . ' hari'
            : '<span class="text-success">Tepat waktu</span>' ?>
    </td>

    <td>
        <?php if ($r['total_denda'] > 0): ?>
            <span class="text-danger fw-bold">
                Rp <?= number_format($r['total_denda'], 0, ',', '.') ?>
            </span>
        <?php else: ?>
            <span class="text-success">Rp 0</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($r['total_denda'] > 0): ?>
        <button class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#detail<?= $r['id_peminjaman'] ?>">
            Lihat
        </button>
        <?php else: ?>
        -
        <?php endif; ?>
    </td>
</tr>

<!-- MODAL DETAIL DENDA -->
<div class="modal fade" id="detail<?= $r['id_peminjaman'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Detail Denda</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p><strong>Kendaraan:</strong> <?= htmlspecialchars($r['nama_kendaraan']) ?></p>
    <p><strong>Terlambat:</strong> <?= $terlambat ?> hari</p>
    <p><strong>Denda Terlambat:</strong> Rp <?= number_format($r['denda_terlambat'],0,',','.') ?></p>
    <p><strong>Denda Kerusakan:</strong> Rp <?= number_format($r['denda_rusak'],0,',','.') ?></p>
    <hr>
    <p class="fw-bold">
        Total: Rp <?= number_format($r['total_denda'],0,',','.') ?>
    </p>

    <?php if (!empty($r['keterangan'])): ?>
        <p><strong>Keterangan:</strong><br><?= nl2br(htmlspecialchars($r['keterangan'])) ?></p>
    <?php endif; ?>
</div>

</div>
</div>
</div>
<!-- END MODAL -->

<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">
    Belum ada riwayat pengembalian.
</div>
<?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
