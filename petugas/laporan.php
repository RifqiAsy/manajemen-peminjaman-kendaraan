<?php
include '../middleware/auth.php';
cekLogin();
cekRole('petugas');
include '../config/database.php';

// Ambil semua peminjaman
$data = mysqli_query($conn, "
    SELECT 
        pg.id_pengembalian,
        pg.nomor_invoice,
        pg.tanggal_kembali,
        pg.status_pembayaran,
        u.nama
    FROM pengembalian pg
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    JOIN users u ON p.id_user = u.id_user
    ORDER BY pg.created_at DESC
");
?>

<?php include '../partials/header.php'; ?>
<body>
    <div class="container mt-5">
        
        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>
        <h3 class="mb-4">Laporan Peminjaman Kendaraan</h3>

        <?php if (mysqli_num_rows($data) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Peminjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $r['nomor_invoice'] ?></td>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= date('d M Y', strtotime($r['tanggal_kembali'])) ?></td>
                                <td>
                                    <?= $r['status_pembayaran'] == 'lunas'
                                        ? '<span class="badge bg-success">Lunas</span>'
                                        : '<span class="badge bg-danger">Belum Lunas</span>' ?>
                                </td>
                                <td>
                                    <a href="invoice_pdf.php?id=<?= $r['id_pengembalian'] ?>"
                                       class="btn btn-sm btn-primary">
                                        Lihat Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Belum ada data peminjaman.</div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>