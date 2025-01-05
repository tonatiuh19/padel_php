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

    if (isset($params['id_platforms_field']) && isset($params['platforms_fields_price_start_time'])) {
        $id_platforms_field = $params['id_platforms_field'];
        $platforms_fields_price_start_time = $params['platforms_fields_price_start_time'];

        // Query to fetch data based on the provided parameters
        $sql = "SELECT a.id_platforms_fields_price, a.id_platforms, a.id_platforms_field, a.id_platforms_disabled_date, a.price, a.platforms_fields_price_start_time, a.platforms_fields_price_end_time, a.slots 
                FROM platforms_fields_prices as a 
                WHERE a.id_platforms_field = ? AND a.active = 3 AND DATE(a.platforms_fields_price_start_time) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_platforms_field, $platforms_fields_price_start_time);

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // Fetch a single row
        $stmt->close();

        if ($data) {
            // Fetch the number of used slots from the platforms_fields_events_users table
            $sql = "SELECT COUNT(*) as used_slots 
                    FROM platforms_fields_events_users as a 
                    WHERE a.active = 1 AND a.id_platforms_disabled_date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $data['id_platforms_disabled_date']);
            $stmt->execute();
            $result = $stmt->get_result();
            $usedSlotsData = $result->fetch_assoc();
            $stmt->close();

            $usedSlots = $usedSlotsData['used_slots'];
            $availableSlots = $data['slots'] - $usedSlots;

            // Add the available slots to the response data
            $data['available_slots'] = $availableSlots;

            echo json_encode($data);
        } else {
            echo json_encode(["message" => "No data found for the given parameters"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>