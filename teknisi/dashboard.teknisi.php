<?php
session_start();
include '../database/config.php';

// Cek login & role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil id_teknisi
$query_teknisi = $conn->prepare("SELECT id_teknisi FROM teknisi WHERE id_pengguna = ?");
$query_teknisi->bind_param("i", $id_pengguna);
$query_teknisi->execute();
$res_teknisi = $query_teknisi->get_result();
$row_teknisi = $res_teknisi ? $res_teknisi->fetch_assoc() : null;
$id_teknisi = $row_teknisi && isset($row_teknisi['id_teknisi']) ? (int)$row_teknisi['id_teknisi'] : null;

// Jika teknisi tidak ditemukan, hentikan dengan pesan yang jelas
if ($id_teknisi === null) {
    http_response_code(400);
    echo "Data teknisi tidak ditemukan untuk pengguna ini. Silakan hubungi admin.";
    exit;
}

// Proses tombol "Mulai"
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mulai'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $update = $conn->prepare("UPDATE jadwal SET status='proses' WHERE id_jadwal=?");
    $update->bind_param("i", $id_jadwal);
    $update->execute();
    header("Location: dashboard.teknisi.php");
    exit;
}

// Ambil jadwal + alamat pelanggan
$query_jadwal = "
    SELECT j.id_jadwal, p.nama_pelanggan, p.alamat, 
           j.deskripsi_pekerjaan, j.status, j.tanggal_jadwal
    FROM jadwal j
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    WHERE j.id_teknisi = ?
    ORDER BY j.tanggal_jadwal DESC
";
$stmt = $conn->prepare($query_jadwal);
$stmt->bind_param("i", $id_teknisi);
$stmt->execute();
$jadwal = $stmt->get_result();

// Hitung status
// Hitung status dengan prepared statement untuk menghindari error saat id_teknisi null
$countStmt = $conn->prepare("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi = ? AND status = ?");

$status = 'dijadwalkan';
$countStmt->bind_param("is", $id_teknisi, $status);
$countStmt->execute();
$count_dijadwalkan = ($countStmt->get_result()->fetch_assoc()['jml']) ?? 0;

$status = 'proses';
$countStmt->bind_param("is", $id_teknisi, $status);
$countStmt->execute();
$count_proses = ($countStmt->get_result()->fetch_assoc()['jml']) ?? 0;

$status = 'selesai';
$countStmt->bind_param("is", $id_teknisi, $status);
$countStmt->execute();
$count_selesai = ($countStmt->get_result()->fetch_assoc()['jml']) ?? 0;

$countStmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Teknisi | Sismontek</title>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fb;
            margin: 0;
        }

        /* Header */
        .header {
            background: #3f72af;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            background: #d32f2f;
            color: white;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
        }

        /* Container */
        .container {
            padding: 20px;
        }

        h1 {
            color: #3f72af;
        }

        /* Status Cards */
        .status-cards {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .status-card {
            flex: 1;
            min-width: 140px;
            background: white;
            border-radius: 14px;
            text-align: center;
            padding: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .status-card h2 {
            color: #3f72af;
            margin: 5px 0 0;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            margin-top: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3f72af;
            color: white;
        }

        /* Status Label */
        .status {
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.dijadwalkan {
            background-color: #f9a825;
        }

        .status.proses {
            background-color: #29b6f6;
        }

        .status.selesai {
            background-color: #66bb6a;
        }

        /* Buttons */
        button,
        .btn {
            padding: 7px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-weight: 600;
        }

        .btn-proses {
            background-color: #29b6f6;
        }

        .btn-selesai {
            background-color: #66bb6a;
            text-decoration: none;
        }

        .btn-proses:hover {
            background-color: #0288d1;
        }

        .btn-selesai:hover {
            background-color: #388e3c;
        }

        /* MOBILE RESPONSIVE */
        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            tr {
                background: #ffffff;
                margin-bottom: 12px;
                padding: 12px;
                border-radius: 12px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            }

            td {
                border: none;
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #3f72af;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>🔧 Dashboard Teknisi</h2>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="container">
        <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>

        <!-- STATUS CARD -->
        <div class="status-cards">
            <div class="status-card">
                <h3>Dijadwalkan</h3>
                <h2><?= $count_dijadwalkan ?></h2>
            </div>
            <div class="status-card">
                <h3>Proses</h3>
                <h2><?= $count_proses ?></h2>
            </div>
            <div class="status-card">
                <h3>Selesai</h3>
                <h2><?= $count_selesai ?></h2>
            </div>
        </div>

        <!-- TABLE LIST -->
        <div class="card">
            <h3>📋 Jadwal Kerja</h3>

            <?php if ($jadwal->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pelanggan</th>
                            <th>Alamat</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1;
                        while ($row = $jadwal->fetch_assoc()): ?>
                            <tr>
                                <td data-label="No"><?= $no ?></td>
                                <td data-label="Pelanggan"><?= $row['nama_pelanggan'] ?></td>
                                <td data-label="Alamat"><?= $row['alamat'] ?></td>
                                <td data-label="Deskripsi"><?= $row['deskripsi_pekerjaan'] ?></td>
                                <td data-label="Status">
                                    <span class="status <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span>
                                </td>
                                <td data-label="Tanggal"><?= $row['tanggal_jadwal'] ?></td>
                                <td data-label="Aksi">

                                    <?php if ($row['status'] == 'dijadwalkan'): ?>

                                        <form method="POST">
                                            <input type="hidden" name="id_jadwal" value="<?= $row['id_jadwal'] ?>">
                                            <button name="mulai" class="btn btn-proses">Mulai</button>
                                        </form>

                                    <?php elseif ($row['status'] == 'proses'): ?>

                                        <a href="form_laporan.php?id_jadwal=<?= $row['id_jadwal'] ?>" class="btn btn-selesai">Buat Laporan</a>

                                    <?php else: ?>

                                        <a href="edit_laporan.php?id_jadwal=<?= $row['id_jadwal'] ?>" class="btn btn-proses">Edit</a>

                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php $no++;
                        endwhile; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <p>Belum ada jadwal kerja.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>