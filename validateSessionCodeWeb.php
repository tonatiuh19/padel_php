<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['email']) && isset($params['code'])) {
        $email = $params['email'];
        $code = $params['code'];

        // Fetch the id_platforms_user from platforms_users using email
        $sql = "SELECT id_platforms_user FROM platforms_users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $id_platforms_user = $userData['id_platforms_user'];
        } else {
            echo json_encode(false);
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Fetch the session code from platforms_users_sessions
        $sql = "SELECT code FROM platforms_users_sessions WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $sessionData = $result->fetch_assoc();
            $session_code = $sessionData['code'];
        } else {
            echo json_encode(false);
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Validate the session code
        if ($code == $session_code) {
            // Update the session to true
            $sql = "UPDATE platforms_users_sessions SET session = 1 WHERE id_platforms_user = ? AND code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_platforms_user, $code);
            $stmt->execute();
            $stmt->close();

            // Fetch and return the user data
            $sql = "SELECT id_platforms_user, full_name, age, date_of_birth, email, phone_number, phone_number_code, stripe_id, type, date_created, id_platforms 
                    FROM platforms_users 
                    WHERE id_platforms_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_user);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            echo json_encode($userData);
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