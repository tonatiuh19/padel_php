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
        $sqlUser = "SELECT id_platforms_user, full_name, age, date_of_birth, email, phone_number, phone_number_code, stripe_id, type, date_created, id_platforms 
                    FROM platforms_users 
                    WHERE id_platforms_user = ?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("i", $id_platforms_user);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {
            // User exists, fetch user data
            $userData = $resultUser->fetch_assoc();

            // Query to fetch the publishable Stripe key
            $sqlKey = "SELECT a.key_string 
                       FROM platforms_keys as a
                       INNER JOIN platforms_environments as b on b.type = a.title AND b.test = a.test
                       WHERE a.type = 'publishable'";
            $resultKey = $conn->query($sqlKey);

            if ($resultKey->num_rows > 0) {
                $keyData = $resultKey->fetch_assoc();
                $userData['publishable_key'] = $keyData['key_string'];
            } else {
                $userData['publishable_key'] = null;
            }

            echo json_encode($userData);
        } else {
            // User does not exist
            echo json_encode(false);
        }

        $stmtUser->close();
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>