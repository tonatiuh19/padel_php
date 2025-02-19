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

        // Query to fetch full user information by id_platforms_user
        $sql = "SELECT a.id_platforms_user, a.id_platforms, a.full_name, a.age, a.date_of_birth, a.email, a.stripe_id, a.type, a.date_created, b.title 
                FROM platforms_users as a
                INNER JOIN platforms as b on b.id_platform = a.id_platforms
                WHERE a.active = 0 AND a.id_platforms_user = ?";
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
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>