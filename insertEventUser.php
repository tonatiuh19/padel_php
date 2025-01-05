<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");

require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user']) && isset($params['id_platforms_disabled_date']) && isset($params['active'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $id_platforms_disabled_date = $params['id_platforms_disabled_date'];
        $active = $params['active'];
        $platforms_fields_events_users_inserted = date('Y-m-d H:i:s'); // Set to current timestamp

        // Query to insert data into the platforms_fields_events_users table
        $sql = "INSERT INTO platforms_fields_events_users (id_platforms_user, id_platforms_disabled_date, active, platforms_fields_events_users_inserted) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $id_platforms_user, $id_platforms_disabled_date, $active, $platforms_fields_events_users_inserted);

        if ($stmt->execute()) {
            $insertedId = $stmt->insert_id;
            $stmt->close();

            // Query to fetch the inserted data
            $sql = "SELECT a.id_platforms_fields_events_users, a.id_platforms_user, a.id_platforms_disabled_date, a.active, a.platforms_fields_events_users_inserted 
                    FROM platforms_fields_events_users as a 
                    WHERE a.id_platforms_fields_events_users = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $insertedId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc(); // Fetch the inserted row
            $stmt->close();

            if ($data) {
                echo json_encode($data);
            } else {
                echo json_encode(["message" => "Failed to fetch inserted data"]);
            }
        } else {
            echo json_encode(["message" => "Failed to insert data"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>