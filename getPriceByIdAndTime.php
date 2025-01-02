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

    if ((isset($params['id_platforms_field']) || isset($params['id_platforms'])) && isset($params['time'])) {
        $id_platforms_field = isset($params['id_platforms_field']) ? $params['id_platforms_field'] : null;
        $id_platforms = isset($params['id_platforms']) ? $params['id_platforms'] : null;
        $datetime = $params['time'];
        $time = date('H:i:s', strtotime($datetime)); // Extract the time part from the datetime string

        if ($id_platforms_field) {
            // Query to fetch special price (active = 2) that fits the datetime range by id_platforms_field
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.active
                    FROM platforms_fields_prices as a
                    WHERE a.id_platforms_field = ? AND a.active = 2 AND a.platforms_fields_price_start_time <= ? AND a.platforms_fields_price_end_time >= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $id_platforms_field, $datetime, $datetime);
        } else {
            // Query to fetch special price (active = 2) that fits the datetime range by id_platforms
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.active
                    FROM platforms_fields_prices as a
                    WHERE a.id_platforms = ? AND a.active = 2 AND a.platforms_fields_price_start_time <= ? AND a.platforms_fields_price_end_time >= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $id_platforms, $datetime, $datetime);
        }

        $stmt->execute();
        $specialPriceResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($specialPriceResult) {
            // Return the special price if it exists
            echo json_encode($specialPriceResult);
        } else {
            if ($id_platforms_field) {
                // Query to fetch fixed price (active = 1) that fits the time range by id_platforms_field
                $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.active
                        FROM platforms_fields_prices as a
                        WHERE a.id_platforms_field = ? AND a.active = 1 AND TIME(a.platforms_fields_price_start_time) <= ? AND TIME(a.platforms_fields_price_end_time) >= ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $id_platforms_field, $time, $time);
            } else {
                // Query to fetch fixed price (active = 1) that fits the time range by id_platforms
                $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.active
                        FROM platforms_fields_prices as a
                        WHERE a.id_platforms = ? AND a.active = 1 AND TIME(a.platforms_fields_price_start_time) <= ? AND TIME(a.platforms_fields_price_end_time) >= ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $id_platforms, $time, $time);
            }

            $stmt->execute();
            $fixedPriceResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($fixedPriceResult) {
                // Return the fixed price if it exists
                echo json_encode($fixedPriceResult);
            } else {
                echo json_encode(["message" => "No price found for the given time"]);
            }
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>