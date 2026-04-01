<?php
include "../middleware/auth.php";
cekLogin();
cekRole('petugas');
include "../config/database.php";

/*
|--------------------------------------------------------------------------
| TOTAL DATA
|--------------------------------------------------------------------------
*/

function getTotal($conn, $where = "")
{
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman $where");
    return mysqli_fetch_assoc($q)['total'];
}

$total_peminjaman = getTotal($conn);
$total_menunggu   = getTotal($conn, "WHERE status='menunggu'");
$total_disetujui  = getTotal($conn, "WHERE status='disetujui'");
$total_kembali    = getTotal($conn, "WHERE status='menunggu_kembali'");

/*
|--------------------------------------------------------------------------
| DATA TERBARU
|--------------------------------------------------------------------------
*/
$qLast = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.status,
        p.tanggal_pinjam,
        u.nama,
        GROUP_CONCAT(k.nama_kendaraan SEPARATOR ', ') AS kendaraan
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
    JOIN kendaraan k ON d.id_kendaraan = k.id_kendaraan
    GROUP BY p.id_peminjaman
    ORDER BY p.id_peminjaman DESC
    LIMIT 5
");

/*
|--------------------------------------------------------------------------
| FUNCTION BADGE STATUS
|--------------------------------------------------------------------------
*/
function badgeStatus($status)
{
    switch ($status) {
        case 'menunggu':
            return 'warning';
        case 'disetujui':
            return 'primary';
        case 'menunggu_kembali':
            return 'danger';
        case 'dikembalikan':
            return 'success';
        default:
            return 'secondary';
    }
}
?>

<?php include "../partials/header.php"; ?>

<body>
<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include "../partials/sidebar_petugas.php"; ?>

    <!-- CONTENT -->
    <div class="flex-grow-1 p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Dashboard Petugas</h4>
                <small class="text-muted">Monitoring sistem peminjaman kendaraan</small>
            </div>
            <div class="fw-semibold">
                👤 <?= htmlspecialchars($_SESSION['nama']); ?>
            </div>
        </div>

        <!-- CARDS -->
        <div class="row g-4 mb-4">

            <?php
            function card($title, $value, $color)
            {
                return "
                <div class='col-md-3'>
                    <div class='card shadow-sm border-0 border-start border-4 border-$color'>
                        <div class='card-body'>
                            <small class='text-muted'>$title</small>
                            <h3 class='fw-bold text-$color'>$value</h3>
                        </div>
                    </div>
                </div>";
            }

            echo card("Total Peminjaman", $total_peminjaman, "dark");
            echo card("Menunggu Persetujuan", $total_menunggu, "warning");
            echo card("Sedang Dipinjam", $total_disetujui, "primary");
            echo card("Menunggu Pengembalian", $total_kembali, "danger");
            ?>

        </div>

        <!-- TABLE -->
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <h5 class="mb-3 fw-semibold">Peminjaman Terbaru</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Kendaraan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php if (mysqli_num_rows($qLast) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($r = mysqli_fetch_assoc($qLast)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($r['nama']) ?></td>
                                    <td><?= htmlspecialchars($r['kendaraan']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= badgeStatus($r['status']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Belum ada data
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
</body>
</html>