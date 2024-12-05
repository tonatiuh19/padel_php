<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_date_time_slot'])) {
        $id_platforms_date_time_slot = $params['id_platforms_date_time_slot'];

        // Delete the record from the database
        $sql = "DELETE FROM `platforms_date_time_slots` WHERE `id_platforms_date_time_slot` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_date_time_slot);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Data deleted successfully"]);
        } else {
            echo json_encode(["message" => "Failed to delete data"]);
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