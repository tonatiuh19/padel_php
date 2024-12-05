<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platform'])) {
        $id_platform = $params['id_platform'];

        // Fetch active ads for the specified platform
        $sql = "SELECT a.id_platforms_ad, a.id_platform, a.platforms_ad_title, a.platforms_ad_image, a.active 
                FROM platforms_ads as a 
                WHERE a.active = 1 AND a.id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platform);
        $stmt->execute();
        $adsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Structure the response
        $response = $adsResult;

        $res = json_encode($response, JSON_NUMERIC_CHECK);
        header('Content-type: application/json; charset=utf-8');
        echo $res;
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>