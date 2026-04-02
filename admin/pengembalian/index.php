<?php
include '../../config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pengembalian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">

    <?php include '../../partials/sidebar_admin.php'; ?>

    <div class="container-fluid p-4">

        <h3 class="mb-4">Data Pengembalian</h3>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Kendaraan</th>
                            <th>Tanggal Pinjam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    $no = 1;

                    $query = mysqli_query($conn, "
                        SELECT p.*, u.nama, k.nama_kendaraan
                        FROM peminjaman p
                        JOIN users u ON p.id_user = u.id_user
                        JOIN kendaraan k ON p.id_kendaraan = k.id_kendaraan
                        WHERE p.status = 'disetujui' OR p.status = 'menunggu_kembali'
                        ORDER BY p.id_peminjaman DESC
                    ");

                    while ($d = mysqli_fetch_assoc($query)) {
                        echo "<tr>
                            <td>$no</td>
                            <td>{$d['nama']}</td>
                            <td>{$d['nama_kendaraan']}</td>
                            <td>{$d['tanggal_pinjam']}</td>
                            <td>
                                <a href='proses_pengembalian.php?id={$d['id_peminjaman']}' 
                                   class='btn btn-success btn-sm'>
                                   Kembalikan
                                </a>
                            </td>
                        </tr>";
                        $no++;
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