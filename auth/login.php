<?php
session_start();
if (isset($_SESSION['username'])) {
    // Arahkan langsung ke dashboard sesuai role
    switch ($_SESSION['role']) {
        case 'admin': header("Location: ../admin/dashboard.php"); break;
        case 'teknisi': header("Location: ../teknisi/dashboard.teknisi.php"); break;
        case 'manajer': header("Location: ../manajer/dashboard.manajer.php"); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Sismontek</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #3f72af, #dbe2ef);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 15px;
    }

    .login-container {
        background-color: #fff;
        padding: 35px 30px;
        border-radius: 16px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeIn 0.7s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2 {
        text-align: center;
        color: #3f72af;
        font-weight: 600;
        margin-bottom: 25px;
    }

    .input-group {
        margin-bottom: 18px;
    }

    .input-group label {
        font-weight: 600;
        font-size: 14px;
        color: #3f72af;
        margin-bottom: 6px;
        display: block;
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #dbe2ef;
        border-radius: 10px;
        font-size: 15px;
        transition: 0.3s;
    }

    .input-group input:focus {
        border-color: #3f72af;
        box-shadow: 0 0 5px rgba(63, 114, 175, 0.3);
        outline: none;
    }

    button {
        background-color: #3f72af;
        color: white;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background-color: #2b5d9c;
    }

    .alert {
        background-color: #f8d7da;
        color: #842029;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 18px;
        text-align: center;
    }

    .footer-text {
        margin-top: 15px;
        text-align: center;
        color: #3f72af;
        font-size: 13px;
    }

    /* RESPONSIVE MOBILE */
    @media (max-width: 480px) {
        .login-container {
            padding: 28px 22px;
            max-width: 100%;
            border-radius: 12px;
        }

        h2 {
            font-size: 20px;
        }

        button {
            font-size: 15px;
            padding: 12px;
        }
    }
</style>
</head>

<body>
    <div class="login-container">
        <h2>🔧 Login Sismontek</h2>

        <?php if (isset($_GET['error'])): ?>
            <?php
            $error = $_GET['error'];
            if ($error === 'wrongpass') $msg = "Password salah!";
            elseif ($error === 'nouser') $msg = "Username tidak ditemukan!";
            elseif ($error === 'empty') $msg = "Harap isi semua field!";
            else $msg = "Login gagal, coba lagi.";
            ?>
            <div class="alert"><?= $msg; ?></div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit">Masuk</button>
        </form>

        <p class="footer-text">© 2025 Sismontek</p>
    </div>
</body>
</html>
