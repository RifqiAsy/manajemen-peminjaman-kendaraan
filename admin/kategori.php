<?php
include "../middleware/auth.php";
cekLogin();
cekRole('admin');
include "../config/database.php";
require '../helpers/logger.php';

// TAMBAH DATA
if(isset($_POST['tambah'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);

    // cek duplikat
    $cek = mysqli_query($conn, "SELECT * FROM kategori_kendaraan WHERE nama_kategori='$nama'");
    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Kategori sudah ada!');</script>";
    } else {
        mysqli_query($conn,"
            INSERT INTO kategori_kendaraan (nama_kategori)
            VALUES ('$nama')
        ");

        logAktivitas($conn, $_SESSION['id_user'], "Menambah kategori kendaraan $nama");
    }
}

// HAPUS
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM kategori_kendaraan
        WHERE id_kategori='$id'
    ");

    logAktivitas($conn, $_SESSION['id_user'], "Menghapus kategori kendaraan ID $id");
}

// EDIT
if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);

    mysqli_query($conn,"
        UPDATE kategori_kendaraan 
        SET nama_kategori='$nama'
        WHERE id_kategori='$id'
    ");

    logAktivitas($conn, $_SESSION['id_user'], "Edit kategori kendaraan ID $id");
}

$data = mysqli_query($conn,"SELECT * FROM kategori_kendaraan");
?>

<?php include "../partials/header.php"; ?>

<body>
<div class="d-flex">

    <!-- SIDEBAR ADMIN -->
    <?php include "../partials/sidebar_admin.php"; ?>

    <!-- CONTENT -->
    <div class="container mt-4">
        <h4>Kategori Kendaraan</h4>

        <!-- FORM TAMBAH -->
        <form method="post" class="mb-3">
            <input type="text" name="nama" class="form-control mb-2" placeholder="Nama kategori" required>
            <button name="tambah" class="btn btn-primary">Tambah</button>
        </form>

        <!-- TABEL -->
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($r=mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $r['nama_kategori'] ?></td>
                <td>
                    <!-- BUTTON EDIT -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id_kategori'] ?>">Edit</button>

                    <!-- BUTTON HAPUS -->
                    <a href="?hapus=<?= $r['id_kategori'] ?>" 
                       onclick="return confirm('Yakin hapus?')" 
                       class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>

            <!-- MODAL EDIT -->
            <div class="modal fade" id="edit<?= $r['id_kategori'] ?>">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post">
                            <div class="modal-header">
                                <h5>Edit Kategori</h5>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $r['id_kategori'] ?>">
                                <input type="text" name="nama" class="form-control" value="<?= $r['nama_kategori'] ?>" required>
                            </div>
                            <div class="modal-footer">
                                <button name="edit" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php endwhile; ?>
        </table>
    </div>

</div>
</body>
</html>