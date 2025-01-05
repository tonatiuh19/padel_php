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

    if (isset($params['id_platforms_fields_events_users']) && isset($params['active']) && isset($params['stripe_id'])) {
        $id_platforms_fields_events_users = $params['id_platforms_fields_events_users'];
        $active = $params['active'];
        $stripe_id = $params['stripe_id'];

        // Query to update the active and stripe_id fields in the platforms_fields_events_users table
        $sql = "UPDATE platforms_fields_events_users 
                SET active = ?, stripe_id = ? 
                WHERE id_platforms_fields_events_users = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $active, $stripe_id, $id_platforms_fields_events_users);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Query to fetch the updated data
                $sql = "SELECT a.id_platforms_fields_events_users, a.id_platforms_user, a.id_platforms_disabled_date, a.active, a.stripe_id, a.platforms_fields_events_users_inserted 
                        FROM platforms_fields_events_users as a 
                        WHERE a.id_platforms_fields_events_users = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_platforms_fields_events_users);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc(); // Fetch the updated row
                $stmt->close();

                if ($data) {
                    echo json_encode($data);
                } else {
                    echo json_encode(["message" => "Failed to fetch updated data"]);
                }
            } else {
                echo json_encode(["message" => "No record found with the given ID"]);
            }
        } else {
            echo json_encode(["message" => "Failed to update data"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>