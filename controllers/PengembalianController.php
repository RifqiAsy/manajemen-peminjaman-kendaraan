<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/logger.php';

class PengembalianController
{
    private $conn;
    private $DENDA_PER_HARI = 50000;

    public function __construct($conn)
    {
        $this->conn = $conn;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    /*
    |--------------------------------------------------------------------------
    | PROSES PENGEMBALIAN (VERIFIKASI)
    |--------------------------------------------------------------------------
    */
    public function verifikasi($id_peminjaman, $id_petugas, $denda_manual = 0, $keterangan = '')
    {
        mysqli_begin_transaction($this->conn);

        try {

            /*
            | VALIDASI + LOCK
            */
            $q = mysqli_query($this->conn, "
                SELECT *
                FROM peminjaman
                WHERE id_peminjaman = $id_peminjaman
                AND status = 'menunggu_kembali'
                FOR UPDATE
            ");

            if (mysqli_num_rows($q) !== 1) {
                throw new Exception("Data tidak valid atau sudah diproses.");
            }

            $p = mysqli_fetch_assoc($q);

            /*
            | HITUNG TERLAMBAT
            */
            $today = new DateTime();

            $batas = !empty($p['tanggal_jatuh_tempo'])
                ? new DateTime($p['tanggal_jatuh_tempo'])
                : (new DateTime($p['tanggal_pinjam']))->modify('+3 days');

            $terlambat = ($today > $batas)
                ? $batas->diff($today)->days
                : 0;

            $denda_terlambat = $terlambat * $this->DENDA_PER_HARI;

            /*
            | TOTAL DENDA
            */
            $total_denda = $denda_terlambat + $denda_manual;
            $status_bayar = $total_denda > 0 ? 'belum_dibayar' : 'lunas';

            /*
            | GENERATE INVOICE
            */
            $nomor_invoice = $this->generateInvoice();

            /*
            | INSERT PENGEMBALIAN
            */
            $ket = mysqli_real_escape_string($this->conn, $keterangan);

            mysqli_query($this->conn, "
                INSERT INTO pengembalian
                (nomor_invoice, id_peminjaman, tanggal_kembali, kondisi_kendaraan, catatan, total_denda, status_pembayaran, status, diperiksa_oleh, created_at)
                VALUES (
                    '$nomor_invoice',
                    $id_peminjaman,
                    CURDATE(),
                    'baik',
                    '$ket',
                    $total_denda,
                    '$status_bayar',
                    'disetujui',
                    $id_petugas,
                    NOW()
                )
            ");

            $id_pengembalian = mysqli_insert_id($this->conn);

            /*
            | KEMBALIKAN STOK
            */
            $detail = mysqli_query($this->conn, "
                SELECT *
                FROM detail_peminjaman
                WHERE id_peminjaman = $id_peminjaman
            ");

            while ($d = mysqli_fetch_assoc($detail)) {

                mysqli_query($this->conn, "
                    UPDATE kendaraan
                    SET stok = stok + {$d['jumlah']}
                    WHERE id_kendaraan = {$d['id_kendaraan']}
                ");
            }

            /*
            | UPDATE STATUS PEMINJAMAN
            */
            mysqli_query($this->conn, "
                UPDATE peminjaman
                SET status = 'dikembalikan'
                WHERE id_peminjaman = $id_peminjaman
            ");

            /*
            | LOG
            */
            logAktivitas(
                $this->conn,
                $id_petugas,
                "Verifikasi pengembalian ID $id_peminjaman (denda: $total_denda)"
            );

            mysqli_commit($this->conn);

            return $id_pengembalian;

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE INVOICE
    |--------------------------------------------------------------------------
    */
    private function generateInvoice()
    {
        $tahun = date('Y');

        $q = mysqli_query($this->conn, "
            SELECT nomor_invoice 
            FROM pengembalian
            WHERE YEAR(created_at) = '$tahun'
            ORDER BY id_pengembalian DESC
            LIMIT 1
        ");

        $lastNumber = 0;

        if ($q && mysqli_num_rows($q) > 0) {
            $data = mysqli_fetch_assoc($q);

            if (!empty($data['nomor_invoice'])) {
                $lastNumber = (int) substr($data['nomor_invoice'], -4);
            }
        }

        $newNumber = $lastNumber + 1;

        return "INV-$tahun-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}