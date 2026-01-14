   <?php
   require 'vendor/autoload.php';
   use PHPMailer\PHPMailer\PHPMailer;
   $mail = new PHPMailer();
   if ($mail) {
       echo "PHP Mailer is installed and ready to use!";
   } else {
       echo "PHP Mailer is not installed.";
   }
   