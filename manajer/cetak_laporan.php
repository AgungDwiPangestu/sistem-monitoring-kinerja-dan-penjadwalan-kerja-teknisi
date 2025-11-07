<?php
include '../database/config.php';
$periode = $_GET['periode'] ?? 'minggu';

// Tentukan filter periode
if ($periode == 'minggu') {
    $filter = "WHERE l.tanggal_laporan >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $judul = "Laporan Kinerja Teknisi - Mingguan";
} else {
    $filter = "WHERE MONTH(l.tanggal_laporan) = MONTH(CURDATE())";
    $judul = "Laporan Kinerja Teknisi - Bulanan";
}

// Ambil data laporan + teknisi
$data = $conn->query("
    SELECT 
        l.*, 
        p.nama_pelanggan, 
        j.deskripsi_pekerjaan, 
        j.status, 
        pg.nama AS nama_teknisi
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    LEFT JOIN teknisi t ON j.id_teknisi = t.id_teknisi
    LEFT JOIN pengguna pg ON t.id_pengguna = pg.id_pengguna
    $filter
    ORDER BY l.tanggal_laporan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $judul; ?></title>
<style>
@page {
    size: A4 portrait;
    margin: 2.5cm 2cm;
}
body {
    font-family: 'Times New Roman', serif;
    background: white;
    color: black;
    margin: 0;
}

/* Header perusahaan */
.header {
    display: flex;
    align-items: center;
    border-bottom: 2px solid black;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.header img {
    width: 70px;
    height: 70px;
    margin-right: 20px;
}
.header-text {
    flex-grow: 1;
    text-align: center;
}
.header-text h1 {
    font-size: 18pt;
    margin: 0;
}
.header-text p {
    font-size: 11pt;
    margin: 2px 0;
}

/* Info laporan */
.info {
    text-align: right;
    margin-bottom: 20px;
    font-size: 11pt;
}

/* Tabel laporan */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11pt;
}
th, td {
    border: 1px solid #000;
    padding: 8px 10px;
    vertical-align: top;
}
th {
    background: #f2f2f2;
    text-align: center;
}
tr:nth-child(even) td {
    background: #fafafa;
}

/* Footer tanda tangan */
.footer {
    margin-top: 40px;
    text-align: right;
    font-size: 12pt;
}
.signature {
    margin-top: 60px;
    text-align: right;
}
.signature p {
    margin: 3px 0;
}

/* Print settings */
@media print {
    body { background: white; }
    .noprint { display: none; }
}
</style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 1000);">

<!-- Header laporan -->
<div class="header">
    <img src="../assets/logo.png" alt="Logo Perusahaan" onerror="this.style.display='none'">
    <div class="header-text">
        <h1>PT. Sismontek Indonesia</h1>
        <p>Jl. Teknologi No. 45, Jakarta 11530</p>
        <p>Telp: (021) 555-9090 | Email: info@sismontek.co.id</p>
    </div>
</div>

<!-- Judul laporan -->
<h2 style="text-align:center; margin-bottom:5px;"><?= $judul; ?></h2>
<p style="text-align:center; margin-top:0;">Periode: <?= ($periode == 'minggu') ? '7 Hari Terakhir' : 'Bulan ' . date('F Y'); ?></p>

<!-- Info tanggal cetak -->
<div class="info">
    <p>Tanggal Cetak: <?= date('d F Y'); ?></p>
</div>

<!-- Tabel data laporan -->
<table>
    <tr>
        <th>No</th>
        <th>Pelanggan</th>
        <th>Teknisi</th>
        <th>Deskripsi Pekerjaan</th>
        <th>Kendala / Catatan</th>
        <th>Status</th>
        <th>Tanggal Laporan</th>
    </tr>
    <?php
    if ($data->num_rows > 0) {
        $no = 1;
        while ($row = $data->fetch_assoc()) {
            $namaPelanggan = htmlspecialchars($row['nama_pelanggan']);
            $namaTeknisi = !empty($row['nama_teknisi']) ? htmlspecialchars($row['nama_teknisi']) : 'Belum ditentukan';
            $deskripsi = htmlspecialchars($row['deskripsi_pekerjaan']);
            $kendala = htmlspecialchars($row['kendala']);
            $status = ucfirst(htmlspecialchars($row['status']));
            $tanggal = htmlspecialchars($row['tanggal_laporan']);

            echo "
            <tr>
                <td style='text-align:center; padding:8px;'>{$no}</td>
                <td style='padding:8px;'>{$namaPelanggan}</td>
                <td style='padding:8px;'>{$namaTeknisi}</td>
                <td style='padding:8px;'>{$deskripsi}</td>
                <td style='padding:8px;'>{$kendala}</td>
                <td style='text-align:center; padding:8px;'>{$status}</td>
                <td style='text-align:center; padding:8px;'>{$tanggal}</td>
            </tr>";
            $no++;
        }
    } else {
        echo "
        <tr>
            <td colspan='7' style='text-align:center; padding:10px; color:#777;'>
                Tidak ada data laporan pada periode ini.
            </td>
        </tr>";
    }
    ?>
</table>

<!-- Footer tanda tangan -->
<div class="footer">
    <p>Jakarta, <?= date('d F Y'); ?></p>
</div>
<div class="signature">
    <p><b>Manajer Operasional</b></p>
    <br><br><br>
    <p><b>ARO MULYA PRATAMA</b></p>
</div>

</body>
</html>
