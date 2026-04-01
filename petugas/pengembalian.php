<?php
require '../middleware/auth.php';
cekLogin();
cekRole('petugas');

require '../config/database.php';
require '../helpers/logger.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_petugas = $_SESSION['id_user'];
$DENDA_PER_HARI = 50000;

/*
|--------------------------------------------------------------------------
| GENERATE INVOICE
|--------------------------------------------------------------------------
*/
function generateInvoiceNumber($conn)
{
    $tahun = date('Y');

    $q = mysqli_query($conn, "
        SELECT nomor_invoice 
        FROM pengembalian
        WHERE YEAR(created_at) = '$tahun'
        ORDER BY id_pengembalian DESC
        LIMIT 1
    ");

    $lastNumber = 0;

    if ($q && mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);

        if (!empty($data['nomor_invoice'])) {
            $lastNumber = (int) substr($data['nomor_invoice'], -4);
        }
    }

    $newNumber = $lastNumber + 1;

    return "INV-$tahun-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/*
|--------------------------------------------------------------------------
| VERIFIKASI
|--------------------------------------------------------------------------
*/
if (isset($_POST['verifikasi'])) {

    $id = (int) $_POST['id_peminjaman'];
    $denda = isset($_POST['denda_manual']) ? (int) $_POST['denda_manual'] : 0;
    $ket = $_POST['keterangan'] ?? '';

    mysqli_begin_transaction($conn);

    try {

        // VALIDASI DATA + LOCK
        $q = mysqli_query($conn, "
            SELECT *
            FROM peminjaman
            WHERE id_peminjaman = $id
            AND status = 'menunggu_kembali'
            FOR UPDATE
        ");

        if (mysqli_num_rows($q) !== 1) {
            throw new Exception("Data tidak valid.");
        }

        $p = mysqli_fetch_assoc($q);

        $nomor_invoice = generateInvoiceNumber($conn);

        // INSERT PENGEMBALIAN
        mysqli_query($conn, "
            INSERT INTO pengembalian
            (nomor_invoice, id_peminjaman, tanggal_kembali, kondisi_kendaraan, catatan, status, diperiksa_oleh, created_at)
            VALUES (
                '$nomor_invoice',
                $id,
                CURDATE(),
                'baik',
                '$ket',
                'disetujui',
                $id_petugas,
                NOW()
            )
        ");

        $id_pengembalian = mysqli_insert_id($conn);

        // UPDATE STOK KENDARAAN
        $detail = mysqli_query($conn, "
            SELECT *
            FROM detail_peminjaman
            WHERE id_peminjaman = $id
        ");

        while ($d = mysqli_fetch_assoc($detail)) {

            mysqli_query($conn, "
                UPDATE kendaraan
                SET stok = stok + {$d['jumlah']}
                WHERE id_kendaraan = {$d['id_kendaraan']}
            ");
        }

        // UPDATE PEMINJAMAN
        mysqli_query($conn, "
            UPDATE peminjaman
            SET 
                status = 'dikembalikan'
            WHERE id_peminjaman = $id
        ");

        // UPDATE TOTAL DENDA DI PENGEMBALIAN
        $status = $denda > 0 ? 'belum_dibayar' : 'lunas';

        mysqli_query($conn, "
            UPDATE pengembalian
            SET total_denda = $denda,
                status_pembayaran = '$status'
            WHERE id_pengembalian = $id_pengembalian
        ");

        logAktivitas($conn, $id_petugas, "Verifikasi pengembalian ID $id");

        mysqli_commit($conn);

        echo "<script>
            window.open('invoice_pdf.php?id=$id_pengembalian','_blank');
            window.location.href='pengembalian.php';
        </script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/
$data = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        u.nama,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS kendaraan
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE p.status='menunggu_kembali'
    GROUP BY p.id_peminjaman
");
?>

<?php include "../partials/header.php"; ?>

<body>
    <div class="d-flex">

        <?php include '../partials/sidebar_petugas.php'; ?>

        <div class="flex-grow-1 p-4" style="min-height:100vh;">
            <h4 class="fw-bold mb-3">Verifikasi Pengembalian</h4>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">

                    <table class="table table-hover align-middle mb-0">
                        <tr style="background:#1e293b; color:white;" class="text-center">
                            <th>Peminjam</th>
                            <th>Kendaraan</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th style="width:120px;">Terlambat</th>
                            <th style="width:150px;">Denda Telat</th>
                            <th style="width:300px;">Denda Kerusakan</th>
                        </tr>

                        <?php while ($r = mysqli_fetch_assoc($data)):

                            $today = new DateTime();

                            $batas = !empty($r['tanggal_jatuh_tempo'])
                                ? new DateTime($r['tanggal_jatuh_tempo'])
                                : (new DateTime($r['tanggal_pinjam']))->modify('+3 days');

                            $terlambat = ($today > $batas)
                                ? $batas->diff($today)->days
                                : 0;

                            $estimasi = $terlambat * $DENDA_PER_HARI;
                        ?>

                            <tr>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= htmlspecialchars($r['kendaraan']) ?></td>
                                <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                                <td><?= $batas->format('d M Y') ?></td>
                                <td class="text-center <?= $terlambat > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                    <?= $terlambat ?> hari
                                </td>
                                <td class="text-danger fw-bold">Rp <?= number_format($estimasi, 0, ',', '.') ?></td>
                                <td>
                                    <form method="post" class="bg-white border-0 rounded-3 p-3 shadow-sm">

                                        <input type="hidden" name="id_peminjaman" value="<?= $r['id_peminjaman'] ?>">

                                        <div class="mb-2">
                                            <input type="number" name="denda_manual"
                                                class="form-control form-control-sm"
                                                placeholder="Masukkan denda (Rp)">
                                        </div>

                                        <div class="mb-2">
                                            <input type="text" name="keterangan"
                                                class="form-control form-control-sm"
                                                placeholder="Keterangan (opsional)">
                                        </div>

                                        <button type="submit" name="verifikasi"
                                            class="btn btn-success btn-sm w-100">
                                            Verifikasi
                                        </button>

                                    </form>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    </table>

                </div>
            </div>

        </div>
    </div>
</body>
</html>