<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_ad']) && isset($params['active'])) {
        $id_platforms_ad = $params['id_platforms_ad'];
        $active = $params['active'];

        // Update the active field for the specified ad
        $sql = "UPDATE platforms_ads SET active = ? WHERE id_platforms_ad = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $active, $id_platforms_ad);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Ad updated successfully"]);
        } else {
            echo json_encode(["message" => "Failed to update ad"]);
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