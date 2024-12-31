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
            $stmt->close(); // Close the statement after update

            // Fetch all ads for the specified platform with active = 1 or 2
            $sql = "SELECT a.id_platforms_ad, a.id_platform, a.platforms_ad_title, a.platforms_ad_image, a.active 
                    FROM platforms_ads as a 
                    WHERE a.id_platform = (SELECT id_platform FROM platforms_ads WHERE id_platforms_ad = ?) AND a.active IN (1, 2)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_ad);
            $stmt->execute();
            $adsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Structure the response
            $response = $adsResult;
            $res = json_encode($response, JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(["message" => "Failed to update ad"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>