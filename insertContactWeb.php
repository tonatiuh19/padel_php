<?php
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

    if (isset($params['id_platforms']) && isset($params['name']) && isset($params['email']) && isset($params['phone']) && isset($params['message'])) {
        $id_platforms = $params['id_platforms'];
        $name = $params['name'];
        $email = $params['email'];
        $phone = $params['phone'];
        $message = $params['message'];

        // Insert data into platforms_contacts
        $sql = "INSERT INTO platforms_contacts (id_platforms, name, email, phone, comment) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $id_platforms, $name, $email, $phone, $message);

        if ($stmt->execute()) {
            $stmt->close();

            // Send email notification to the contact and Felix
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 2;
                $mail->Host = 'mail.intelipadel.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'no-reply@intelipadel.com'; // SMTP username
                $mail->Password = 'Mailer123'; // SMTP password
                $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
                $mail->CharSet = 'UTF-8';

                // Recipients
                $mail->setFrom('no-reply@intelipadel.com', 'InteliPadel');
                $mail->addAddress($email); // Add a recipient

                // Content for the contact
                $mail->isHTML(true);
                $mail->Subject = 'Gracias por tu interés';
                $mail->Body = "Hola $name,<br><br>Hemos recibido tu mensaje. Nos pondremos en contacto contigo lo antes posible.<br><br>Saludos,<br>InteliPadel Team";

                $mail->send();

                // Content for Felix
                $mail->clearAddresses();
                $mail->addAddress('felix@intelipadel.com');
                $mail->Subject = 'Nuevo interés de contacto';
                $mail->Body = "Hola Felix,<br><br>Has recibido un nuevo contacto de interés.<br><br>Nombre: $name<br>Email: $email<br>Teléfono: $phone<br>Mensaje: $message<br><br>Por favor, responde lo antes posible.<br><br>Saludos,<br>InteliPadel Team";

                $mail->send();

                echo json_encode(true);
            } catch (Exception $e) {
                echo json_encode(false);
                exit;
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