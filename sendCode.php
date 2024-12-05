<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';
use Twilio\Rest\Client;

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user']) && isset($params['id_platforms']) && isset($params['method'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $id_platforms = $params['id_platforms'];
        $method = $params['method']; // 'sms' or 'whatsapp'

        // Fetch phone number and phone number code from platforms_users
        $sql = "SELECT phone_number, phone_number_code FROM platforms_users WHERE id_platforms_user = ? AND id_platforms = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_platforms_user, $id_platforms);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $phone_number = $userData['phone_number'];
            $phone_number_code = $userData['phone_number_code'];
        } else {
            echo json_encode(false);
            exit;
        }
        $stmt->close();

        $phone_number_w_code = $phone_number_code . $phone_number;

        // Delete the old session code
        $sql = "DELETE FROM platforms_users_sessions WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $stmt->close();

        // Generate a random six-digit session code and ensure it is unique
        do {
            $session_code = rand(100000, 999999);
            $sql = "SELECT COUNT(*) as count FROM platforms_users_sessions WHERE code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $session_code);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } while ($result['count'] > 0);

        // Insert the new session code into platforms_users_sessions
        $date_start = date('Y-m-d H:i:s');
        $sql = "INSERT INTO platforms_users_sessions (id_platforms_user, code, session, date_start) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $session_active = false;
        $stmt->bind_param("iiss", $id_platforms_user, $session_code, $session_active, $date_start);
        $stmt->execute();
        $stmt->close();

        // Send session code via Twilio
        $sid = "AC9d959815dd66e608ac22507e9972aba3";
        $token = "b464a06161c029fe1069cb3f162701a2";
        $twilio = new Client($sid, $token);

        if ($method === 'sms') {
            $message = $twilio->messages->create(
                $phone_number_w_code, // to
                [
                    "messagingServiceSid" => "MG2c32ae1ce7a5a3712aeea1a47bc5c874",
                    "body" => "Tu código para iniciar sesión en PadelRoom es: $session_code"
                ]
            );
        } elseif ($method === 'whatsapp') {
            $message = $twilio->messages->create(
                "whatsapp:$phone_number_w_code", // to
                [
                    "from" => "whatsapp:+14155238886",
                    "body" => "Tu código para iniciar sesión en PadelRoom es: $session_code"
                ]
            );
            /* $message = $twilio->messages
                 ->create(
                     "whatsapp:+5214741400363", // to
                     array(
                         "from" => "whatsapp:+14155238886",
                         "contentSid" => "HX229f5a04fd0510ce1b071852155d3e75",
                         "contentVariables" => "{\"1\":\"$session_code\"}",
                         "body" => "Your Message"
                     )
                 );*/
        } else {
            echo json_encode(["message" => "Invalid method"]);
            exit;
        }

        // Update twilio_sid in platforms_users_sessions
        $twilio_sid = $message->sid;
        $sql = "UPDATE platforms_users_sessions SET twilio_sid = ? WHERE id_platforms_user = ? AND code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $twilio_sid, $id_platforms_user, $session_code);
        $stmt->execute();
        $stmt->close();

        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>