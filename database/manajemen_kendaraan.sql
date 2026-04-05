-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 04 Apr 2026 pada 14.19
-- Versi server: 8.4.3
-- Versi PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manajemen_kendaraan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `denda`
--

CREATE TABLE `denda` (
  `id_denda` int NOT NULL,
  `id_pengembalian` int DEFAULT NULL,
  `id_detail` int DEFAULT NULL,
  `jenis_denda` enum('terlambat','kerusakan') DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `keterangan` text,
  `status` enum('belum_dibayar','lunas') DEFAULT 'belum_dibayar',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_peminjaman`
--

CREATE TABLE `detail_peminjaman` (
  `id_detail` int NOT NULL,
  `id_peminjaman` int NOT NULL,
  `id_kendaraan` int NOT NULL,
  `jumlah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_kendaraan`
--

CREATE TABLE `kategori_kendaraan` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kategori_kendaraan`
--

INSERT INTO `kategori_kendaraan` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Mobil'),
(2, 'Motor'),
(3, 'Truk');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` int NOT NULL,
  `id_kategori` int NOT NULL,
  `nama_kendaraan` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'tersedia',
  `stok` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kendaraan`
--

INSERT INTO `kendaraan` (`id_kendaraan`, `id_kategori`, `nama_kendaraan`, `status`, `stok`) VALUES
(10, 1, 'Toyota Land Cruiser', 'tersedia', 20),
(11, 2, 'Honda Vario', 'tersedia', 7),
(13, 3, 'Hino', 'tersedia', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `aktivitas` varchar(255) DEFAULT NULL,
  `referensi_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `role`, `ip_address`, `aktivitas`, `referensi_id`, `created_at`) VALUES
(217, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-22 10:22:53'),
(218, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-22 10:23:05'),
(219, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-03-22 10:23:21'),
(220, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 11:39:25'),
(221, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 11:40:35'),
(222, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 11:44:46'),
(223, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 11:46:58'),
(224, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 11:52:08'),
(225, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:04:37'),
(226, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:07:14'),
(227, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:24:09'),
(228, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:25:17'),
(229, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:29:47'),
(230, 2, 'admin', '::1', '2', 0, '2026-03-30 12:29:56'),
(231, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:30:05'),
(232, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:30:31'),
(233, 3, 'petugas', '::1', '3', 0, '2026-03-30 12:30:37'),
(234, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:30:48'),
(235, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:31:30'),
(236, 3, 'petugas', '::1', '3', 0, '2026-03-30 12:31:36'),
(237, 3, 'petugas', '::1', '3', 0, '2026-03-30 12:31:41'),
(238, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-30 12:32:02'),
(239, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:00:02'),
(240, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:01:36'),
(241, 3, 'petugas', '::1', '3', 0, '2026-03-31 07:01:43'),
(242, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:01:47'),
(243, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:02:01'),
(244, 3, 'petugas', '::1', '3', 0, '2026-03-31 07:02:19'),
(245, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:02:25'),
(246, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:02:42'),
(247, 3, 'petugas', '::1', '3', 0, '2026-03-31 07:02:53'),
(248, 3, 'petugas', '::1', '3', 0, '2026-03-31 07:02:53'),
(249, 3, 'petugas', '::1', '3', 0, '2026-03-31 07:03:00'),
(250, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:03:10'),
(251, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:03:21'),
(252, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-03-31 07:47:49'),
(253, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:11:58'),
(254, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:12:27'),
(255, 3, 'petugas', '::1', '3', 0, '2026-04-01 09:12:30'),
(256, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-03 09:13:10'),
(257, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-03 09:13:33'),
(258, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-03 09:29:27'),
(259, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-03 09:40:55'),
(260, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:43:01'),
(261, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:43:07'),
(262, 3, 'petugas', '::1', '3', 0, '2026-04-01 09:43:11'),
(263, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:43:14'),
(264, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-04 09:43:33'),
(265, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:57:41'),
(266, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:57:48'),
(267, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 09:58:17'),
(268, 3, 'petugas', '::1', '3', 0, '2026-04-01 09:58:21'),
(269, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 09:58:32'),
(270, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 09:58:40'),
(271, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 10:21:26'),
(272, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 10:24:28'),
(273, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 10:24:49'),
(274, 3, 'petugas', '::1', '3', 0, '2026-04-01 10:24:52'),
(275, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 10:24:56'),
(276, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 10:24:17'),
(277, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 10:24:44'),
(278, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 10:33:41'),
(279, 3, 'petugas', '::1', '3', 0, '2026-04-05 11:03:38'),
(280, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 11:17:24'),
(281, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 11:19:00'),
(282, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-01 11:19:06'),
(283, 3, 'petugas', '::1', '3', 0, '2026-04-01 11:19:09'),
(284, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 11:19:17'),
(285, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 11:19:27'),
(286, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-05 11:19:34'),
(287, 3, 'petugas', '::1', '3', 0, '2026-04-05 11:19:37'),
(288, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-04-02 10:34:21'),
(289, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-02 10:34:32'),
(290, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-02 10:34:37'),
(291, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-04-02 11:04:44'),
(292, 2, 'admin', '::1', '2', 0, '2026-04-02 11:23:06'),
(293, 2, 'admin', '::1', '2', 0, '2026-04-02 11:23:18'),
(294, 2, 'admin', '::1', '2', 0, '2026-04-02 11:26:12'),
(295, 2, 'admin', '::1', '2', 0, '2026-04-02 11:26:17'),
(296, 4, NULL, NULL, 'Login ke sistem', NULL, '2026-04-04 06:04:35'),
(297, 3, NULL, NULL, 'Login ke sistem', NULL, '2026-04-04 06:04:57'),
(298, 2, NULL, NULL, 'Login ke sistem', NULL, '2026-04-04 06:05:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_denda`
--

CREATE TABLE `pembayaran_denda` (
  `id_pembayaran` int NOT NULL,
  `id_pengembalian` int DEFAULT NULL,
  `jumlah_bayar` int DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `status` enum('menunggu','disetujui','menunggu_kembali','dikembalikan','ditolak') NOT NULL DEFAULT 'menunggu',
  `approved_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_kembali` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id_pengembalian` int NOT NULL,
  `nomor_invoice` varchar(50) DEFAULT NULL,
  `id_peminjaman` int DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `kondisi_kendaraan` enum('baik','rusak') DEFAULT NULL,
  `catatan` text,
  `status` enum('proses','selesai') DEFAULT NULL,
  `diperiksa_oleh` int DEFAULT NULL,
  `total_denda` int DEFAULT '0',
  `status_pembayaran` enum('lunas','belum_dibayar') DEFAULT 'lunas',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','peminjam') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(2, 'Administrator', 'admin', '$2y$10$Nw68KymN8RwFqeUrUmI2s.WB.XpIJWkEIJROEjG0lCSy8/SBkVjFy', 'admin'),
(3, 'Petugas 1', 'petugas', '$2y$10$Nw68KymN8RwFqeUrUmI2s.WB.XpIJWkEIJROEjG0lCSy8/SBkVjFy', 'petugas'),
(4, 'Peminjam 1', 'peminjam', '$2y$10$Nw68KymN8RwFqeUrUmI2s.WB.XpIJWkEIJROEjG0lCSy8/SBkVjFy', 'peminjam');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `id_pengembalian` (`id_pengembalian`);

--
-- Indeks untuk tabel `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_detail_peminjaman` (`id_peminjaman`),
  ADD KEY `idx_kendaraan` (`id_kendaraan`);

--
-- Indeks untuk tabel `kategori_kendaraan`
--
ALTER TABLE `kategori_kendaraan`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`);

--
-- Indeks untuk tabel `pembayaran_denda`
--
ALTER TABLE `pembayaran_denda`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pengembalian` (`id_pengembalian`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_approved_by` (`approved_by`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id_pengembalian`),
  ADD KEY `id_peminjaman` (`id_peminjaman`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `kategori_kendaraan`
--
ALTER TABLE `kategori_kendaraan`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id_kendaraan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- AUTO_INCREMENT untuk tabel `pembayaran_denda`
--
ALTER TABLE `pembayaran_denda`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id_pengembalian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_pengembalian`) REFERENCES `pengembalian` (`id_pengembalian`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  ADD CONSTRAINT `detail_peminjaman_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_peminjaman_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`);

--
-- Ketidakleluasaan untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD CONSTRAINT `kendaraan_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_kendaraan` (`id_kategori`);

--
-- Ketidakleluasaan untuk tabel `pembayaran_denda`
--
ALTER TABLE `pembayaran_denda`
  ADD CONSTRAINT `pembayaran_denda_ibfk_1` FOREIGN KEY (`id_pengembalian`) REFERENCES `pengembalian` (`id_pengembalian`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
