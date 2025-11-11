<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// =================== PROSES TAMBAH JADWAL ===================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $id_teknisi = $_POST['id_teknisi'];
    $id_pelanggan = $_POST['id_pelanggan'];
    $deskripsi = $_POST['deskripsi_pekerjaan'];
    $tanggal = $_POST['tanggal_jadwal'];
    $status = $_POST['status'];

    $sql = "INSERT INTO jadwal (id_teknisi, id_pelanggan, deskripsi_pekerjaan, tanggal_jadwal, status)
            VALUES ('$id_teknisi', '$id_pelanggan', '$deskripsi', '$tanggal', '$status')";
    if ($conn->query($sql)) {
        header("Location: jadwal.php?success=1");
        exit;
    } else {
        header("Location: jadwal.php?error=" . urlencode($conn->error));
        exit;
    }
}

// =================== PROSES UPDATE JADWAL ===================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $id_teknisi = $_POST['id_teknisi'];
    $id_pelanggan = $_POST['id_pelanggan'];
    $deskripsi = $_POST['deskripsi_pekerjaan'];
    $tanggal = $_POST['tanggal_jadwal'];
    $status = $_POST['status'];

    $sql = "UPDATE jadwal 
            SET id_teknisi='$id_teknisi', id_pelanggan='$id_pelanggan', deskripsi_pekerjaan='$deskripsi', 
                tanggal_jadwal='$tanggal', status='$status'
            WHERE id_jadwal='$id_jadwal'";
    if ($conn->query($sql)) {
        header("Location: jadwal.php?updated=1");
        exit;
    } else {
        header("Location: jadwal.php?error=" . urlencode($conn->error));
        exit;
    }
}

// =================== PROSES HAPUS JADWAL ===================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM jadwal WHERE id_jadwal='$id'");
    header("Location: jadwal.php?deleted=1");
    exit;
}

// =================== AMBIL DATA ===================
// Ambil daftar teknisi dan nama pengguna (nama teknisi = pengguna.nama)
$teknisi = $conn->query("
    SELECT t.id_teknisi, u.nama AS nama_teknisi
    FROM teknisi t
    LEFT JOIN pengguna u ON t.id_pengguna = u.id_pengguna
");
$pelanggan = $conn->query("SELECT * FROM pelanggan");

// Ambil daftar jadwal lengkap
$jadwal = $conn->query("
    SELECT j.id_jadwal, 
           u.nama AS nama_teknisi, 
           p.nama_pelanggan, 
           j.deskripsi_pekerjaan, 
           j.tanggal_jadwal, 
           j.status 
    FROM jadwal j
    LEFT JOIN teknisi t ON j.id_teknisi = t.id_teknisi
    LEFT JOIN pengguna u ON t.id_pengguna = u.id_pengguna
    LEFT JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    ORDER BY j.tanggal_jadwal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Jadwal | Sismontek</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        display: flex;
    }

    .sidebar {
        width: 240px;
        background-color: #3f72af;
        color: white;
        height: 100vh;
        padding-top: 20px;
        position: fixed;
    }
    .sidebar h2 {
        text-align: center;
        margin-bottom: 40px;
    }
    .sidebar a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 14px 25px;
        transition: 0.3s;
        font-size: 15px;
    }
    .sidebar a:hover, .sidebar a.active {
        background-color: #2e5c8a;
    }

    .main-content {
        margin-left: 240px;
        padding: 30px;
        width: 100%;
    }

    h1 {
        color: #3f72af;
        margin-bottom: 20px;
    }

    .form-card, .table-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .form-card h3, .table-card h3 {
        margin-top: 0;
        color: #3f72af;
    }

    form input, form select, form textarea {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }

    form button {
        background-color: #3f72af;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 15px;
        transition: 0.3s;
    }

    form button:hover {
        background-color: #2e5c8a;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th {
        background-color: #3f72af;
        color: white;
        padding: 10px;
        text-align: left;
    }

    td {
        padding: 10px;
    }

    .status {
        font-weight: bold;
        text-transform: capitalize;
        padding: 5px 10px;
        border-radius: 8px;
    }

    .status.dijadwalkan { background: #ffb74d; color: white; }
    .status.proses { background: #29b6f6; color: white; }
    .status.selesai { background: #66bb6a; color: white; }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .alert.success { background: #e8f5e9; color: #2e7d32; }
    .alert.error { background: #ffebee; color: #c62828; }

    .action-btn a {
        padding: 6px 10px;
        border-radius: 6px;
        text-decoration: none;
        color: white;
        margin-right: 5px;
    }
    .action-btn .edit { background-color: #29b6f6; }
    .action-btn .delete { background-color: #e53935; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>🔧 Sismontek</h2>
    <a href="dashboard.php">🏠 Home</a>
    <a href="jadwal.php" class="active">🗓 Jadwal</a>
    <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
    <a href="pelanggan.php">👥 Pelanggan</a>
    <a href="teknisi.php">🧑‍🔧 Teknisi</a>
    <a href="laporan.php">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <h1>Manajemen Jadwal Teknisi</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert success">✅ Jadwal berhasil ditambahkan!</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert success">✅ Jadwal berhasil diperbarui!</div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert success">🗑 Jadwal berhasil dihapus!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert error">❌ Terjadi kesalahan: <?= htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Form tambah jadwal -->
    <div class="form-card">
        <h3>Tambah Jadwal Baru</h3>
        <form method="POST" action="">
            <input type="hidden" name="id_jadwal" id="id_jadwal">
            <label for="id_teknisi">Pilih Teknisi</label>
            <select name="id_teknisi" id="id_teknisi" required>
                <option value="">-- Pilih Teknisi --</option>
                <?php
                $teknisi->data_seek(0);
                while ($t = $teknisi->fetch_assoc()):
                ?>
                    <option value="<?= $t['id_teknisi']; ?>"><?= $t['nama_teknisi']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="id_pelanggan">Pilih Pelanggan</label>
            <select name="id_pelanggan" id="id_pelanggan" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php
                $pelanggan->data_seek(0);
                while ($p = $pelanggan->fetch_assoc()):
                ?>
                    <option value="<?= $p['id_pelanggan']; ?>"><?= $p['nama_pelanggan']; ?> (<?= $p['paket']; ?>)</option>
                <?php endwhile; ?>
            </select>

            <label for="deskripsi_pekerjaan">Deskripsi Pekerjaan</label>
            <textarea name="deskripsi_pekerjaan" id="deskripsi_pekerjaan" rows="3" required></textarea>

            <label for="tanggal_jadwal">Tanggal Jadwal</label>
            <input type="date" name="tanggal_jadwal" id="tanggal_jadwal" required>

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="dijadwalkan">Dijadwalkan</option>
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
            </select>

            <button type="submit" name="tambah" id="btn-submit">+ Tambah Jadwal</button>
        </form>
    </div>

    <!-- Tabel daftar jadwal -->
    <div class="table-card">
        <h3>Daftar Jadwal Teknisi</h3>
        <table>
            <tr>
                <th>Nama Teknisi</th>
                <th>Nama Pelanggan</th>
                <th>Deskripsi</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php if ($jadwal && $jadwal->num_rows > 0): ?>
                <?php while ($j = $jadwal->fetch_assoc()): ?>
                <tr>
                    <td><?= $j['nama_teknisi'] ?: '-'; ?></td>
                    <td><?= $j['nama_pelanggan'] ?: '-'; ?></td>
                    <td><?= $j['deskripsi_pekerjaan']; ?></td>
                    <td><?= $j['tanggal_jadwal']; ?></td>
                    <td><span class="status <?= strtolower($j['status']); ?>"><?= ucfirst($j['status']); ?></span></td>
                    <td class="action-btn">
                        <a href="javascript:void(0)" class="edit" 
                           onclick="editData('<?= $j['id_jadwal']; ?>','<?= $j['nama_teknisi']; ?>','<?= $j['nama_pelanggan']; ?>','<?= htmlspecialchars($j['deskripsi_pekerjaan']); ?>','<?= $j['tanggal_jadwal']; ?>','<?= $j['status']; ?>')">
                           ✏ Edit
                        </a>
                        <a href="jadwal.php?hapus=<?= $j['id_jadwal']; ?>" class="delete" onclick="return confirm('Yakin ingin menghapus jadwal ini?')">🗑 Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Belum ada jadwal teknisi.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function editData(id, teknisi, pelanggan, deskripsi, tanggal, status) {
    document.getElementById('id_jadwal').value = id;
    document.getElementById('deskripsi_pekerjaan').value = deskripsi;
    document.getElementById('tanggal_jadwal').value = tanggal;
    document.getElementById('status').value = status;
    document.getElementById('btn-submit').name = 'edit';
    document.getElementById('btn-submit').innerText = '💾 Simpan Perubahan';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

</body>
</html>
