<?php
session_start();
require_once '../db.php';
require_once '../phpqrcode/qrlib.php'; // pustaka QR Code 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
$username = $_SESSION['username'];
if (!isset($_GET['id']) || ($id = intval($_GET['id'])) <= 0) {
    die("ID data tidak ditemukan atau tidak valid.");
}
try {
    $stmt = $conn->prepare("SELECT blood_type, size, tanggal_masuk FROM blood_stock WHERE id = ? AND status = 'aktif'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($blood_type, $size, $tanggal_masuk);
    if (!$stmt->fetch()) {
        die("Data tidak ditemukan.");
    }
} catch (Exception $e) {
    die("Error database: " . $e->getMessage());
}

$stmt->close();
$conn->close();

$dateTime = new DateTime($tanggal_masuk); 
$formattedDateTime = $dateTime->format('H:i:s/d-m-Y'); 
// Siapkan data untuk QR Code
$data = "g=$blood_type&sz=$size&tm=$formattedDateTime";
$port = 80; 
$base_url = ($port == 80) ? "http://blood-app.local" : "http://blood-app.local:$port";
$scan_url = $base_url . "/dashbord/scanQR.php?id=" . $id;
error_log("URL untuk QR Code ID $id (singkat): " . $scan_url);
$qr_folder = 'phpqrcode'; // Folder di dalam dashbord
$filename = $qr_folder . '/QRcode' . $id . '.png'; 
if (!file_exists($qr_folder)) {
    mkdir($qr_folder, 0777, true); 
}
$abs_filename = __DIR__ . '/' . $filename; // Path lengkap untuk generate
if (file_exists($abs_filename)) {
}
QRcode::png($scan_url, $abs_filename, QR_ECLEVEL_H, 10, 4); 
$file_size = file_exists($abs_filename) ? filesize($abs_filename) : 0;
if ($file_size == 0) {
    error_log("Error: QR file $filename kosong atau gagal generate.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Kode QR Data Kantong Darah</title>
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
            margin-left: 20px; 
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
            background: rgb(163, 33, 70);
        }
        .container {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            margin-right: 10px; 
            font-size: 15px; 
        }
        h1 {
            /* Tanpa style khusus, default seperti kode kedua */
        }
        img {
            margin-top: 20px;
            max-width: 250px;
            max-height: 250px; 
            image-rendering: pixelated; 
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-print {
            background: #007bff;
            color: #fff;
            padding: 10px 0; 
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 20px; 
        }
        .btn-print:hover {
            background: #0056b3;
        }
        @media print { 
            .sidebar { display: none; }
            h1 { display: none; }
            body { margin: 0; }
            .container { box-shadow: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="greeting" style="white-space: pre-line; margin-top: 0; padding-top: 0; line-height: 1.5;">
            Hallo!
            Selamat Datang 
            <?php echo htmlspecialchars($username); ?>
        </div>
        <nav>
            <a href="index.php">Halaman Dashboard</a>
            <a href="input-data-baru.php">Input Data Baru</a>
            <a href="stok-darah.php">Lihat Stok Darah</a>
        </nav>
        <button class="logout" onclick="window.location.href='./login.php'">Logout</button>
    </div>
    <div class="container">
        <h1>Kode QR Data Kantong Darah</h1>
        <img src="<?= htmlspecialchars($filename) ?>" alt="Kode QR" id="qrimg" />
        <br />
        <div class="data">
            <div class="value" style="font-size: 20px; font-weight: bold;"><?= htmlspecialchars($blood_type) ?></div>
        </div>
        <div class="data">
            <div class="label">Tanggal Masuk:</div>
            <div class="value"><?= htmlspecialchars($formattedDateTime) ?></div>
        </div>
        <button class="btn-print" onclick="window.print()">Print</button>
    </div>
</body>
</html>
