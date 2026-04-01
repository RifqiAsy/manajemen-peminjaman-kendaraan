<?php
include '../middleware/auth.php';
cekLogin();
cekRole('petugas');
include '../config/database.php';
require '../helpers/logger.php';

/*
|--------------------------------------------------------------------------
| PROSES APPROVAL / REJECT
|--------------------------------------------------------------------------
*/
if (isset($_POST['approve']) || isset($_POST['reject'])) {

    $id = (int) $_POST['id_peminjaman'];
    $petugas = $_SESSION['id_user'];

    mysqli_begin_transaction($conn);

    try {

        // Lock data peminjaman
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
            throw new Exception("Peminjaman sudah diproses.");
        }

        /*
        |----------------------------------------
        | APPROVE
        |----------------------------------------
        */
        if (isset($_POST['approve'])) {

            $detail = mysqli_query($conn, "
                SELECT d.id_kendaraan, d.jumlah, k.stok
                FROM detail_peminjaman d
                JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
                WHERE d.id_peminjaman = $id
                FOR UPDATE
            ");

            if (mysqli_num_rows($detail) == 0) {
                throw new Exception("Detail peminjaman tidak ditemukan.");
            }

            while ($d = mysqli_fetch_assoc($detail)) {

                if ($d['stok'] < $d['jumlah']) {
                    throw new Exception("Stok kendaraan tidak mencukupi.");
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

            logAktivitas($conn, $petugas, "Approve peminjaman ID $id");

        } 
        /*
        |----------------------------------------
        | REJECT
        |----------------------------------------
        */
        else {

            mysqli_query($conn, "
                UPDATE peminjaman
                SET status='ditolak'
                WHERE id_peminjaman=$id
            ");

            logAktivitas($conn, $petugas, "Reject peminjaman ID $id");
        }

        mysqli_commit($conn);

    } catch (Exception $e) {

        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: approval.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA MENUNGGU APPROVAL
|--------------------------------------------------------------------------
*/
$q = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam,
        u.nama AS peminjam,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS kendaraan
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE p.status='menunggu'
    GROUP BY p.id_peminjaman
    ORDER BY p.tanggal_pinjam ASC
");

if (!$q) {
    die("Query gagal: " . mysqli_error($conn));
}
?>

<?php include '../partials/header.php'; ?>

<body>
<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include '../partials/sidebar_petugas.php'; ?>

    <!-- CONTENT -->
    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Approval Peminjaman</h4>
        </div>

        <!-- ERROR -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- CARD -->
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <?php if(mysqli_num_rows($q) > 0): ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Kendaraan</th>
                                <th>Tanggal</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while($r = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['peminjam']) ?></td>
                            <td><?= htmlspecialchars($r['kendaraan']) ?></td>
                            <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                            <td>

                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="id_peminjaman" value="<?= $r['id_peminjaman'] ?>">

                                    <button type="submit" name="approve"
                                        class="btn btn-success btn-sm flex-fill"
                                        onclick="return confirm('Setujui peminjaman ini?')">
                                        ✔ Approve
                                    </button>

                                    <button type="submit" name="reject"
                                        class="btn btn-danger btn-sm flex-fill"
                                        onclick="return confirm('Tolak peminjaman ini?')">
                                        ✖ Tolak
                                    </button>
                                </form>

                            </td>
                        </tr>
                        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>

                <?php else: ?>

                <div class="text-center py-4 text-muted">
                    <h6 class="mb-1">Tidak ada data</h6>
                    <small>Semua peminjaman sudah diproses</small>
                </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
</body>
</html>