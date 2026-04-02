<?php
require '../middleware/auth.php';
cekLogin();
cekRole('petugas');

require '../config/database.php';
require '../helpers/logger.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id_pengembalian = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| AMBIL DATA INVOICE
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        u.nama AS nama_peminjam,
        peng.id_pengembalian,
        peng.tanggal_kembali,
        peng.status AS status_pengembalian,
        peng.nomor_invoice,
        peng.total_denda,
        peng.status_pembayaran,
        peng.created_at
    FROM pengembalian peng
    JOIN peminjaman p ON peng.id_peminjaman = p.id_peminjaman
    JOIN users u ON p.id_user = u.id_user
    WHERE peng.id_pengembalian = ?
");

$stmt->bind_param("i", $id_pengembalian);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data tidak ditemukan.");
}

$data = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| VALIDASI STATUS
|--------------------------------------------------------------------------
*/
if ($data['status_pengembalian'] !== 'disetujui') {
    die("Invoice belum tersedia.");
}

/*
|--------------------------------------------------------------------------
| LOG
|--------------------------------------------------------------------------
*/
logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Melihat invoice {$data['nomor_invoice']}",
    $data['id_pengembalian']
);

/*
|--------------------------------------------------------------------------
| AMBIL KENDARAAN
|--------------------------------------------------------------------------
*/
$kendaraan_stmt = $conn->prepare("
    SELECT k.nama_kendaraan, d.jumlah
    FROM detail_peminjaman d
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE d.id_peminjaman = ?
");

$kendaraan_stmt->bind_param("i", $data['id_peminjaman']);
$kendaraan_stmt->execute();
$kendaraan_result = $kendaraan_stmt->get_result();

$kendaraan_list = [];
while ($k = $kendaraan_result->fetch_assoc()) {
    $kendaraan_list[] = $k['nama_kendaraan'] . " ({$k['jumlah']})";
}

/*
|--------------------------------------------------------------------------
| OPTIONAL: DETAIL DENDA (kalau tabel denda dipakai)
|--------------------------------------------------------------------------
*/
$denda_list = [];

$denda_stmt = $conn->prepare("
    SELECT jenis_denda, jumlah, keterangan, status
    FROM denda
    WHERE id_pengembalian = ?
");

$denda_stmt->bind_param("i", $data['id_pengembalian']);
$denda_stmt->execute();
$denda_result = $denda_stmt->get_result();

while ($d = $denda_result->fetch_assoc()) {
    $denda_list[] = $d;
}

/*
|--------------------------------------------------------------------------
| STATUS PEMBAYARAN
|--------------------------------------------------------------------------
*/
$semua_lunas = ($data['status_pembayaran'] === 'lunas');

$tanggal_cetak = date("d-m-Y H:i");
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($data['nomor_invoice']) ?></title>
<style>
body { font-family: Arial; padding: 40px; }
.invoice-box { max-width: 800px; margin: auto; }
.header { display: flex; justify-content: space-between; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
table, th, td { border: 1px solid #ccc; }
th, td { padding: 10px; }
.total { font-weight: bold; font-size: 18px; }
.text-right { text-align: right; }
.print-btn { margin-top: 20px; }
@media print { .print-btn { display: none; } }
</style>
</head>

<body>

<div class="invoice-box">

<div class="header">

<p>
Status:
<?= $semua_lunas
    ? '<span style="color:green;font-weight:bold;">LUNAS</span>'
    : '<span style="color:red;font-weight:bold;">BELUM LUNAS</span>' ?>
</p>

<div>
<h2>INVOICE DENDA</h2>
<p><?= htmlspecialchars($data['nomor_invoice']) ?></p>
</div>

<div style="text-align:right;">
<strong>Dicetak:</strong><br>
<?= $tanggal_cetak ?>
</div>

</div>

<hr>

<p><strong>Nama:</strong> <?= htmlspecialchars($data['nama_peminjam']) ?></p>

<p><strong>Kendaraan:</strong><br>
<?= htmlspecialchars(implode(', ', $kendaraan_list)) ?>
</p>

<p><strong>Tanggal Pinjam:</strong> <?= date('d M Y', strtotime($data['tanggal_pinjam'])) ?></p>
<p><strong>Tanggal Kembali:</strong> <?= date('d M Y', strtotime($data['tanggal_kembali'])) ?></p>

<table>
<thead>
<tr>
<th>Jenis</th>
<th>Keterangan</th>
<th>Status</th>
<th class="text-right">Jumlah</th>
</tr>
</thead>

<tbody>

<?php if (!empty($denda_list)): ?>
<?php foreach ($denda_list as $d): ?>
<tr>
<td><?= ucfirst($d['jenis_denda']) ?></td>
<td><?= htmlspecialchars($d['keterangan'] ?: '-') ?></td>
<td>
<?= $d['status'] == 'dibayar'
    ? '<span style="color:green;">LUNAS</span>'
    : '<span style="color:red;">BELUM</span>' ?>
</td>
<td class="text-right">
Rp <?= number_format($d['jumlah'], 0, ',', '.') ?>
</td>
</tr>
<?php endforeach; ?>

<?php else: ?>
<tr>
<td colspan="4" style="text-align:center;">Tidak ada rincian denda</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<table style="margin-top:20px;">
<tr>
<td class="total">Total</td>
<td class="text-right total">
Rp <?= number_format($data['total_denda'], 0, ',', '.') ?>
</td>
</tr>
</table>

<br><br>

<p style="text-align:right;">
Petugas,<br><br><br>
_________________________
</p>

<div class="print-btn">
<button onclick="window.print()">Cetak</button>
</div>

</div>

</body>
</html>