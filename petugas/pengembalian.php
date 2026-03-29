<?php
require '../middleware/auth.php';
cekLogin();
cekRole('petugas');

require '../config/database.php';
require '../helpers/logger.php';

$id_petugas = $_SESSION['id_user'];

$MAX_HARI = 3;
$DENDA_PER_HARI = 50000;

/*
|--------------------------------------------------------------------------
| GENERATE NOMOR INVOICE
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

    if (!$q) {
        throw new Exception(mysqli_error($conn));
    }

    if (mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
        $lastNumber = (int) substr($data['nomor_invoice'], -4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return "INV-$tahun-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/*
|--------------------------------------------------------------------------
| PROSES VERIFIKASI
|--------------------------------------------------------------------------
*/
if (isset($_POST['verifikasi'])) {

    $id_peminjaman = (int) $_POST['id_peminjaman'];
    $denda_rusak   = (int) $_POST['denda_rusak'];
    $keterangan    = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    mysqli_begin_transaction($conn);

    try {

        // Lock peminjaman
        $q = mysqli_query($conn, "
            SELECT *
            FROM peminjaman
            WHERE id_peminjaman = $id_peminjaman
            AND status = 'menunggu_kembali'
            FOR UPDATE
        ");

        if (mysqli_num_rows($q) !== 1) {
            throw new Exception("Data tidak valid atau sudah diverifikasi.");
        }

        $p = mysqli_fetch_assoc($q);

        $tgl_pinjam  = new DateTime($p['tanggal_pinjam']);
        $tgl_sekarang = new DateTime();

        $lama = $tgl_pinjam->diff($tgl_sekarang)->days;
        $terlambat = max(0, $lama - $MAX_HARI);
        $denda_terlambat = $terlambat * $DENDA_PER_HARI;

        $nomor_invoice = generateInvoiceNumber($conn);

        // Insert pengembalian
        $insert_pengembalian = mysqli_query($conn, "
            INSERT INTO pengembalian
            (nomor_invoice, id_peminjaman, tanggal_kembali, kondisi_kendaraan, catatan, status, diperiksa_oleh, created_at)
            VALUES (
                '$nomor_invoice',
                $id_peminjaman,
                CURDATE(),
                '" . ($denda_rusak > 0 ? 'rusak' : 'baik') . "',
                '$keterangan',
                'disetujui',
                $id_petugas,
                NOW()
            )
        ");

        if (!$insert_pengembalian) {
            throw new Exception(mysqli_error($conn));
        }

        $id_pengembalian = mysqli_insert_id($conn);

        $total_denda = 0;

        /*
        |----------------------------------------
        | 1️⃣ DENDA TERLAMBAT (SEKALI)
        |----------------------------------------
        */
        if ($denda_terlambat > 0) {

            $insert_terlambat = mysqli_query($conn, "
                INSERT INTO denda
                (id_pengembalian, id_detail, jenis_denda, jumlah, keterangan, status)
                VALUES (
                    $id_pengembalian,
                    NULL,
                    'terlambat',
                    $denda_terlambat,
                    'Terlambat $terlambat hari',
                    'belum_dibayar'
                )
            ");

            if (!$insert_terlambat) {
                throw new Exception(mysqli_error($conn));
            }

            $total_denda += $denda_terlambat;
        }

        /*
        |----------------------------------------
        | 2️⃣ AMBIL DETAIL KENDARAAN
        |----------------------------------------
        */
        $detail = mysqli_query($conn, "
            SELECT *
            FROM detail_peminjaman
            WHERE id_peminjaman = $id_peminjaman
        ");

        if (!$detail) {
            throw new Exception(mysqli_error($conn));
        }

        while ($d = mysqli_fetch_assoc($detail)) {

            $id_detail = $d['id_detail'];

            /*
            |----------------------------------------
            | DENDA KERUSAKAN
            |----------------------------------------
            */
            if ($denda_rusak > 0) {

                $insert_rusak = mysqli_query($conn, "
                    INSERT INTO denda
                    (id_pengembalian, id_detail, jenis_denda, jumlah, keterangan, status)
                    VALUES (
                        $id_pengembalian,
                        $id_detail,
                        'kerusakan',
                        $denda_rusak,
                        '$keterangan',
                        'belum_dibayar'
                    )
                ");

                if (!$insert_rusak) {
                    throw new Exception(mysqli_error($conn));
                }

                $total_denda += $denda_rusak;

                // hanya 1 kendaraan kena denda rusak
                $denda_rusak = 0;
            }

            /*
            |----------------------------------------
            | UPDATE STOK KENDARAAN
            |----------------------------------------
            */
            mysqli_query($conn, "
                UPDATE kendaraan
                SET stok = stok + {$d['jumlah']}
                WHERE id_kendaraan = {$d['id_kendaraan']}
            ");
        }

        /*
        |----------------------------------------
        | UPDATE STATUS PEMINJAMAN
        |----------------------------------------
        */
        mysqli_query($conn, "
            UPDATE peminjaman
            SET status = 'dikembalikan'
            WHERE id_peminjaman = $id_peminjaman
        ");

        /*
        |----------------------------------------
        | UPDATE TOTAL DENDA
        |----------------------------------------
        */
        $status_pembayaran = ($total_denda > 0) ? 'belum_dibayar' : 'lunas';

        mysqli_query($conn, "
            UPDATE pengembalian
            SET total_denda = $total_denda,
                status_pembayaran = '$status_pembayaran'
            WHERE id_pengembalian = $id_pengembalian
        ");

        /*
        |----------------------------------------
        | LOG AKTIVITAS
        |----------------------------------------
        */
        logAktivitas(
            $conn,
            $id_petugas,
            "Verifikasi pengembalian ID $id_peminjaman - Invoice $nomor_invoice"
        );

        mysqli_commit($conn);

        echo "<script>
                window.open('invoice_pdf.php?id=$id_pengembalian', '_blank');
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
| DATA MENUNGGU VERIFIKASI
|--------------------------------------------------------------------------
*/
$data = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        u.nama,
        p.tanggal_pinjam,
        p.tanggal_pengembalian,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS daftar_kendaraan
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    WHERE p.status = 'menunggu_kembali'
    GROUP BY p.id_peminjaman
    ORDER BY p.tanggal_pengembalian ASC
");
?>

<?php include "../partials/header.php"; ?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Verifikasi Pengembalian</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="card shadow-sm">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead class="table-light">
<tr>
    <th>Peminjam</th>
    <th>Kendaraan</th>
    <th>Tanggal Pinjam</th>
    <th>Rencana Kembali</th>
    <th>Denda Terlambat</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($data) > 0): ?>
<?php while($r = mysqli_fetch_assoc($data)): 

    $pinjam = new DateTime($r['tanggal_pinjam']);
    $now = new DateTime();
    $lama = $pinjam->diff($now)->days;
    $terlambat = max(0, $lama - $MAX_HARI);
    $estimasi = $terlambat * $DENDA_PER_HARI;
?>
<tr>
<td><?= htmlspecialchars($r['nama']) ?></td>
<td><?= htmlspecialchars($r['daftar_kendaraan']) ?></td>
<td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
<td><?= date('d M Y', strtotime($r['tanggal_pengembalian'])) ?></td>
<td class="text-danger fw-bold">Rp <?= number_format($estimasi) ?></td>
<td>
<form method="post" class="d-flex gap-2">
    <input type="hidden" name="id_peminjaman" value="<?= $r['id_peminjaman'] ?>">

    <input type="number" name="denda_rusak"
           class="form-control form-control-sm"
           placeholder="Denda rusak"
           min="0" value="0">

    <input type="text" name="keterangan"
           class="form-control form-control-sm"
           placeholder="Catatan">

    <button type="submit"
            name="verifikasi"
            class="btn btn-success btn-sm">
        Verifikasi
    </button>
</form>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="6" class="text-center py-4 text-muted">
    Tidak ada pengembalian menunggu verifikasi.
</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>
</div>

</div>

</body>
</html>