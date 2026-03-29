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

$query = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        u.nama AS nama_peminjam,
        peng.id_pengembalian,
        peng.tanggal_kembali,
        peng.status AS status_pengembalian,
        peng.nomor_invoice,
        peng.created_at
    FROM pengembalian peng
    JOIN peminjaman p ON peng.id_peminjaman = p.id_peminjaman
    JOIN users u ON p.id_user = u.id_user
    WHERE peng.id_pengembalian = $id_pengembalian
");

if (mysqli_num_rows($query) == 0) {
    die("Data tidak ditemukan.");
}

$data = mysqli_fetch_assoc($query);

/*
|--------------------------------------------------------------------------
| CEK STATUS PENGEMBALIAN
|--------------------------------------------------------------------------
*/

if ($data['status_pengembalian'] !== 'disetujui') {
    die("Invoice belum tersedia.");
}

/*
|--------------------------------------------------------------------------
| LOG AKTIVITAS
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
| AMBIL DATA KENDARAAN
|--------------------------------------------------------------------------
*/

$kendaraan_q = mysqli_query($conn, "
    SELECT k.nama_kendaraan
    FROM detail_peminjaman d
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE d.id_peminjaman = {$data['id_peminjaman']}
");

/*
|--------------------------------------------------------------------------
| AMBIL DATA DENDA
|--------------------------------------------------------------------------
*/

$denda_q = mysqli_query($conn, "
    SELECT jenis_denda, jumlah, keterangan, status
    FROM denda
    WHERE id_pengembalian = {$data['id_pengembalian']}
");

$total_denda = 0;
$denda_list = [];

while ($d = mysqli_fetch_assoc($denda_q)) {
    $total_denda += $d['jumlah'];
    $denda_list[] = $d;
}

/*
|--------------------------------------------------------------------------
| CEK STATUS PEMBAYARAN
|--------------------------------------------------------------------------
*/

$semua_lunas = true;

foreach ($denda_list as $d) {
    if ($d['status'] !== 'dibayar') {
        $semua_lunas = false;
        break;
    }
}

$tanggal_cetak = date("d-m-Y H:i");

?>
<!DOCTYPE html>
<html>
<head>
<title><?= $data['nomor_invoice'] ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    padding: 40px;
}

.invoice-box {
    max-width: 800px;
    margin: auto;
}

.header {
    display: flex;
    justify-content: space-between;
}

h2 {
    margin: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 10px;
}

.total {
    font-weight: bold;
    font-size: 18px;
}

.text-right {
    text-align: right;
}

.print-btn {
    margin-top: 20px;
}

@media print {
    .print-btn { display: none; }
}
</style>
</head>
<body>

<div class="invoice-box">

<div class="header">

<p>
Status Invoice:
<?= $semua_lunas
    ? '<span style="color:green;font-weight:bold;">LUNAS</span>'
    : '<span style="color:red;font-weight:bold;">BELUM LUNAS</span>' ?>
</p>

<div>
<h2>INVOICE DENDA</h2>
<p><?= $data['nomor_invoice'] ?></p>
</div>

<div style="text-align:right;">
<strong>Dicetak:</strong><br>
<?= $tanggal_cetak ?>
</div>

</div>

<hr>

<p><strong>Nama Peminjam:</strong> <?= htmlspecialchars($data['nama_peminjam']) ?></p>

<p>
<strong>Kendaraan:</strong>
<?php
$kendaraan_list = [];
while ($k = mysqli_fetch_assoc($kendaraan_q)) {
    $kendaraan_list[] = $k['nama_kendaraan'];
}
echo htmlspecialchars(implode(', ', $kendaraan_list));
?>
</p>

<p><strong>Tanggal Pinjam:</strong> <?= $data['tanggal_pinjam'] ?></p>
<p><strong>Tanggal Kembali:</strong> <?= $data['tanggal_kembali'] ?></p>

<table>
<thead>
<tr>
<th>Jenis Denda</th>
<th>Keterangan</th>
<th>Status</th>
<th class="text-right">Jumlah</th>
</tr>
</thead>

<tbody>

<?php if (count($denda_list) > 0): ?>

<?php foreach ($denda_list as $d): ?>

<tr>
<td><?= ucfirst($d['jenis_denda']) ?></td>

<td><?= htmlspecialchars($d['keterangan'] ?: '-') ?></td>

<td>
<?= $d['status'] == 'dibayar'
    ? '<span style="color:green;">LUNAS</span>'
    : '<span style="color:red;">BELUM LUNAS</span>' ?>
</td>

<td class="text-right">
Rp <?= number_format($d['jumlah']) ?>
</td>
</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="4" style="text-align:center;">
Tidak ada denda
</td>
</tr>

<?php endif; ?>

</tbody>
</table>

<table style="margin-top:20px;">
<tr>
<td class="total">Total Denda</td>
<td class="text-right total">
Rp <?= number_format($total_denda) ?>
</td>
</tr>
</table>

<br><br>

<p style="text-align:right;">
Petugas,<br><br><br>
_________________________
</p>

<div class="print-btn">
<button onclick="window.print()">Cetak Invoice</button>
</div>

</div>

</body>
</html>