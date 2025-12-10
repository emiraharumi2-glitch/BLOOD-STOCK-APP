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

// ===================== 1. AMBIL ID DARI QR =====================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $error = "QR Code tidak valid. ID tidak ditemukan.";
}

// ===================== 2. AMBIL DATA DARI DATABASE BERDASARKAN ID =====================
$blood_type = "";
$size = "";
$tanggal_masuk = "";
$tanggal_masuk_post = "";
$tanggal_masuk_display = "";
$date_out = "";
$date_out_display = "";


if (!$error) {
    $stmt = $conn->prepare("SELECT blood_type, size, tanggal_masuk FROM blood_stock WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($blood_type, $size, $tanggal_masuk);

    if (!$stmt->fetch()) {
        // Jika tidak ada → berarti stok sudah digunakan
        $used = true;
    }
    $stmt->close();
    

    if (!$used) {
        // Format tanggal untuk tampilan
        $dateTime = new DateTime($tanggal_masuk);
        $tanggal_masuk_post = $dateTime->format('Y-m-d H:i:s');
        $tanggal_masuk_display = $dateTime->format('H:i:s / d-m-Y');
    }
    if ($used) {
    // AMBIL DATA DARI blood_out
    $stmt2 = $conn->prepare("SELECT blood_type, size, tanggal_masuk, date_out FROM blood_out WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->bind_result($blood_type, $size, $tanggal_masuk, $date_out);
    $stmt2->fetch();
    $stmt2->close();
}
}
if ($used && !empty($tanggal_masuk)) {
    $dt = new DateTime($tanggal_masuk);
    $tanggal_masuk_display = $dt->format('H:i:s / d-m-Y');
}
if ($used && !empty($date_out)) {
    $dt2 = new DateTime($date_out);
    $date_out_display = $dt2->format('H:i:s / d-m-Y');
}


// ===================== 3. KONFIRMASI PENGGUNAAN =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_usage'])) {
    
    // SIMPAN DATA SEBELUM DIHAPUS
    $old_blood_type = $blood_type;
    $old_size = $size;
    $old_tanggal_masuk = $tanggal_masuk;

    $conn->begin_transaction();
    try {
        // 1. Pindahkan ke tabel blood_out
        $stmt_insert = $conn->prepare("
            INSERT INTO blood_out (id, blood_type, size, tanggal_masuk, date_out, is_used)
            VALUES (?, ?, ?, ?, NOW(), 1)
        ");
        $stmt_insert->bind_param("isss", $id, $blood_type, $size, $tanggal_masuk_post);
        $stmt_insert->execute();
        $stmt_insert->close();

        // 2. Hapus dari stok darah
        $stmt_del = $conn->prepare("DELETE FROM blood_stock WHERE id = ? LIMIT 1");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();

        if ($stmt_del->affected_rows === 0) {
            throw new Exception("Data tidak ditemukan untuk dihapus.");
        }

        
        $stmt_del->close();
        $conn->commit();

        $success = "Stok darah berhasil digunakan.";
        $used = true;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal menggunakan stok darah: " . $e->getMessage();
    }
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
            flex-grow: 1; /* Memungkinkan nav untuk mengambil ruang yang tersedia */
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
            margin-top: auto; /* Memindahkan tombol logout ke bawah */
            margin-bottom: 20px;
            margin-left: 10px; 
            margin-right: 10px; 
            background: #ef476f;
            color: white;
            font-weight: 700;
            font-size: 15px; 
            padding: 5px 10px; /*jarak box dari font */
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
            background-image: url('../images/h.png'); /* Ganti dengan path gambar Anda */
            background-size: cover; /* Memastikan gambar menutupi seluruh area */
            background-position: center; /* Memposisikan gambar di tengah */
            background-repeat: no-repeat;
            width: 380px;
            border-radius: 10px;
            box-shadow: 0px 15px 20px rgba(245, 12, 12, 0.1);
            padding: 20px;
            margin-right: 20px; /* Jarak antara konten utama dan sidebar */
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
            align-items: center; /* Pindahkan ke tengah */
            color: #262626;
            font-size: 25px;
            text-align: center; /* Pindahkan teks ke tengah */
            background-color: #fff;
            width: 100%; /* Pastikan lebar data penuh */
            padding: 15px; /* Tambahkan padding untuk data */
            border-radius: 10px; /* Tambahkan border-radius untuk data */
        }
        .data-item {
            display: flex; /* Selaras tabel dan nilai horizontal */
            justify-content: space-between; /* space-between Menyebar teks, flex-start untuk kekiri dan tengah*/
            width: 100%; /* Mengatur lebar penuh */
            align-items: center; /* Pusatkan secara vertikal */
        }
        .label {
            font-weight: 700;
            color:rgb(0, 0, 0);
            text-align: left;
            font-size: 20px; 
            width: 190px; /* Lebar label */
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
            width: 20px; /* Lebar tanda = */
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
            margin-top: 20px; /* Tambahkan margin atas untuk jarak */
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
        .used {  /* Baru: Style untuk pesan "sudah digunakan" (oranye, mirip success tapi warning) */
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
            <?php elseif ($used): ?>  <!-- Baru: Tampilkan pesan jika sudah digunakan -->
                <div class="used">Stok darah sudah digunakan</div>
            <?php endif; ?>
            <?php if (!$error && ($blood_type || $used)): ?>
                <!-- Tampilkan data hanya jika valid (tidak ada error dan data ada) -->
               <?php 
                // Jika sudah digunakan, tampilkan kembali data lama
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

                <?php if (!$used): ?>  <!-- Baru: Hilangkan form/tombol jika sudah used -->
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
                <?php endif; ?>  <!-- Tutup kondisi if (!$used) -->
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
