<?php
include '../../config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Peminjaman</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include '../../partials/sidebar_admin.php'; ?>

    <!-- CONTENT -->
    <div class="container-fluid p-4">

        <h3 class="mb-4">Data Peminjaman</h3>

        <a href="../peminjaman/tambah.php" class="btn btn-primary mb-3">+ Tambah</a>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama User</th>
                            <th>Kendaraan</th>
                            <th>Tanggal Pinjam</th>
                            <th>Rencana Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    $no = 1;

                    $query = mysqli_query($conn, "
                        SELECT 
                            p.*,
                            u.nama,
                            GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS kendaraan
                        FROM peminjaman p
                        JOIN users u ON p.id_user = u.id_user
                        JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
                        JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
                        GROUP BY p.id_peminjaman
                        ORDER BY p.id_peminjaman DESC
                    ");

                    if (!$query) {
                        echo "<tr><td colspan='6' class='text-danger'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                    } else {
                        if (mysqli_num_rows($query) > 0) {
                            while ($d = mysqli_fetch_assoc($query)) {
                                echo "<tr>
                                    <td>$no</td>
                                    <td>{$d['nama']}</td>
                                    <td>{$d['kendaraan']}</td>
                                    <td>{$d['tanggal_pinjam']}</td>
                                    <td>{$d['tanggal_kembali']}</td>
                                    <td>
                                        <span class='badge bg-info text-dark'>
                                            {$d['status']}
                                        </span>
                                    </td>
                                </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Belum ada data peminjaman</td></tr>";
                        }
                    }
                    ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</div>

</body>
</html>