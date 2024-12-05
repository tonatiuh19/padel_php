<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $session = true;

        // Query to validate the session
        $sql = "SELECT id_platforms_users_session, id_platforms_user, session 
                FROM platforms_users_sessions 
                WHERE id_platforms_user = ? AND session = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_platforms_user, $session);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Session is valid, fetch additional user data
            $stmt->close(); // Close the first statement before preparing a new one

            $sql = "SELECT a.id_platforms_user, a.full_name, a.age, a.date_of_birth, a.phone_number, a.stripe_id, a.type, a.date_created, 
                           b.id_platforms_users_session, b.code, b.session 
                    FROM platforms_users as a
                    INNER JOIN platforms_users_sessions as b ON b.id_platforms_user = a.id_platforms_user 
                    WHERE a.id_platforms_user = ? AND b.session = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_platforms_user, $session);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            echo json_encode($userData);
        } else {
            // Session is invalid
            $stmt->close(); // Close the statement here if the session is invalid
            echo json_encode(false);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>