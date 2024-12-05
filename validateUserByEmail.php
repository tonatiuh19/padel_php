<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['email'])) {
        $email = $params['email'];

        // Query to check if the user exists by email
        $sql = "SELECT id_platforms_user 
                FROM platforms_users 
                WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, fetch user data
            $user = $result->fetch_assoc();
            $id_platforms_user = $user['id_platforms_user'];
            $stmt->close();

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
            // User does not exist
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