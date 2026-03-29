<?php
include "../middleware/auth.php";
cekLogin();
cekRole('admin');
include "../config/database.php";
require '../helpers/logger.php';

/* ===============================
   TAMBAH USER
================================ */
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "
        INSERT INTO users (nama, username, password, role)
        VALUES ('$nama', '$username', '$password', '$role')
    ");
    

    logAktivitas(
        $conn,
        $_SESSION['id_user'],
        "Menambah user $nama"
    );

    $_SESSION['success'] = "User berhasil ditambahkan";
    header("Location: users.php");
    exit;
}

/* ===============================
   UPDATE USER
================================ */
if (isset($_POST['update'])) {
    $id       = (int)$_POST['id_user'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "
            UPDATE users 
            SET nama='$nama', username='$username', role='$role', password='$password'
            WHERE id_user=$id
        ");
    } else {
        mysqli_query($conn, "
            UPDATE users 
            SET nama='$nama', username='$username', role='$role'
            WHERE id_user=$id
        ");
    }
    

    logAktivitas(
        $conn,
        $_SESSION['id_user'],
        "Mengupdate user $nama"
    );

    $_SESSION['success'] = "User berhasil diperbarui";
    header("Location: users.php");
    exit;
}

/* ===============================
   DELETE USER
================================ */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id");

    logAktivitas(
        $conn,
        $_SESSION['id_user'],
        "Menghapus user ID $id"
    );

    $_SESSION['success'] = "User berhasil dihapus";
    header("Location: users.php");
    exit;
}

/* ===============================
   DATA USER
================================ */
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
?>

<?php include "../partials/header.php"; ?>
<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-auto p-0">
        <?php include "../partials/sidebar_admin.php"; ?>
    </div>

    <!-- CONTENT -->
    <div class="col p-4">

        <h4 class="mb-3">Manajemen User</h4>

        <!-- FLASH MESSAGE -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH USER -->
        <form method="post" class="row g-2 mb-4">
            <div class="col-md-3">
                <input type="text" name="nama" class="form-control" placeholder="Nama" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="col-md-2">
                <select name="role" class="form-control" required>
                    <option value="">Role</option>
                    <option value="admin">Admin</option>
                    <option value="petugas">Petugas</option>
                    <option value="peminjam">Peminjam</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-md-2">
                <button name="tambah" class="btn btn-primary w-100">Tambah</button>
            </div>
        </form>

        <!-- TABEL USER -->
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th width="170">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <form method="post">
                        <td>
                            <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                            <input type="text" name="nama" class="form-control"
                                   value="<?= htmlspecialchars($u['nama']) ?>" required>
                        </td>
                        <td>
                            <input type="text" name="username" class="form-control"
                                   value="<?= htmlspecialchars($u['username']) ?>" required>
                        </td>
                        <td>
                            <select name="role" class="form-control" required>
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                <option value="petugas" <?= $u['role']=='petugas'?'selected':'' ?>>Petugas</option>
                                <option value="peminjam" <?= $u['role']=='peminjam'?'selected':'' ?>>Peminjam</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="submit" name="update" class="btn btn-sm btn-warning">
                                Simpan
                            </button>
                            <a href="?hapus=<?= $u['id_user'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus user ini?')">
                                Hapus
                            </a>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>
</div>
