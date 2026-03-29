<?php
include '../config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Peminjaman</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h3>Tambah Peminjaman</h3>

    <form action="proses_tambah_peminjaman.php" method="POST">

        <div class="mb-3">
            <label>User</label>
            <select name="id_user" class="form-control" required>
                <option value="">-- Pilih User --</option>
                <?php
                $user = mysqli_query($conn, "SELECT * FROM users");
                while ($u = mysqli_fetch_assoc($user)) {
                    echo "<option value='{$u['id_user']}'>{$u['nama']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Kendaraan</label>
            <select name="id_kendaraan" class="form-control" required>
                <option value="">-- Pilih Kendaraan --</option>
                <?php
                $kendaraan = mysqli_query($conn, "SELECT * FROM kendaraan");
                while ($k = mysqli_fetch_assoc($kendaraan)) {
                    echo "<option value='{$k['id_kendaraan']}'>{$k['nama_kendaraan']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Tanggal Pinjam</label>
            <input type="date" name="tanggal_pinjam" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Rencana Kembali</label>
            <input type="date" name="tanggal_rencana_kembali" class="form-control" required>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="peminjaman.php" class="btn btn-secondary">Kembali</a>

    </form>
</div>

</body>
</html>