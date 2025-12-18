<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$error = null;
$success = null;
$used = false;

// ===================== AMBIL ID DARI QR =====================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $error = "QR Code tidak valid. ID tidak ditemukan.";
}
// ========AMBIL DATA DARI DATABASE BERDASARKAN ID =======
$blood_type = "";
$size = "";
$tanggal_masuk = "";
$tanggal_masuk_post = "";
$tanggal_masuk_display = "";
$date_out = "";
$date_out_display = "";

$found_active = false;

/* ================= CEK DATA AKTIF ================= */
$stmt = $conn->prepare("
    SELECT blood_type, size, tanggal_masuk
    FROM blood_stock
    WHERE id = ? AND status = 'aktif'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($blood_type, $size, $tanggal_masuk);

if ($stmt->fetch()) {
    $found_active = true;
}
$stmt->close();

/* ================= JIKA MASIH AKTIF ================= */
if ($found_active) {

    $dateTime = new DateTime($tanggal_masuk);
    $tanggal_masuk_post = $dateTime->format('Y-m-d H:i:s');
    $tanggal_masuk_display = $dateTime->format('H:i:s / d-m-Y');

}
/* ================= JIKA TIDAK AKTIF ================= */
else {

    // CEK APAKAH SUDAH DIGUNAKAN
    $stmt2 = $conn->prepare("
        SELECT blood_type, size, tanggal_masuk, date_out
        FROM blood_out
        WHERE id = ?
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->bind_result($blood_type, $size, $tanggal_masuk, $date_out);

    if ($stmt2->fetch()) {

        // ✅ BENAR-BENAR SUDAH DIGUNAKAN
        $used = true;
        $tanggal_masuk_display = (new DateTime($tanggal_masuk))->format('H:i:s / d-m-Y');
        $date_out_display = (new DateTime($date_out))->format('H:i:s / d-m-Y');

    } else {

        // ❌ DIHAPUS SEBELUM DIGUNAKAN
        $error = "Data tidak ditemukan atau telah dihapus.";

    }
    $stmt2->close();
}

?>

<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Informasi Data Kantong Darah</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background: rgb(255, 253, 253);
        }
        .sidebar {
            width: 220px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            user-select: none;
            margin-left: 20px; /* Jarak antara sidebar dan konten utama */
        }
        .sidebar .greeting {
            font-size: 23px;
            font-weight: 600;
            font-style: italic; 
            color: #222;
            margin-bottom: 20px;
            margin-top: 35px; 
            text-align: center;
        }
        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex-grow: 1; 
        }
        .sidebar nav a {
            text-decoration: none;
            color: #4178d6;
            font-weight: 600;
            margin-left: 15px; 
            margin-right: 15px; 
            padding: 10px 12px;
            border-radius: 8px;
            background: #e6f0ff;
            text-align: center;
            transition: background-color 0.3s ease, color 0.3s ease;
            cursor: pointer;
        }
        .sidebar nav a:hover {
            background: #4178d6;
            color: white;
        }
        .logout {
            margin-top: auto; 
            margin-bottom: 20px;
            margin-left: 10px; 
            margin-right: 10px; 
            background: #ef476f;
            color: white;
            font-weight: 700;
            font-size: 15px; 
            padding: 5px 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .sidebar .logout:hover {
            background:rgb(163, 33, 70);
        }
        .wrapper {
            flex: 1;

            width: 380px;
            border-radius: 10px;
            box-shadow: 0px 15px 20px rgba(245, 12, 12, 0.1);
            padding: 20px;
            margin-right: 20px; 
            text-align: center;
        }
        .wrapper .title {
            font-size: 45px;
            font-weight: 1000;
            line-height: 105px;
            color: rgb(255, 255, 255);
            user-select: none;
            border-radius: 10px 10px 0 0;
            background: linear-gradient(-135deg,rgb(224, 17, 17));
            font-style: italic; 
        }
        .data {
            margin-top: 10px;
            flex-direction: column;
            row-gap: 22px;
            align-items: center;
            color: #262626;
            font-size: 25px;
            text-align: center; 
            background-color: #fff;
            width: 100%; 
            padding: 15px; 
            border-radius: 10px; 
        }
        .data-item {
            display: flex; 
            justify-content: space-between; 
            width: 100%;
            align-items: center; 
        }
        .label {
            font-weight: 700;
            color:rgb(0, 0, 0);
            text-align: left;
            font-size: 20px; 
            width: 190px; 
            margin-left: 45px; 
        }
        .value {
            text-align: left; 
            flex: 1; 
            font-weight: 500;
            font-size: 20px; 
            margin-left: 27px;
        }
        .equal-sign {
            font-weight: 700;
            color:rgb(0, 0, 0);
            text-align: center;
            font-size: 20px; 
            width: 20px; 
        }
        .btn-container {
            width: 100%; 
            margin-top: 25px; 
        }
        .btn {
            width: 100%;
            padding: 12px;
            font-size: 20px;
            font-weight: 500;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: linear-gradient(-135deg, rgb(224, 17, 17), rgb(236, 105, 105));
            color: white;
            margin-top: 20px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn:hover {
            filter: brightness(1.1);
        }
        .error {
            font-size: 18px;
            color: #dc2626;
            font-weight: 600;
            text-align: center;
            margin: 2px 0;
        }
        .success {
            font-size: 18px;
            color: #16a34a;
            font-weight: 600;
            text-align: center;
            margin: 2px 0;
        }
        .used {  
            font-size: 18px;
            color: #d97706;
            font-weight: 600;
            text-align: center;
            margin: 2px 0;
            background-color: #fef3c7;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="greeting" style="
            white-space: pre-line; 
            margin-top: 0; /* Menghilangkan margin atas */
            padding-top: 0; /* Menghilangkan padding atas */
            line-height: 1.5; /* Mengatur jarak antar baris */">
            Hallo!
            Selamat Datang 
            <?php echo htmlspecialchars($username); ?>
        </div>
        <nav>
            <a href="index.php">Halaman Dashboard</a>
            <a href="input-data-baru.php">Input Data Baru</a>
            <a href="stok-darah.php">Lihat Stok Darah</a>
        </nav>
        <button class="logout" onclick="window.location.href='../login.php'">Logout</button>
    </div>
    <div class="wrapper">
        <div class="title">
            <span>Informasi Data Kantong Darah</span>
        </div>
        <div class="data">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($used): ?>  
                <div class="used">Stok darah sudah digunakan</div>
            <?php endif; ?>
            <?php if (!$error && ($blood_type || $used)): ?>
               <?php 
                if ($used && isset($old_blood_type)) {
                    $blood_type = $old_blood_type;
                    $size = $old_size;
                    $tanggal_masuk = $old_tanggal_masuk;
                }
                ?>
                <div class="data-item">
                    <div class="label">Golongan Darah</div> 
                    <div class="equal-sign">=</div>
                    <div class="value"><?= htmlspecialchars($blood_type) ?></div>
                </div>
                <div class="data-item">
                    <div class="label">Berat Darah</div>
                    <div class="equal-sign">=</div>
                    <div class="value"><?= htmlspecialchars($size) ?> ml</div>
                </div>
                <div class="data-item">
                    <div class="label">Tanggal Donor</div>
                    <div class="equal-sign">=</div>
                    <div class="value"><?= htmlspecialchars($tanggal_masuk_display) ?></div>
                </div>
                <?php if ($used && $date_out_display): ?>
                <div class="data-item">
                    <div class="label">Tanggal Keluar</div>
                    <div class="equal-sign">=</div>
                    <div class="value"><?= htmlspecialchars($date_out_display) ?></div>
                </div>
            <?php endif; ?>

                <?php if (!$used): ?>  
                <form method="post" action="" id="use-blood-form">
                    <input type="hidden" name="size" value="<?= htmlspecialchars($size) ?>">
                    <input type="hidden" name="tanggal_masuk" value="<?= htmlspecialchars($tanggal_masuk_post) ?>">
                    <div class="btn-container">
                        <button type="submit" name="confirm_usage" class="btn" 
                            onclick="return confirm('Apakah Anda yakin ingin menggunakan stok darah ini?')">
                            Gunakan Stok Darah
                        </button>
                    </div>
                </form>
                <?php endif; ?>  
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
