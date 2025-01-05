<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");

require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_fields_events_users'])) {
        $id_platforms_fields_events_users = $params['id_platforms_fields_events_users'];

        // Query to delete the record from the platforms_fields_events_users table
        $sql = "DELETE FROM platforms_fields_events_users WHERE id_platforms_fields_events_users = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_fields_events_users);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["message" => "Record deleted successfully"]);
            } else {
                echo json_encode(["message" => "No record found with the given ID"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["message" => "Failed to delete record"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>