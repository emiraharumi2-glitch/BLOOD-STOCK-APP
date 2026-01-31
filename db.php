<?php
$servername = "localhost"; // atau IP address server 
$username = "root"; // username server
$password = ""; // password database (kosong jika Anda tidak mengatur password)
$dbname = "blood"; // nama database yang Anda gunakan
// Membuat koneksi
$conn = new mysqli ($servername, $username, $password, $dbname);
// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->query("SET time_zone = '+07:00'"); //set waktu, +7.00 dar utc 7 (waktu sekarang) 
?>
