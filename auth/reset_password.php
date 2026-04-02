<?php
include "config/database.php"; // path diperbarui

// Password default
$default_password = "password";

// Hash password default
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update semua user
$query = mysqli_query($conn, "UPDATE users SET password='$hashed_password'");

if ($query) {
    echo "Semua password user berhasil di-reset menjadi 'password' (sudah di-hash).<br>";
    echo "Sekarang kamu bisa login dengan password: <b>password</b>";
} else {
    echo "Terjadi error: " . mysqli_error($conn);
}
