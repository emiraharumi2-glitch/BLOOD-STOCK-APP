<?php
session_start();
require_once 'db.php';
$error = null; 
$success = null; 

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if ($username === '' || $password === '') {
        $error = "Username atau password tidak diisi.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        if ($data && password_verify($password, $data['password'])) { 
            $_SESSION['username'] = $data['username'];
            header("Location: dashbord/index.php");
            exit(); 
        } else {
            $error = "Login gagal! Username atau password salah."; 
        }
        $stmt->close();
    } 
}

if (isset($_POST['register_username']) && isset($_POST['register_email']) && isset($_POST['register_password']) && isset($_POST['confirm_password'])) {
    $register_username = $_POST['register_username'];
    $register_email = $_POST['register_email'];
    $register_password = $_POST['register_password'];
    $confirm_password = $_POST['confirm_password'];
    if ($register_username === '' || $register_email === '' || $register_password === '' || $confirm_password === '') { 
        $error = "Semua field harus diisi.";
    } elseif ($register_password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $register_username, $register_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            $hashed_password = password_hash($register_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $register_username, $register_email, $hashed_password);
            if ($stmt->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
                //kirim email konfirmasi 
                require 'vendor/autoload.php'; 
                $mail = new PHPMailer\PHPMailer\PHPMailer(true); 
                try { 
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Server email
                    $mail->SMTPAuth = true;
                    $mail->Username = 'bloodstock.app1@gmail.com'; // email 
                    $mail->Password = 'gamx qtpv kzyz tapt'; // Sandi app (keamanan email)
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    //penerima email 
                    $mail->setFrom('bloodstock.app1@gmail.com','Blood Stock App'); 
                    $mail->addAddress($register_email); 
                    $mail->isHTML(true); 
                    $mail->Subject = 'Selamat Datang di Blood-Stock-APP';
                    $mail->Body = '
                        <p> Kepada '. htmlspecialchars($register_username) .', </p>
                        <p> Selamat datang di Blood Stock APP! Terimakasih telah bergabung.</p>
                        <p> untuk memulai silakan lihat panduan penggunaan yang kami lampirkan di email ini:</p>
                        <ul> 
                            <li> Akses Akun: <a href = "[http://blood-app.local/]"> http://blood-app.local/ </a></li>
                        </ul>  
                        <p> Jika ada pertanyaan, hubungi: <a href="mailto:bloodstock.app1@gmail.com">bloodstock.app1@gmail.com</a></p>
                        <p> Salam, <br> Tim Blood-Stock-APP</p> 
                        ';
                    $mail->AltBody = 'Kepada'.htmlspecialchars($register_username) . ', Selamat bergabung dengan Blood-Stock-APP! Kami terima kasih atas partisipasi Anda. Untuk memulai: Akses Akun: [tautan akses akun], Panduan Pengguna: [tautan panduan pengguna], Bantuan: [Harumidina0@gmail.com]. Mari bersama-sama membantu memenuhi kebutuhan stok darah. Salam, Tim Blood-Stock-APP'; 
                    $pdf_path = __DIR__ . '/Panduan_pengguna/Panduan_pengguna.pdf';
                    if (file_exists($pdf_path)) {
                    $mail->addAttachment($pdf_path, 'Panduan_Pengguna.pdf');
                    } else {
                    error_log("File PDF panduan tidak ditemukan: $pdf_path");
                    }   
                    $mail->send(); 
                } catch (Exception $e) {
                    echo "Email tidak dapat dikirim. Kesalahan: {$mail->ErrorInfo}"; 
                }
            } else {
                $error = "Pendaftaran gagal! Username atau email sudah terdaftar.";
            }
            $stmt->close(); 
        }
    }   
}
$conn->close(); // Menutup koneksi
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        html, body {
            display: grid;
            height: 100%;
            width: 100%;
            place-items: center;
            background-image: url('images/gg.png');
            background-size: cover; 
            background-position: center center; 
            background-repeat: no-repeat; 
            background-attachment: fixed; 
        }
        .wrapper {
            width: 500px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1); 
            position: relative;
            overflow: hidden;
        }
        .wrapper .title {
            font-size: 35px;
            font-weight: 600;
            text-align: center;
            line-height: 100px;
            color: #fff;
            user-select: none;
            border-radius: 15px 15px 0 0;
            background: linear-gradient(-135deg,rgb(224, 17, 17));
            font-style: italic;
        }
        .wrapper form {
            padding: 10px 30px 30px 30px;
        }
        .wrapper form .field {
            height: 50px;
            width: 100%;
            margin-top: 20px;
            position: relative;
        }
        .wrapper form .field input {
            height: 100%;
            width: 100%;
            outline: none;
            font-size: 17px;
            padding-left: 20px;
            border: 1px solid lightgrey;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .wrapper form .field input:focus {
            border-color: #4158d0;
        }
        .wrapper form .field label {
            position: absolute;
            top: 50%;
            left: 20px;
            color: #999999;
            font-weight: 400;
            font-size: 17px;
            pointer-events: none;
            transform: translateY(-50%);
            transition: all 0.3s ease;
        }
        form .field input:focus~label,
        form .field input:valid~label {
            top: 0%;
            font-size: 16px;
            color: #4158d0;
            background: #fff;
            transform: translateY(-50%);
        }
        form .field input[type="submit"] {
            color: #fff;
            border: none;
            padding-left: 0;
            margin-top: -10px;
            font-size: 20px;
            font-weight: 500;
            cursor: pointer;
            background: linear-gradient(-135deg,rgb(224, 17, 17));
            transition: all 0.3s ease;
        }
        form .field input[type="submit"]:active {
            transform: scale(0.95);
        }
        form .signup-link {
            color: #262626;
            margin-top: 20px;
            text-align: center;
        }
        form .pass-link a,
        form .signup-link a {
            color: #4158d0;
            text-decoration: none;
        }
        form .pass-link a:hover,
        form .signup-link a:hover {
            text-decoration: underline;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            background: none;
            border: none;
            font-size: 18px;
            padding: 5px;
            transition: color 0.3s ease;
        }
        .toggle-password:hover {
            color: #4a90e2;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title" id="formTitle">Login</div>
        <?php if ($error): ?>
            <div style="color: red; text-align: center;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: green; text-align: center;"><?php echo $success; ?></div>
        <?php endif; ?>
        <form id="loginForm" action="login.php" method="post">
            <div class="field">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" required>
                <label>Password</label>
                <button type="button" class="toggle-password" aria-label="Show password" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="field">
                <input type="submit" value="Login">
            </div>
            <div class="signup-link">
                <p>Belum punya akun? <a href="#" onclick="showRegisterForm()">Register</a></p>
            </div>
        </form>
        <form id="registerForm" style="display: none;" action="login.php" method="post">
            <div class="field">
                <input type="email" name="register_email" required>
                <label>Email</label>
            </div>
            <div class="field">
                <input type="text" name="register_username" required>
                <label>Username</label>
            </div>
            <div class="field">
                <input type="password" name="register_password" id="register_password" required>
                <label>Password</label>
                <button type="button" class="toggle-password" aria-label="Show password" id="toggleRegisterPassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="field">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <label>Konfirmasi Password</label>
                <button type="button" class="toggle-password" aria-label="Show password" id="toggleConfirmPassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="field">
                <input type="submit" value="Register">
            </div>
            <div class="signup-link">
                <p>Sudah punya akun? <a href="#" onclick="showLoginForm()">Login</a></p>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
        });
        document.getElementById('toggleRegisterPassword').addEventListener('click', function() {
            const registerPasswordInput = document.getElementById('register_password');
            const icon = this.querySelector('i');
            const type = registerPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            registerPasswordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
        });
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
        });
        function showRegisterForm() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('formTitle').innerText = "Pembuatan Akun";
        }
        function showLoginForm() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('formTitle').innerText = "Form Login";
        }
    </script>
</body>
</html>
