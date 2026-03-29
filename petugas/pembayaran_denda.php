<?php
require '../middleware/auth.php';
cekLogin();
cekRole('petugas');
require '../config/database.php';
require '../helpers/logger.php';

$id_petugas = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| PROSES PEMBAYARAN DENDA
|--------------------------------------------------------------------------
*/
if (isset($_POST['bayar'])) {

    $id_denda = (int) $_POST['id_denda'];

    mysqli_begin_transaction($conn);

    try {

        // Lock denda agar tidak bisa dibayar dua kali
        $getPengembalian = mysqli_query($conn, "
            SELECT id_pengembalian 
            FROM denda 
            WHERE id_denda = $id_denda
            AND status = 'belum_dibayar'
            FOR UPDATE
        ");

        if (mysqli_num_rows($getPengembalian) !== 1) {
            throw new Exception("Denda tidak valid atau sudah dibayar.");
        }

        $row = mysqli_fetch_assoc($getPengembalian);
        $id_pengembalian = $row['id_pengembalian'];

        // Update status denda
        mysqli_query($conn, "
            UPDATE denda
            SET status = 'dibayar'
            WHERE id_denda = $id_denda
        ");

        logAktivitas(
            $conn,
            $id_petugas,
            "Membayar denda ID $id_denda"
        );

        // Cek apakah masih ada denda belum dibayar
        $cek = mysqli_query($conn, "
            SELECT COUNT(*) as total
            FROM denda
            WHERE id_pengembalian = $id_pengembalian
            AND status = 'belum_dibayar'
        ");

        $sisa = mysqli_fetch_assoc($cek)['total'];

        // Jika semua lunas → update status_pembayaran
        if ($sisa == 0) {

            mysqli_query($conn, "
                UPDATE pengembalian
                SET status_pembayaran = 'lunas'
                WHERE id_pengembalian = $id_pengembalian
            ");

            logAktivitas(
                $conn,
                $id_petugas,
                "Pengembalian ID $id_pengembalian dinyatakan LUNAS"
            );
        }

        mysqli_commit($conn);

        $_SESSION['success'] = "Pembayaran berhasil diproses.";

    } catch (Exception $e) {

        mysqli_rollback($conn);
        $_SESSION['error'] = "Terjadi kesalahan sistem.";
    }

    header("Location: pembayaran_denda.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA DENDA BELUM DIBAYAR
|--------------------------------------------------------------------------
*/
$data = mysqli_query($conn, "
    SELECT 
        dn.id_denda,
        dn.id_pengembalian,
        u.nama,
        k.nama_kendaraan,
        dn.jenis_denda,
        dn.jumlah,
        dn.keterangan,
        dn.created_at
    FROM denda dn
    JOIN detail_peminjaman dp ON dn.id_detail = dp.id_detail
    JOIN kendaraan k ON dp.id_kendaraan = k.id_kendaraan
    JOIN pengembalian pg ON dn.id_pengembalian = pg.id_pengembalian
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    JOIN users u ON p.id_user = u.id_user
    WHERE dn.status = 'belum_dibayar'
    ORDER BY dn.created_at DESC
");

?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container mt-5">

<a href="dashboard.php" class="btn btn-secondary mb-3">← Dashboard</a>

<h3 class="mb-4">Manajemen Pembayaran Denda</h3>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<?php if (mysqli_num_rows($data) > 0): ?>
<div class="table-responsive">
<table class="table table-hover table-bordered align-middle">
<thead class="table-dark">
<tr>
    <th>Peminjam</th>
    <th>Kendaraan</th>
    <th>Jenis</th>
    <th>Jumlah</th>
    <th>Keterangan</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php while($r = mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= htmlspecialchars($r['nama']) ?></td>
<td><?= htmlspecialchars($r['nama_kendaraan']) ?></td>
<td><?= ucfirst($r['jenis_denda']) ?></td>
<td class="text-danger fw-bold">
    Rp <?= number_format($r['jumlah']) ?>
</td>
<td><?= htmlspecialchars($r['keterangan'] ?: '-') ?></td>
<td><?= date('d-m-Y', strtotime($r['created_at'])) ?></td>
<td>
<form method="post">
    <input type="hidden" name="id_denda" value="<?= $r['id_denda'] ?>">
    <button type="submit"
            name="bayar"
            onclick="return confirm('Konfirmasi pembayaran denda ini?')"
            class="btn btn-success btn-sm">
        Bayar
    </button>
</form>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-success">
    Semua denda sudah lunas.
</div>
<?php endif; ?>

</div>
</body>
</html>
