<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');
include '../config/database.php';

$id_user = $_SESSION['id_user'] ?? 0;

if (!$id_user) {
    header("Location: ../auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK DENDA
|--------------------------------------------------------------------------
*/
$cekDenda = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM denda dn
    JOIN pengembalian pg ON dn.id_pengembalian = pg.id_pengembalian
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    WHERE p.id_user = $id_user
    AND dn.status = 'belum_dibayar'
");

$dataDenda = mysqli_fetch_assoc($cekDenda);
$punyaDenda = $dataDenda['total'] > 0;


/*
|--------------------------------------------------------------------------
| PROSES AJUKAN
|--------------------------------------------------------------------------
*/
if (isset($_POST['ajukan']) && !$punyaDenda) {

    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? null;
    $tanggal_kembali = $_POST['tanggal_kembali'] ?? null;
    $jumlah = $_POST['jumlah'] ?? [];
    $dipilih = $_POST['pilih'] ?? [];

    if (empty($dipilih)) {
        $_SESSION['error'] = "Pilih minimal 1 kendaraan.";
        header("Location: ajukan.php");
        exit;
    }

    if (
        !$tanggal_pinjam ||
        !$tanggal_kembali ||
        strtotime($tanggal_pinjam) < strtotime(date('Y-m-d')) ||
        strtotime($tanggal_kembali) <= strtotime($tanggal_pinjam)
    ) {
        $_SESSION['error'] = "Tanggal tidak valid.";
        header("Location: ajukan.php");
        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        $result = mysqli_query($conn, "
            INSERT INTO peminjaman
            (id_user, tanggal_pinjam, tanggal_jatuh_tempo, status, created_at)
            VALUES (
                $id_user,
                '$tanggal_pinjam',
                '$tanggal_kembali',
                'menunggu',
                NOW()
            )
        ");

        if (!$result) {
            throw new Exception("Gagal insert peminjaman: " . mysqli_error($conn));
        }

        $id_peminjaman = mysqli_insert_id($conn);

        foreach ($dipilih as $id_kendaraan) {

            $id_kendaraan = (int)$id_kendaraan;
            $qty = (int)($jumlah[$id_kendaraan] ?? 0);

            if ($qty <= 0) {
                throw new Exception("Jumlah tidak valid.");
            }

            $cek = mysqli_query($conn, "
                SELECT stok
                FROM kendaraan
                WHERE id_kendaraan = $id_kendaraan
                FOR UPDATE
            ");

            $k = mysqli_fetch_assoc($cek);

            if ($k['stok'] < $qty) {
                throw new Exception("Stok tidak cukup.");
            }

            mysqli_query($conn, "
                INSERT INTO detail_peminjaman
                (id_peminjaman, id_kendaraan, jumlah)
                VALUES ($id_peminjaman, $id_kendaraan, $qty)
            ");
        }

        mysqli_commit($conn);
        $_SESSION['success'] = "Pengajuan berhasil.";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: dashboard.php");
    exit;
}
?>

<?php include "../partials/header.php"; ?>

<body>

<div class="d-flex">

    <!-- ✅ SIDEBAR -->
    <?php include "../partials/sidebar_peminjam.php"; ?>

    <!-- ✅ CONTENT -->
    <div class="container-fluid p-4">

        <h3 class="mb-4">Ajukan Peminjaman Kendaraan</h3>

        <?php if ($punyaDenda): ?>
        <div class="alert alert-danger">
            Anda masih memiliki denda yang belum dibayar.
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (!$punyaDenda): ?>

        <form method="post">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Tanggal Kembali</label>
                <input type="date" name="tanggal_kembali" class="form-control" required>
            </div>
        </div>

        <div class="card">
        <div class="card-body">

        <?php
        $q = mysqli_query($conn, "SELECT * FROM kendaraan WHERE stok > 0 ORDER BY nama_kendaraan ASC");
        ?>

        <table class="table table-bordered">
        <thead class="table-light">
        <tr>
            <th>Pilih</th>
            <th>Nama Kendaraan</th>
            <th>Stok</th>
            <th>Jumlah</th>
        </tr>
        </thead>
        <tbody>

        <?php while ($k = mysqli_fetch_assoc($q)): ?>
        <tr>
            <td>
                <input type="checkbox" name="pilih[]" value="<?= $k['id_kendaraan'] ?>">
            </td>
            <td><?= htmlspecialchars($k['nama_kendaraan']) ?></td>
            <td><?= $k['stok'] ?></td>
            <td>
                <input type="number"
                       name="jumlah[<?= $k['id_kendaraan'] ?>]"
                       min="0"
                       max="<?= $k['stok'] ?>"
                       class="form-control"
                       value="0">
            </td>
        </tr>
        <?php endwhile; ?>

        </tbody>
        </table>

        </div>
        </div>

        <button type="submit" name="ajukan" class="btn btn-primary mt-3">
            Ajukan Peminjaman
        </button>

        </form>

        <?php endif; ?>

    </div>
</div>

</body>
</html>