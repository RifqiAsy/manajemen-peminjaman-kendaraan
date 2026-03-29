<?php
include '../config/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Log Aktivitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">

<?php include '../partials/sidebar_admin.php'; ?>

<div class="container-fluid p-4">

<h3 class="mb-4">Log Aktivitas</h3>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>User</th>
            <th>Role</th>
            <th>Aktivitas</th>
            <th>IP</th>
            <th>Waktu</th>
        </tr>
    </thead>
    <tbody>

<?php
$no = 1;

$query = mysqli_query($conn, "
    SELECT l.*, u.nama 
    FROM log_aktivitas l
    LEFT JOIN users u ON l.id_user = u.id_user
    ORDER BY l.created_at DESC
");

while ($d = mysqli_fetch_assoc($query)) {
    echo "<tr>
        <td>$no</td>
        <td>{$d['nama']}</td>
        <td>{$d['role']}</td>
        <td>{$d['aktivitas']}</td>
        <td>{$d['ip_address']}</td>
        <td>{$d['created_at']}</td>
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