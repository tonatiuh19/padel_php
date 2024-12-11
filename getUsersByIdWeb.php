<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms'])) {
        $id_platforms = $params['id_platforms'];

        // Query to fetch users with the specified filters
        $sql = "SELECT a.id_platforms_user, a.id_platforms, a.full_name, a.age, a.date_of_birth, a.email, a.phone_number_code, a.phone_number, a.stripe_id, a.type 
                FROM platforms_users as a
                WHERE a.type <> 3 AND a.id_platforms = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();

        echo json_encode($data);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>