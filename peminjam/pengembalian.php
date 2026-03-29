<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');
include '../config/database.php';

$id_user = $_SESSION['id_user'];

// =============================
// AJUKAN PENGEMBALIAN
// =============================
if (isset($_GET['kembali'])) {
    $id = (int)$_GET['kembali'];

    mysqli_query($conn, "
        UPDATE peminjaman 
        SET status = 'menunggu_kembali', tanggal_pengembalian = CURDATE()
        WHERE id_peminjaman = '$id'
        AND id_user = '$id_user'
        AND status = 'disetujui'
    ");

    $_SESSION['success'] = "Pengembalian berhasil diajukan.";
    header("Location: pengembalian.php");
    exit;
}

// =============================
// DATA SEDANG DIPINJAM
// =============================
$data = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
    FROM peminjaman p
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE p.id_user = '$id_user'
    AND p.status = 'disetujui'
    GROUP BY p.id_peminjaman
");
?>

<?php include "../partials/header.php"; ?>
<body>
<div class="container mt-5">

<a href="dashboard.php" class="btn btn-secondary mb-3">← Dashboard</a>

<h3 class="mb-4">Pengembalian Kendaraan</h3>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<?php if (mysqli_num_rows($data) > 0): ?>
<div class="table-responsive">
<table class="table table-bordered align-middle">
<thead class="table-light">
<tr>
    <th>No</th>
    <th>Kendaraan</th>
    <th>Tanggal Pinjam</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; while($r=mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['daftar_kendaraan']) ?></td>
    <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
    <td>
        <a href="?kembali=<?= $r['id_peminjaman'] ?>"
           onclick="return confirm('Ajukan pengembalian kendaraan ini?')"
           class="btn btn-warning btn-sm">
           Ajukan Pengembalian
        </a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">
    Tidak ada kendaraan yang sedang dipinjam.
</div>
<?php endif; ?>

</div>
</body>
</html>
