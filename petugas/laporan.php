<?php
require '../middleware/auth.php';
cekLogin();
cekRole('petugas');

require '../config/database.php';

// 🔥 Debug mode
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Filter tanggal (optional)
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$where = "";

if (!empty($from) && !empty($to)) {
    $where = "WHERE DATE(pg.tanggal_kembali) BETWEEN '$from' AND '$to'";
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA LAPORAN + TOTAL DENDA
|--------------------------------------------------------------------------
*/
$data = mysqli_query($conn, "
    SELECT 
        pg.id_pengembalian,
        pg.nomor_invoice,
        pg.tanggal_kembali,
        pg.status_pembayaran,
        pg.total_denda,
        u.nama
    FROM pengembalian pg
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    JOIN users u ON p.id_user = u.id_user
    $where
    ORDER BY pg.created_at DESC
");

if (!$data) {
    die(mysqli_error($conn));
}
?>

<?php include '../partials/header.php'; ?>

<body>
<div class="d-flex">

<?php include '../partials/sidebar_petugas.php'; ?>

<div class="content flex-grow-1 p-4">

<h3 class="mb-4">Laporan Peminjaman Kendaraan</h3>

<!-- 🔍 FILTER -->
<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="form-control">
    </div>
    <div class="col-auto">
        <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="form-control">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary">Filter</button>
        <a href="laporan.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<?php if (mysqli_num_rows($data) > 0): ?>
<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">

<thead class="table-light">
<tr>
    <th>Invoice</th>
    <th>Peminjam</th>
    <th>Tanggal Kembali</th>
    <th>Total Denda</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= htmlspecialchars($r['nomor_invoice'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
    <td>
    <?= !empty($r['tanggal_kembali']) 
            ? date('d M Y', strtotime($r['tanggal_kembali'])) 
            : '-' ?>
    </td>
    <td class="text-danger fw-semibold">
        Rp <?= number_format($r['total_denda'] ?? 0, 0, ',', '.') ?>
    </td>

    <td>
        <?= $r['status_pembayaran'] == 'lunas'
            ? '<span class="badge bg-success">Lunas</span>'
            : '<span class="badge bg-danger">Belum Lunas</span>' ?>
    </td>

    <td>
        <a href="invoice_pdf.php?id=<?= $r['id_pengembalian'] ?>"
           target="_blank"
           class="btn btn-sm btn-primary">
            Invoice
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<?php else: ?>
<div class="alert alert-info">
    Tidak ada data pada rentang ini.
</div>
<?php endif; ?>

</div>
</div>

</body>
</html>