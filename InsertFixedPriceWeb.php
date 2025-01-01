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

    if (isset($params['id_platforms']) && isset($params['timeRanges']) && isset($params['active'])) {
        $id_platforms = $params['id_platforms'];
        $timeRanges = $params['timeRanges'];
        $active = $params['active'];

        $conn->begin_transaction();

        try {
            if ($active == 1) {
                // Set active to 0 for current prices by id_platforms
                $updateSql = "UPDATE platforms_fields_prices SET active = 0 WHERE id_platforms = ? AND active = 1";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $id_platforms);
                $stmt->execute();
                $stmt->close();
            }

            $currentDate = (new DateTime())->format('Y-m-d');

            foreach ($timeRanges as $range) {
                if (isset($range['price']) && isset($range['start_time']) && isset($range['end_time'])) {
                    $price = $range['price'];
                    $start_time = $currentDate . ' ' . $range['start_time'] . ':00';
                    $end_time = $currentDate . ' ' . $range['end_time'] . ':00';

                    // Insert the new price entry
                    $sql = "INSERT INTO platforms_fields_prices (id_platforms, price, platforms_fields_price_start_time, platforms_fields_price_end_time, active) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("idssi", $id_platforms, $price, $start_time, $end_time, $active);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Invalid time range data");
                }
            }

            $conn->commit();

            // Query to fetch fixed prices (active = 1)
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.platforms_fields_price_start_time, a.platforms_fields_price_end_time, a.active 
                    FROM platforms_fields_prices as a
                    WHERE a.active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $fixedPricesResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Query to fetch special prices (active = 2) but not select the prices before today
            $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.price, a.platforms_fields_price_start_time, a.platforms_fields_price_end_time, a.active 
                    FROM platforms_fields_prices as a
                    WHERE a.active = 2 AND a.platforms_fields_price_start_time >= CURDATE()";
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
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["message" => "Failed to insert data: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>