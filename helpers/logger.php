<?php

function logAktivitas($conn, $aktivitas, $referensi_id = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id_user = $_SESSION['id_user'] ?? null;
    $role = $_SESSION['role'] ?? 'unknown';

    if (!$conn || !$id_user || !$aktivitas) {
        return false;
    }

    $id_user = (int) $id_user;
    $referensi_id = $referensi_id ? (int)$referensi_id : "NULL";

    $aktivitas = mysqli_real_escape_string($conn, $aktivitas);

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $query = "
        INSERT INTO log_aktivitas 
        (id_user, role, ip_address, aktivitas, referensi_id, created_at)
        VALUES 
        ($id_user, '$role', '$ip', '$aktivitas', $referensi_id, NOW())
    ";

    return mysqli_query($conn, $query);
}