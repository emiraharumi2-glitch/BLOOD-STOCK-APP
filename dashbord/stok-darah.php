<?php
session_start();
require_once '../db.php'; 
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
$username = $_SESSION['username']; 


$sql_masuk = "SELECT blood_type, size, COUNT(size) as total FROM blood_stock WHERE status = 'aktif' GROUP BY blood_type, size";
$result_masuk = $conn->query($sql_masuk); 
$blood_stock = [
    'A' => ['250' => 0, '350' => 0, '450' => 0],
    'B' => ['250' => 0, '350' => 0, '450' => 0],
    'O' => ['250' => 0, '350' => 0, '450' => 0],
    'AB' => ['250' => 0, '350' => 0, '450' => 0]
];
if ($result_masuk->num_rows > 0) {
    while ($row = $result_masuk->fetch_assoc()) {
        $blood_stock[$row['blood_type']][$row['size']] = $row['total'];
    }
}
$sql_keluar = "SELECT blood_type, size, COUNT(size) as total FROM blood_out GROUP BY blood_type, size";
$result_keluar = $conn->query($sql_keluar);
$blood_out = [
    'A' => ['250' => 0, '350' => 0, '450' => 0],
    'B' => ['250' => 0, '350' => 0, '450' => 0],
    'O' => ['250' => 0, '350' => 0, '450' => 0],
    'AB' => ['250' => 0, '350' => 0, '450' => 0]
];
if ($result_keluar->num_rows > 0) {
    while ($row = $result_keluar->fetch_assoc()) {
        $blood_out[$row['blood_type']][$row['size']] = $row['total'];
    }
}
$sql_masuk = "SELECT * FROM blood_stock WHERE status = 'aktif'"; //mengambil data masuk
$result_masuk = $conn->query($sql_masuk);
$sql_keluar = "SELECT * FROM blood_out"; //mengambil data keluar
$result_keluar = $conn->query($sql_keluar);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lihat Stok Darah</title>
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
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 15px 20px rgba(245, 12, 12, 0.1);
            padding: 20px;
            width: 380px; 
            text-align: center;
            margin-right: 20px; 
            display: flex; 
            flex-direction: column;
            height: 100vh;
        }
        .fixed-part {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
            padding-bottom: 0px;
        }
        .title {
            font-size: 40px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #fff;
            background: linear-gradient(-135deg, rgb(224, 17, 17));
            border-radius: 15px;
            padding: 10px;
            font-style: italic;
        }
        .data-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .data-card {
            flex: 1;
            background:rgb(234, 233, 253);
            border-radius: 12px;
            padding: 20px;
            margin-right: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .data-card:last-child {
            margin-right: 0; 
        }
        .scrollable-part {
            flex: 1;
            overflow-y: auto;
            padding-top: 0px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color:rgb(234, 233, 253);
        }
    </style>
</head>
<body>
    <div class="sidebar">
    <div class="greeting" style= "
            white-space: pre-line; 
            margin-top: 0; /* Menghilangkan margin atas */
            padding-top: 0; /* Menghilangkan padding atas */
            line-height: 1.5; /* Mengatur jarak antar baris */">
            Hallo!
            Selamat Datang 
            <?php echo htmlspecialchars($username); ?>
        </div>
    <nav>
        <a href="index.php" class="back-button">Halaman Dashboard</a>
        <a href="input-data-baru.php" class="back-button">Input Data Baru</a>
    </nav>
    <button class="logout" onclick="window.location.href='../login.php'">Logout</button>
    </div>
    <div class="wrapper">
        <div class="fixed-part">
        <div class="title">Stok Darah</div>
        <div class="data-box">
            <div class="data-card">
                <h3 style = "display: inline-block; margin-right: 10px;">Data Masuk</h3>
                <span style="background: #007bff; color: white; border: none; padding: 7px 15px; border-radius: 8px; cursor: pointer;">
                    <?php echo array_sum(array_column($blood_stock, '250')) + array_sum(array_column($blood_stock, '350')) + array_sum(array_column($blood_stock, '450')); ?> unit
                </span>
            </div>
            <div class="data-card">
                <h3 style = "display: inline-block; margin-right: 10px;">Data Keluar</h3>
                <span style="background: #007bff; color: white; border: none; padding: 7px 15px; border-radius: 8px; cursor: pointer;">
                    <?php echo array_sum(array_column($blood_out, '250')) + array_sum(array_column($blood_out, '350')) + array_sum(array_column($blood_out, '450')); ?> unit <!-- Ganti dengan logika yang sesuai untuk data keluar -->
                </span>
         </div>
         </div>
        </div>
         <div class="scrollable-part">
        <table>
            <thead>
                <tr>
                    <th>Golongan Darah</th>
                    <th>250 ml</th>
                    <th>350 ml</th>
                    <th>450 ml</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A</td>
                    <td><?php echo $blood_stock['A']['250']; ?> unit</td>
                    <td><?php echo $blood_stock['A']['350']; ?> unit</td>
                    <td><?php echo $blood_stock['A']['450']; ?> unit</td>
                </tr>
                <tr>
                    <td>B</td>
                    <td><?php echo $blood_stock['B']['250']; ?> unit</td>
                    <td><?php echo $blood_stock['B']['350']; ?> unit</td>
                    <td><?php echo $blood_stock['B']['450']; ?> unit</td>
                </tr>
                <tr>
                    <td>O</td>
                    <td><?php echo $blood_stock['O']['250']; ?> unit</td>
                    <td><?php echo $blood_stock['O']['350']; ?> unit</td>
                    <td><?php echo $blood_stock['O']['450']; ?> unit</td>
                </tr>
                <tr>
                    <td>AB</td>
                    <td><?php echo $blood_stock['AB']['250']; ?> unit</td>
                    <td><?php echo $blood_stock['AB']['350']; ?> unit</td>
                    <td><?php echo $blood_stock['AB']['450']; ?> unit</td>
                </tr>
            </tbody>
        </table>
        <div class="data-summary">
            <h3>Data Masuk</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Golongan Darah</th>
                        <th>Ukuran</th>
                        <th>Tanggal Masuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_masuk->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row_masuk = $result_masuk->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo  $no++;  ?></td>
                                <td><?php echo $row_masuk['blood_type']; ?></td>
                                <td><?php echo $row_masuk['size']; ?> ml</td>
                                <td><?php echo date ('H:i:s / d-m-Y', strtotime($row_masuk['tanggal_masuk'])); ?></td>
                                <td>
                                    <a href="edit-hapus.php?id=<?= $row_masuk['id']; ?>&action=edit">Edit</a> |
                                    <a href="edit-hapus.php?id=<?= $row_masuk['id']; ?>&action=hapus"
                                    onclick="return confirm('Yakin hapus data ini?')">
                                    Hapus
                                    </a>
                                </td>
                             </tr>
                    

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Tidak ada data masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="data-summary">
            <h3>Data Keluar</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Golongan Darah</th>
                        <th>Ukuran</th>                        
                        <th>Tanggal masuk</th>
                        <th>Tanggal Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_keluar->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row_keluar = $result_keluar->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row_keluar['blood_type']; ?></td>
                                <td><?php echo $row_keluar['size']; ?> ml</td>
                                <td><?php echo date ('H:i:s / d-m-Y', strtotime($row_keluar['tanggal_masuk'])); ?></td> <!-- Y=0000 y=00 -->
                                <td><?php echo date ('H:i:s / d-m-Y', strtotime($row_keluar['date_out'])); ?></td> <!-- Y=0000 y=00 -->
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Tidak ada data keluar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>
</html>
