<?php
function logAktivitas($conn, $user_id, $aksi) {
    mysqli_query($conn,
        "INSERT INTO log_aktivitas(user_id, aksi, waktu)
         VALUES ('$user_id','$aksi',NOW())"
    );
}
