<?php
include '../middleware/auth.php';
cekLogin();
cekRole('petugas');
include '../config/database.php';

require '../helpers/logger.php';

if (isset($_POST['approve']) || isset($_POST['reject'])) {

    $id = (int) $_POST['id_peminjaman'];
    $petugas = $_SESSION['id_user'];

    mysqli_begin_transaction($conn);

    try {

        // Lock peminjaman
        $q_peminjaman = mysqli_query($conn, "
            SELECT status
            FROM peminjaman
            WHERE id_peminjaman = $id
            FOR UPDATE
        ");

        if (mysqli_num_rows($q_peminjaman) !== 1) {
            throw new Exception("Data tidak ditemukan.");
        }

        $p = mysqli_fetch_assoc($q_peminjaman);

        if ($p['status'] !== 'menunggu') {
            throw new Exception("Sudah diproses.");
        }

        if (isset($_POST['approve'])) {

            // Lock & cek semua detail
            $detail = mysqli_query($conn, "
                SELECT d.id_kendaraan, d.jumlah, k.stok
                FROM detail_peminjaman d
                JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
                WHERE d.id_peminjaman = $id
                FOR UPDATE
            ");

            if (mysqli_num_rows($detail) == 0) {
                throw new Exception("Detail tidak ditemukan.");
            }

            while ($d = mysqli_fetch_assoc($detail)) {

                if ($d['stok'] < $d['jumlah']) {
                    throw new Exception("Stok tidak mencukupi.");
                }

                mysqli_query($conn, "
                    UPDATE kendaraan
                    SET stok = stok - {$d['jumlah']}
                    WHERE id_kendaraan = {$d['id_kendaraan']}
                ");
            }

            mysqli_query($conn, "
                UPDATE peminjaman
                SET status='disetujui', approved_by=$petugas
                WHERE id_peminjaman=$id
            ");

            logAktivitas(
                $conn,
                $petugas,
                "Approve peminjaman ID $id"
            );

        } else {

            mysqli_query($conn, "
                UPDATE peminjaman
                SET status='ditolak'
                WHERE id_peminjaman=$id
            ");

            logAktivitas(
                $conn,
                $petugas,
                "Reject peminjaman ID $id"
            );
        }

        mysqli_commit($conn);

    } catch (Exception $e) {

        mysqli_rollback($conn);
    }

    header("Location: approval.php");
    exit;
}



// ===================== QUERY UTAMA =====================
// Ambil semua peminjaman menunggu
$q = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        u.nama AS peminjam,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE p.status='menunggu'
    GROUP BY p.id_peminjaman
    ORDER BY p.tanggal_pinjam ASC
");

if (!$q) {
    die("Query peminjaman gagal: " . mysqli_error($conn));
}
?>

<?php include '../partials/header.php'; ?>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <!-- Tombol Kembali -->
            <div class="mb-3">
                <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
            </div>

            <h3 class="mb-4">Approval Peminjaman Kendaraan</h3>

            <?php if(mysqli_num_rows($q) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Peminjam</th>
                            <th>Kendaraan</th>
                            <th>Tanggal Pinjam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['peminjam']) ?></td>
                            <td><?= htmlspecialchars($r['daftar_kendaraan']) ?></td>
                            <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                            <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_peminjaman" value="<?= $r['id_peminjaman'] ?>">
                                <button type="submit" name="approve"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Setujui peminjaman?')">
                                    Approve
                                </button>
                            </form>

                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_peminjaman" value="<?= $r['id_peminjaman'] ?>">
                                <button type="submit" name="reject"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tolak peminjaman?')">
                                    Tolak
                                </button>
                            </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info">Tidak ada peminjaman yang menunggu approval.</div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
