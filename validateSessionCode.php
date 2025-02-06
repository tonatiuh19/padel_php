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

    if (isset($params['id_platforms_user']) && isset($params['id_platforms']) && isset($params['code'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $id_platforms = $params['id_platforms'];
        $code = $params['code'];

        // Fetch the user's type from platforms_users
        $sql = "SELECT type FROM platforms_users WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $user_type = $userData['type'];
        } else {
            echo json_encode(false);
            exit;
        }
        $stmt->close();

        // Bypass validation if user type is 3
        if ($user_type == 3) {
            echo json_encode(true);
            exit;
        }

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

            echo json_encode(true);
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