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
        p.tanggal_pengembalian,
        d.terlambat_hari,
        d.denda_terlambat,
        d.denda_rusak,
        d.total_denda,
        d.keterangan
    FROM peminjaman p
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    LEFT JOIN denda d ON p.id_peminjaman = d.id_peminjaman
    WHERE p.id_user = '$id_user'
      AND p.status = 'dikembalikan'
    ORDER BY p.tanggal_pengembalian DESC
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
    <td><?= date('d M Y', strtotime($r['tanggal_pengembalian'])) ?></td>

    <td>
        <?= $r['terlambat_hari'] > 0
            ? $r['terlambat_hari'].' hari'
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
    <p><strong>Terlambat:</strong> <?= $r['terlambat_hari'] ?> hari</p>
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
