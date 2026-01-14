<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
$username = $_SESSION['username']; 
// Inisialisasi variabel untuk menampilkan nilai form jika ada
$blood_type_val = '';
$size_val = '';
$tanggal_masuk_val = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_type = $_POST['blood_type'];
    $size = $_POST['size'];
    $tanggal_input = $_POST['tanggal_masuk']; 
    $date_obj = DateTime::createFromFormat('d-m-Y', $tanggal_input); // Konversi  d-m-Y ke Y-m-d 
    if ($date_obj) {
        $tanggal_input_db = $date_obj->format('Y-m-d');
    } else {
        $tanggal_input_db = null; 
    }
    $blood_type_val = $blood_type;
    $size_val = $size;
    $tanggal_masuk_val = $tanggal_input;
    $waktu_sekarang = date('H:i:s'); 
    if ($tanggal_input_db !== null) {
        $tanggal_masuk = $tanggal_input_db . ' ' . $waktu_sekarang;
    } else {
        $tanggal_masuk = date('Y-m-d') . ' ' . $waktu_sekarang;
    }
    $stmt = $conn->prepare("INSERT INTO blood_stock (blood_type, size, tanggal_masuk) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $blood_type, $size, $tanggal_masuk);
    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        header("Location: QRcode.php?id=" . $last_id);
        exit();
    } else {
        echo "<script>alert('Terjadi kesalahan: " . $stmt->error . "');</script>";
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Input Data Baru</title>
    <link rel="stylesheet" href="../assets/flatpickr-4.6.13/dist/flatpickr.min.css">
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
        .wrapper form .field {
            height: 45px;
            width: 100%;
            margin-top: 12px;
            position: relative;
        }
        .wrapper form .field select,
        .wrapper form .field input[type="text"] {
            height: 100%;
            width: 100%;
            outline: none;
            font-size: 20px;
            padding-left: 20px;
            border: 1px solid lightgrey;
            border-radius: 25px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            color: #0e0d0d99; 
        }
        .wrapper form .field select:focus,
        .wrapper form .field input[type="text"]:focus {
            border-color: #4158d0;
        }
        input.flatpickr-alt-input {
            height: 100% !important;
            width: 100% !important;
            padding-left: 20px !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 20px !important;
            border-radius: 25px !important;
            border: 1px solid lightgrey !important;
            outline: none !important;
            box-sizing: border-box !important;
            transition: all 0.3s ease !important;
            background: transparent !important;
            cursor: pointer;
            color: #a18c8cff !important;
        }
        input.flatpickr-alt-input:focus {
            border-color: #4158d0 !important;
            box-shadow: none !important;
        }
        input.flatpickr-alt-input::placeholder,
        input.flatpickr-alt-input::-webkit-input-placeholder,
        input.flatpickr-alt-input:-moz-placeholder,
        input.flatpickr-alt-input::-moz-placeholder,
        input.flatpickr-alt-input:-ms-input-placeholder {
            color: #222222 !important;
            opacity: 1 !important;
        }
        .flatpickr-input {
            display: none !important;
        }
        .submit-container {
            display: block;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            width: 100%; 
        }
        .submit-container input[type="submit"] {
            text-decoration: none;
            color: #fff;
            padding: 10px 0;
            font-size: 25px;
            font-weight: 500;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            background: linear-gradient(-135deg,rgb(224, 17, 17),rgb(236, 105, 105));
        }
        .submit-container input[type="submit"]:active {
            transform: scale(0.95);
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
            <a href="stok-darah.php">Lihat Stok Darah</a>
        </nav>
        <button class="logout" onclick="window.location.href='../login.php'">Logout</button>
    </div>
    <div class="wrapper">
        <div class="title">
            Input Data Baru
        </div>
        <form method="post" action="">
            <div class="field">
                <select name="blood_type" required class="<?php echo ($blood_type_val == '') ? 'placeholder' : ''; ?>">
                    <option value="" disabled <?php echo ($blood_type_val == '') ? 'selected' : ''; ?>>Pilih Golongan Darah</option>
                    <option value="A" <?php echo ($blood_type_val == 'A') ? 'selected' : ''; ?>>A</option>
                    <option value="B" <?php echo ($blood_type_val == 'B') ? 'selected' : ''; ?>>B</option>
                    <option value="O" <?php echo ($blood_type_val == 'O') ? 'selected' : ''; ?>>O</option>
                    <option value="AB" <?php echo ($blood_type_val == 'AB') ? 'selected' : ''; ?>>AB</option>
                </select>
            </div>
            <div class="field">
                <select name="size" required class="<?php echo ($size_val == '') ? 'placeholder' : ''; ?>">
                    <option value="" disabled <?php echo ($size_val == '') ? 'selected' : ''; ?>>Pilih Ukuran</option>
                    <option value="250" <?php echo ($size_val == '250') ? 'selected' : ''; ?>>250 ml</option>
                    <option value="350" <?php echo ($size_val == '350') ? 'selected' : ''; ?>>350 ml</option>
                    <option value="450" <?php echo ($size_val == '450') ? 'selected' : ''; ?>>450 ml</option>
                </select>
            </div>
            <div class="field">
                <input type="text" id="tanggal_masuk" name="tanggal_masuk" required value="<?php echo htmlspecialchars($tanggal_masuk_val); ?>" placeholder="Pilih tanggal">
            </div>

            <div class="submit-container">
                <input type="submit" value="Simpan">
            </div>
        </form>
    </div>
    <script src="../assets/flatpickr-4.6.13/dist/flatpickr.min.js"></script>
    <script>
        flatpickr("#tanggal_masuk", {
            dateFormat: "d-m-Y",
            altInput: true,
            altFormat: "d-m-Y",
            allowInput: true
        });
        document.querySelectorAll('select').forEach(function(select) {
            function updateClass() {
                if (select.value === '') {
                    select.classList.add('placeholder');
                } else {
                    select.classList.remove('placeholder');
                }
            }
            updateClass();
            select.addEventListener('change', updateClass);
        });
    </script>
</body>
</html>
