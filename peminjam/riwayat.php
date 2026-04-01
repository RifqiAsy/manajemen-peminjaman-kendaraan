<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');

include '../config/database.php';

$id_user = (int) $_SESSION['id_user'];

// =============================
// AMBIL DATA RIWAYAT
// =============================
$data = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        MAX(p.tanggal_pinjam) AS tanggal_pinjam,
        MAX(pg.tanggal_kembali) AS tanggal_pengembalian,
        MAX(p.status) AS status,
        COUNT(d.id_kendaraan) AS jumlah_kendaraan,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
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
<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include "../partials/sidebar_peminjam.php"; ?>

    <!-- CONTENT -->
    <div class="p-4 w-100">
        <h3 class="mb-4">Riwayat Peminjaman</h3>

        <?php if ($data && mysqli_num_rows($data) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Kendaraan</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while($r = mysqli_fetch_assoc($data)): ?>

                <?php
                // =============================
                // NORMALISASI STATUS (ANTI BUG)
                // =============================
                $status_raw = $r['status'] ?? '';
                $status = strtolower(trim($status_raw));

                // mapping status
                $label = 'Unknown';
                $badge = 'dark';

                if ($status === 'menunggu') {
                    $label = 'Menunggu';
                    $badge = 'secondary';
                } elseif ($status === 'disetujui') {
                    $label = 'Disetujui';
                    $badge = 'primary';
                } elseif ($status === 'ditolak') {
                    $label = 'Ditolak';
                    $badge = 'danger';
                } elseif ($status === 'menunggu_kembali' || $status === 'menunggu kembali') {
                    $label = 'Menunggu Pengembalian';
                    $badge = 'warning';
                } elseif (
                    $status === 'selesai' || 
                    $status === 'dikembalikan' || 
                    $status === 'kembali'
                ) {
                    $label = 'Selesai';
                    $badge = 'success';
                }
                ?>

                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['daftar_kendaraan'] ?? '-') ?></td>
                    <td>
                        <?= $r['jumlah_kendaraan'] ?> kendaraan<br>
                    </td>
                    <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                    <td>
                        <?= $r['tanggal_pengembalian'] 
                            ? date('d M Y', strtotime($r['tanggal_pengembalian'])) 
                            : '-' ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $badge ?>">
                            <?= $label ?>
                        </span>

                        <!-- DEBUG (hapus nanti kalau sudah aman) -->
                        <?php if ($label === 'Unknown'): ?>
                            <br><small class="text-danger">
                                (<?= htmlspecialchars($status_raw) ?>)
                            </small>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- TRACKING DETAIL -->
                <tr>
                    <td colspan="5">
                        <small>
                            Tracking:
                            <?php
                            if ($status === 'menunggu') {
                                echo "Pengajuan sedang menunggu persetujuan admin.";
                            } elseif ($status === 'disetujui') {
                                echo "Peminjaman disetujui, kendaraan sedang digunakan.";
                            } elseif ($status === 'ditolak') {
                                echo "Pengajuan ditolak oleh admin.";
                            } elseif ($status === 'menunggu_kembali' || $status === 'menunggu kembali') {
                                echo "Pengembalian sedang menunggu konfirmasi admin.";
                            } elseif (
                                $status === 'selesai' || 
                                $status === 'dikembalikan' || 
                                $status === 'kembali'
                            ) {
                                echo "Transaksi telah selesai.";
                            } else {
                                echo "Status tidak dikenali sistem.";
                            }
                            ?>
                        </small>
                    </td>
                </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info">
                Belum ada riwayat peminjaman.
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>