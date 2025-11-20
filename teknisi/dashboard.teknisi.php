<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil id_teknisi
$query_teknisi = $conn->prepare("SELECT id_teknisi FROM teknisi WHERE id_pengguna = ?");
$query_teknisi->bind_param("i", $id_pengguna);
$query_teknisi->execute();
$id_teknisi = $query_teknisi->get_result()->fetch_assoc()['id_teknisi'];

// Proses Mulai
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mulai'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $update = $conn->prepare("UPDATE jadwal SET status='proses' WHERE id_jadwal=?");
    $update->bind_param("i", $id_jadwal);
    $update->execute();
    header("Location: dashboard.teknisi.php?status=proses");
    exit;
}

// Ambil jadwal teknisi
$query_jadwal = "
    SELECT j.id_jadwal, p.nama_pelanggan, j.deskripsi_pekerjaan, j.status, j.tanggal_jadwal
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
$count_dijadwalkan = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='dijadwalkan'")->fetch_assoc()['jml'];
$count_proses = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='proses'")->fetch_assoc()['jml'];
$count_selesai = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='selesai'")->fetch_assoc()['jml'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Teknisi | Sismontek</title>

<style>
:root {
    --primary: #3f72af;
    --danger: #d32f2f;
    --success: #66bb6a;
    --warning: #f9a825;
    --proses: #29b6f6;
}

/* RESET */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
}

/* HEADER */
.header {
    background: var(--primary);
    color: white;
    padding: 15px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h2 {
    margin: 0;
    font-size: 20px;
}

.header a {
    background: var(--danger);
    padding: 8px 14px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-size: 14px;
}

/* CONTAINER */
.container {
    padding: 18px;
}

/* TITLE */
h1 {
    margin-top: 5px;
    color: var(--primary);
    font-size: 20px;
}

/* STATUS CARDS */
.status-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 16px;
    margin-top: 15px;
}

.status-card {
    background: white;
    padding: 18px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

.status-card h3 {
    margin: 0;
    font-size: 14px;
    color: #555;
}

.status-card h2 {
    margin: 8px 0 0 0;
    color: var(--primary);
    font-size: 26px;
}

/* CARD BOX */
.card {
    background: white;
    padding: 20px;
    margin-top: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: var(--primary);
    color: white;
    padding: 10px;
    font-size: 14px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

/* STATUS BADGE */
.status {
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    text-transform: capitalize;
    font-size: 12px;
}

.status.dijadwalkan { background: var(--warning); }
.status.proses { background: var(--proses); }
.status.selesai { background: var(--success); }

/* BUTTONS */
button, .btn {
    padding: 8px 14px;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    color: white;
    font-weight: 600;
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
}

.btn-proses { background: var(--proses); }
.btn-selesai { background: var(--success); }

.btn-proses:hover { background: #0288d1; }
.btn-selesai:hover { background: #388e3c; }

/* RESPONSIVE TABLE */
@media (max-width: 700px) {
    table, thead, tbody, tr, th, td {
        display: block;
    }

    tr {
        margin-bottom: 15px;
        background: #fff;
        padding: 12px;
        border-radius: 10px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    }

    th { display: none; }

    td {
        padding: 6px 0;
        border: none;
        display: flex;
        justify-content: space-between;
    }

    td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #444;
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

    <!-- STATUS CARDS -->
    <div class="status-cards">
        <div class="status-card">
            <h3>Dijadwalkan</h3>
            <h2><?= $count_dijadwalkan; ?></h2>
        </div>

        <div class="status-card">
            <h3>Proses</h3>
            <h2><?= $count_proses; ?></h2>
        </div>

        <div class="status-card">
            <h3>Selesai</h3>
            <h2><?= $count_selesai; ?></h2>
        </div>
    </div>

    <!-- TABLE JADWAL -->
    <div class="card">
        <h3>📋 Jadwal Kerja</h3>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>

            <?php
            if ($jadwal->num_rows > 0) {
                $no = 1;
                while ($row = $jadwal->fetch_assoc()) { ?>
                    
                    <tr>
                        <td data-label="No"><?= $no ?></td>
                        <td data-label="Pelanggan"><?= $row['nama_pelanggan'] ?></td>
                        <td data-label="Deskripsi"><?= $row['deskripsi_pekerjaan'] ?></td>

                        <td data-label="Status">
                            <span class="status <?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>

                        <td data-label="Tanggal"><?= $row['tanggal_jadwal'] ?></td>

                        <td data-label="Aksi">
                            <?php if ($row['status'] == 'dijadwalkan'): ?>
                                <form method="POST">
                                    <input type="hidden" name="id_jadwal" value="<?= $row['id_jadwal'] ?>">
                                    <button name="mulai" class="btn btn-proses">Mulai</button>
                                </form>

                            <?php elseif ($row['status'] == 'proses'): ?>
                                <a class="btn btn-selesai" href="form_laporan.php?id_jadwal=<?= $row['id_jadwal'] ?>">Buat Laporan</a>

                            <?php else: ?>
                                <a class="btn btn-proses" href="edit_laporan.php?id_jadwal=<?= $row['id_jadwal'] ?>">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>

            <?php $no++; }
            } else {
                echo "<tr><td colspan='6'>Belum ada jadwal.</td></tr>";
            }
            ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
