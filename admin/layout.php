<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f4f6f9;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            position: fixed;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 10px 0;
        }

        .sidebar a:hover {
            background: #34495e;
            padding: 5px;
        }

        .content {
            margin-left: 240px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="users.php">User</a>
    <a href="kendaraan.php">Kendaraan</a>
    <a href="kategori.php">Kategori</a>
    <a href="peminjaman.php">Peminjaman</a>
</div>

<div class="content">