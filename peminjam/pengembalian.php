<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');

include '../config/database.php';
require '../helpers/logger.php';

$id_user = (int) $_SESSION['id_user'];

// =============================
// AJUKAN PENGEMBALIAN
// =============================
if (isset($_GET['kembali'])) {

    $id = (int) $_GET['kembali'];

    mysqli_begin_transaction($conn);

    try {

        // 🔍 Cek apakah peminjaman valid
        $cek = mysqli_query($conn, "
            SELECT * FROM peminjaman 
            WHERE id_peminjaman = $id
            AND id_user = $id_user
            AND status = 'disetujui'
        ");

        if (mysqli_num_rows($cek) == 0) {
            throw new Exception("Data tidak valid atau sudah diproses.");
        }

        // 🔍 Cek apakah sudah pernah diajukan pengembalian
        $cekPengembalian = mysqli_query($conn, "
            SELECT * FROM pengembalian 
            WHERE id_peminjaman = $id
        ");

        if (mysqli_num_rows($cekPengembalian) > 0) {
            throw new Exception("Pengembalian sudah diajukan sebelumnya.");
        }

        // 1. Simpan ke tabel pengembalian
        mysqli_query($conn, "
            INSERT INTO pengembalian (id_peminjaman, tanggal_kembali)
            VALUES ($id, CURDATE())
        ");

        // 2. Update status peminjaman
        mysqli_query($conn, "
            UPDATE peminjaman 
            SET status = 'menunggu_kembali'
            WHERE id_peminjaman = $id
        ");

        // LOG
        logAktivitas($conn, $id_user, "Mengajukan pengembalian ID $id");

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengembalian berhasil diajukan.";

    } catch (Exception $e) {

        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

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
    WHERE p.id_user = $id_user
    AND p.status = 'disetujui'
    GROUP BY p.id_peminjaman
");
?>

<?php include "../partials/header.php"; ?>

<body>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include "../partials/sidebar_peminjam.php"; ?>

    <!-- CONTENT -->
    <div class="container-fluid p-4">

        <h3 class="mb-4">Pengembalian Kendaraan</h3>

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

        <?php if ($data && mysqli_num_rows($data) > 0): ?>
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
</div>

</body>
</html>