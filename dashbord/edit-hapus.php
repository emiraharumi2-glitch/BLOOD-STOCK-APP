<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id <= 0 || !in_array($action, ['edit','hapus'])) {
    header("Location: stok-darah.php");
    exit();
}

/* ================= MODE HAPUS ================= */
if ($action === 'hapus') {
    $stmt = $conn->prepare(
        "UPDATE blood_stock SET status='hapus' WHERE id=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->close();
    header("Location: stok-darah.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT blood_type, size, DATE(tanggal_masuk) AS tanggal_masuk
     FROM blood_stock
     WHERE id=? AND status='aktif'"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $conn->close();
    header("Location: stok-darah.php");
    exit();
}

$blood_type_val    = $data['blood_type'];
$size_val          = $data['size'];
$tanggal_masuk_val = date('d-m-Y', strtotime($data['tanggal_masuk']));

/* ================= SIMPAN EDIT ================= */
if (isset($_POST['simpan'])) {

    $blood_type    = $_POST['blood_type'];
    $size          = $_POST['size'];
    $tanggal_input = $_POST['tanggal_masuk'];
    $allowed_blood = ['A','B','O','AB'];
    $allowed_size  = ['250','350','450'];

    if (!in_array($blood_type, $allowed_blood) || !in_array($size, $allowed_size)) {
        $conn->close();
        header("Location: stok-darah.php");
        exit();
    }

    $date_obj = DateTime::createFromFormat('d-m-Y', $tanggal_input);
    $tanggal_db = $date_obj
        ? $date_obj->format('Y-m-d') . ' ' . date('H:i:s')
        : date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        "UPDATE blood_stock
         SET blood_type=?, size=?, tanggal_masuk=?
         WHERE id=?"
    );
    $stmt->bind_param("sisi", $blood_type, $size, $tanggal_db, $id);
    $stmt->execute();
    $conn->close();
    header("Location: stok-darah.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Data Darah</title>

<link rel="stylesheet" href="/assets/flatpickr-4.6.13/dist/flatpickr.min.css">

<style>
@import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

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
    color: #fff;
    user-select: none;
    border-radius: 10px 10px 0 0;
    background: linear-gradient(-135deg, rgb(224, 17, 17));
    font-style: italic;
}

.wrapper form .field {
    height: 45px;
    width: 100%;
    margin-top: 12px;
}

.wrapper form select,
.wrapper form input[type="text"] {
    height: 100%;
    width: 100%;
    font-size: 20px;
    padding-left: 20px;
    border-radius: 25px;
    border: 1px solid lightgrey;
}

.submit-container {
    margin-top: 30px;
}

.submit-container input {
    width: 100%;
    padding: 10px 0;
    font-size: 22px;
    font-weight: 500; 
    border-radius: 20px;
    border: none;
    color: #fff;
    background: linear-gradient(-135deg, rgb(224, 17, 17), rgb(236, 105, 105));
    cursor: pointer;
}

.submit-container a {
    display: block;
    margin-top: 12px;
    padding: 10px;
    border-radius: 20px;
    background: #e0e0e0;
    text-decoration: none;
    color: #333;
}
.submit-container .btn-cancel {
    width: 100%;
    padding: 10px 0;
    font-size: 22px;
    font-weight: 500;
    border-radius: 20px;
    text-align: center;
    text-decoration: none;
    display: block;
    margin-top: 12px;
    background: #dcdcdc;
    color: #333;
    transition: all 0.3s ease;
}

.submit-container .btn-cancel:hover {
    background: #bdbdbd;
}
</style>
</head>

<body>
<div class="sidebar">
    <div class="greeting">
        Hallo!<br>
        Selamat Datang<br>
        <?= htmlspecialchars($_SESSION['username']); ?>
    </div>

    <nav>
        <a href="index.php">Halaman Dashboard</a>
        <a href="input-data-baru.php">Input Data Baru</a>
        <a href="stok-darah.php">Lihat Stok Darah</a>
    </nav>

    <button class="logout" onclick="window.location.href='../login.php'">
        Logout
    </button>
</div>

<div class="wrapper">
    <div class="title">Edit Data Kantong Darah</div>

    <form method="post">
        <div class="field">
            <select name="blood_type" required>
                <option value="A" <?= $blood_type_val=='A'?'selected':'' ?>>A</option>
                <option value="B" <?= $blood_type_val=='B'?'selected':'' ?>>B</option>
                <option value="O" <?= $blood_type_val=='O'?'selected':'' ?>>O</option>
                <option value="AB" <?= $blood_type_val=='AB'?'selected':'' ?>>AB</option>
            </select>
        </div>

        <div class="field">
            <select name="size" required>
                <option value="250" <?= $size_val=='250'?'selected':'' ?>>250 ml</option>
                <option value="350" <?= $size_val=='350'?'selected':'' ?>>350 ml</option>
                <option value="450" <?= $size_val=='450'?'selected':'' ?>>450 ml</option>
            </select>
        </div>

        <div class="field">
            <input type="text"
                   id="tanggal_masuk"
                   name="tanggal_masuk"
                   value="<?= htmlspecialchars($tanggal_masuk_val); ?>"
                   required>
        </div>

        <div class="submit-container">
            <input type="submit" name="simpan" value="Simpan Perubahan">
            <a href="stok-darah.php" class = "btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script src="/assets/flatpickr-4.6.13/dist/flatpickr.min.js"></script>
<script>
flatpickr("#tanggal_masuk", {
    dateFormat: "d-m-Y",
    altInput: true,
    altFormat: "d-m-Y",
    allowInput: true
});
</script>
</body>
</html>

