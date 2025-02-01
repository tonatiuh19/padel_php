<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms']) && isset($params['name']) && isset($params['email']) && isset($params['message'])) {
        $id_platforms = $params['id_platforms'];
        $name = $params['name'];
        $email = $params['email'];
        $message = $params['message'];
        $date = date('Y-m-d H:i:s'); // Set to current datetime

        // Query to insert data into the platforms_support table
        $sql = "INSERT INTO platforms_support (id_platforms, name, email, message, date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $id_platforms, $name, $email, $message, $date);

        if ($stmt->execute()) {
            $stmt->close();

            // Send confirmation email via PHPMailer
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 0;                                     // Enable verbose debug output
                // $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'mail.intelipadel.com';                     // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username = 'no-reply@intelipadel.com';             // SMTP username
                $mail->Password = 'Mailer123';                            // SMTP password
                $mail->SMTPSecure = 'ssl';                                // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 469;                                        // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
                $mail->CharSet = 'UTF-8';

                //Recipients
                $mail->setFrom('no-reply@intelipadel.com', 'PadelRoom');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de Ayuda';
                $mail->Body = "Hola $name,<br><br>Hemos recibido tu mensaje:<br><br>$message<br><br>Nos pondremos en contacto contigo lo antes posible.<br><br>Saludos,<br>Equipo de PadelRoom";

                $mail->send();
                echo json_encode(true);
            } catch (Exception $e) {
                echo json_encode(false);
            }
        } else {
            echo json_encode(false);
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>