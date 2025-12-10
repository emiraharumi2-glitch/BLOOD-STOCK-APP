<?php
session_start();
// Memastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
//ambil username dari halaman login 
$username = $_SESSION ['username'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
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
            background: #f2f2f2;
            
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
            color: #222;
            text-align: center;
            font-style: italic;
            margin: 0; 
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
            padding: 10px 15px;
            border-radius: 8px;
            background: rgb(192, 71, 81);
            text-align: center;
            transition: background-color 0.3s ease, color 0.3s ease;
            cursor: pointer;
            display: block; /* Mengubah menjadi block agar memenuhi lebar */
        }

        .sidebar nav a:hover {
            background: rgb(214, 65, 65);
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
            background-repeat: no-repeat; /* Menghindari pengulangan gambar */
            border-radius: 10px;
            box-shadow: 0px 20px 20px rgba(245, 12, 12, 0.1);
            padding: 0px;
            text-align: center;
            margin-right: 20px; /* Jarak antara konten utama dan sidebar */
            color: white; /* Ubah warna teks jika perlu */
        }

        .wrapper .title {
            font-size: 45px;
            font-weight: 1000;
            line-height: 105px;
            user-select: none;
            border-radius: 10px 10px 0 0;
            background: linear-gradient(-135deg,rgb(224, 17, 17));
            font-style: italic; 
        }

        .wrapper .options {
            font-size: 25px;
            font-weight: 500; /*tebal font */
            line-height: 30px;
            padding: 100px;
            display: flex;
            flex-direction: column; /* Tombol vertikal */
            align-items: center; /* Tombol ditengah horizontal */
            padding: 20px; 
            gap: 13px; /* Jarak antar tombol */
        }

        .wrapper .options a {
            width: 400px; /* Mengatur lebar tombol */
            padding: 15px;
            margin: 10px auto; /* Mengatur margin agar berada di tengah */
            text-decoration: none;
            color: #fff;
            background: linear-gradient(-135deg,rgb(224, 17, 17));
            border-radius: 25px;
            transition: background 0.3s ease;
        }

        .wrapper .options a:hover {
            background: linear-gradient(-135deg,rgb(214, 37, 37));
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="greeting" style= "
        white-space: pre-line;
        margin-top: 0; /* Menghilangkan margin atas */
        padding-top: 0; /* Menghilangkan padding atas */
        line-height: 1.5; /* Mengatur jarak antar baris */
        ">
        Hallo! 
        Selamat Datang 
        <?php echo htmlspecialchars($username); ?>
    </div>
    </nav>
    <button class="logout" onclick="window.location.href='../login.php'">Logout</button>
    </div>

    <div class="wrapper">
        <div class="title">
            Dashboard
        </div>
        <div class="options">
            <a href="input-data-baru.php">Input Data Baru</a>
            <a href="stok-darah.php">Lihat Stok</a>
        </div>
    </div>
</body>

</html>
