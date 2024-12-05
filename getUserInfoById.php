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

        // Query to fetch user information by id_platforms_user
        $sql = "SELECT id_platforms_user, full_name, age, date_of_birth, email, phone_number, phone_number_code, stripe_id, type, date_created, id_platforms 
                FROM platforms_users 
                WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, fetch user data
            $userData = $result->fetch_assoc();
            echo json_encode($userData);
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