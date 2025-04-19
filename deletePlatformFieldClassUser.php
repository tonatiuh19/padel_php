<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_fields_classes_users'])) {
        $id_platforms_fields_classes_users = $params['id_platforms_fields_classes_users'];

        // Delete the record from the database
        $sql = "DELETE FROM `platforms_fields_classes_users` WHERE `id_platforms_fields_classes_users` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_fields_classes_users);

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