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

    if (isset($params['id_platforms']) && isset($params['id_platforms_field']) && isset($params['price']) && isset($params['platforms_fields_price_start_time']) && isset($params['platforms_fields_price_end_time']) && isset($params['active'])) {
        $id_platforms = $params['id_platforms'];
        $id_platforms_field = $params['id_platforms_field'];
        $price = $params['price'];
        $platforms_fields_price_start_time = $params['platforms_fields_price_start_time'];
        $platforms_fields_price_end_time = $params['platforms_fields_price_end_time'];
        $active = $params['active'];

        // Insert the new price entry
        $sql = "INSERT INTO platforms_fields_prices (id_platforms, id_platforms_field, price, platforms_fields_price_start_time, platforms_fields_price_end_time, active) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $id_platforms, $id_platforms_field, $price, $platforms_fields_price_start_time, $platforms_fields_price_end_time, $active);

        if ($stmt->execute()) {
            $stmt->close();

            // Query to fetch fixed prices (active = 1)
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.id_platforms_field, a.price, a.platforms_fields_price_start_time, a.platforms_fields_price_end_time, a.active 
                    FROM platforms_fields_prices as a
                    WHERE a.active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $fixedPricesResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Query to fetch special prices (active = 2) but not select the prices before today
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.platforms_fields_price_start_time, a.platforms_fields_price_end_time, a.active, 
                       b.id_platforms_field, b.id_platform, b.title, b.active as field_active
                FROM platforms_fields_prices as a
                JOIN platforms_fields as b ON a.id_platforms_field = b.id_platforms_field
                WHERE a.id_platforms = ? AND a.active = 2 AND a.platforms_fields_price_start_time >= CURDATE()";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $specialPricesResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Structure the response
            $response = [
                'fixedPrices' => $fixedPricesResult,
                'specialPrices' => $specialPricesResult
            ];

            echo json_encode($response);
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