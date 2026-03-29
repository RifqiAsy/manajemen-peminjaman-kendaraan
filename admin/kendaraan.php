<?php
include "../middleware/auth.php";
cekLogin();
cekRole('admin');
include "../config/database.php";
require '../helpers/logger.php';

/* ===============================
   TAMBAH KENDARAAN
================================ */
if (isset($_POST['tambah'])) {

    $nama = mysqli_real_escape_string($conn, $_POST['nama_kendaraan']);
    $id_kategori = (int)$_POST['id_kategori'];
    $stok = (int)$_POST['stok'];

    mysqli_query($conn, "
        INSERT INTO kendaraan (id_kategori, nama_kendaraan, stok)
        VALUES ($id_kategori, '$nama', $stok)
    ");
    
    logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Menambah kendaraan $nama"
);

    header("Location: kendaraan.php");
    exit;
}

/* ===============================
   UPDATE KENDARAAN
================================ */
if (isset($_POST['update'])) {

    $id = (int)$_POST['id_kendaraan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kendaraan']);
    $id_kategori = (int)$_POST['id_kategori'];
    $stok = (int)$_POST['stok'];

    mysqli_query($conn, "
        UPDATE kendaraan
        SET id_kategori = $id_kategori,
            nama_kendaraan = '$nama',
            stok = $stok
        WHERE id_kendaraan = $id
    ");

    logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Mengupdate kendaraan $nama"
);

    header("Location: kendaraan.php");
    exit;
}

/* ===============================
   DELETE KENDARAAN
================================ */
if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    // Cek apakah sedang dipakai di detail_peminjaman
    $cek = mysqli_query($conn, "
        SELECT COUNT(*) as total
        FROM detail_peminjaman
        WHERE id_kendaraan = $id
    ");

    $dipakai = mysqli_fetch_assoc($cek)['total'];

    if ($dipakai > 0) {
        $_SESSION['error'] = "Kendaraan tidak bisa dihapus karena sudah pernah digunakan.";
    } else {
        mysqli_query($conn, "DELETE FROM kendaraan WHERE id_kendaraan = $id");
        $_SESSION['success'] = "Kendaraan berhasil dihapus.";
    }

    logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Menghapus kendaraan ID $id"
);

    header("Location: kendaraan.php");
    exit;
}

/* ===============================
   AMBIL DATA
================================ */
$data = mysqli_query($conn, "
    SELECT k.*, kk.nama_kategori
    FROM kendaraan k
    LEFT JOIN kategori_kendaraan kk 
        ON k.id_kategori = kk.id_kategori
    ORDER BY k.id_kendaraan DESC
");
?>

<?php include "../partials/header.php"; ?>

<div class="container-fluid">
<div class="row">

<div class="col-auto p-0">
<?php include "../partials/sidebar_admin.php"; ?>
</div>

<div class="col p-4">

<h4>Data Kendaraan</h4>

<!-- FORM TAMBAH -->
<form method="post" class="row g-2 mb-4">

<div class="col-md-3">
<input type="text" name="nama_kendaraan" class="form-control" placeholder="Nama Kendaraan" required>
</div>

<div class="col-md-2">
<select name="id_kategori" class="form-control" required>
<option value="">Kategori</option>
<?php
$kategori = mysqli_query($conn, "SELECT * FROM kategori_kendaraan");
while($kat = mysqli_fetch_assoc($kategori)){
    echo "<option value='{$kat['id_kategori']}'>{$kat['nama_kategori']}</option>";
}
?>
</select>
</div>


<div class="col-md-2">
<input type="number" name="stok" class="form-control" placeholder="Stok" min="1" required>
</div>

<div class="col-md-2">
<button name="tambah" class="btn btn-primary w-100">Tambah</button>
</div>

</form>

<?php
if (isset($_GET['edit'])):
    $id_edit = (int)$_GET['edit'];
    $edit = mysqli_query($conn, "
        SELECT * FROM kendaraan WHERE id_kendaraan = $id_edit
    ");
    $data_edit = mysqli_fetch_assoc($edit);
?>

<h5>Edit Kendaraan</h5>
<form method="post" class="row g-2 mb-4">

<input type="hidden" name="id_kendaraan" value="<?= $data_edit['id_kendaraan'] ?>">

<div class="col-md-3">
<input type="text" name="nama_kendaraan"
       value="<?= $data_edit['nama_kendaraan'] ?>"
       class="form-control" required>
</div>

<div class="col-md-2">
<select name="id_kategori" class="form-control" required>
<?php
$kategori = mysqli_query($conn, "SELECT * FROM kategori_kendaraan");
while($kat = mysqli_fetch_assoc($kategori)){
    $selected = $kat['id_kategori'] == $data_edit['id_kategori'] ? 'selected' : '';
    echo "<option value='{$kat['id_kategori']}' $selected>{$kat['nama_kategori']}</option>";
}
?>
</select>
</div>

<div class="col-md-2">
<input type="number" name="stok"
       value="<?= $data_edit['stok'] ?>"
       class="form-control" min="1" required>
</div>

<div class="col-md-2">
<button name="update" class="btn btn-success w-100">Update</button>
</div>

</form>

<?php endif; ?>


<!-- TABLE -->
<table class="table table-bordered align-middle">
<thead class="table-light">
<tr>
<th>No</th>
<th>Nama Kendaraan</th>
<th>Kategori</th>
<th>Stok Tersedia</th>
<th>Aksi</th>

</tr>
</thead>

<tbody>
<?php 
$no = 1;
while($row = mysqli_fetch_assoc($data)):
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nama_kendaraan']) ?></td>
<td><?= htmlspecialchars($row['nama_kategori']) ?></td>
<td>
<span class="badge <?= $row['stok'] > 0 ? 'bg-success' : 'bg-danger' ?>">
<?= $row['stok'] ?>
</span>
</td>
<td>
    <a href="?edit=<?= $row['id_kendaraan'] ?>" 
       class="btn btn-warning btn-sm">Edit</a>

    <a href="?hapus=<?= $row['id_kendaraan'] ?>" 
       onclick="return confirm('Yakin ingin menghapus kendaraan ini?')"
       class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>
</div>
</div>
