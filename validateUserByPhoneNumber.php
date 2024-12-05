<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['phone_number'])) {
        $phone_number = $params['phone_number'];

        // Query to check if the user exists by phone_number
        $sql = "SELECT id_platforms_user 
                FROM platforms_users 
                WHERE phone_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists
            echo json_encode(true);
        } else {
            // User does not exist
            echo json_encode(false);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>