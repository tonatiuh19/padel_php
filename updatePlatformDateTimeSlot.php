<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_date_time_slot']) && isset($params['active'])) {
        $id_platforms_date_time_slot = $params['id_platforms_date_time_slot'];
        $active = $params['active'];
        $stripe_id = isset($params['stripe_id']) ? $params['stripe_id'] : null;

        // Update the active column and optionally the stripe_id in the database
        if ($stripe_id) {
            $sql = "UPDATE `platforms_date_time_slots` SET `active` = ?, `stripe_id` = ? WHERE `id_platforms_date_time_slot` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $active, $stripe_id, $id_platforms_date_time_slot);
        } else {
            $sql = "UPDATE `platforms_date_time_slots` SET `active` = ? WHERE `id_platforms_date_time_slot` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $active, $id_platforms_date_time_slot);
        }

        if ($stmt->execute()) {
            // Fetch all data from the table
            $stmt->close();
            $sql = "SELECT id_platforms_date_time_slot, id_platforms_field, platforms_date_time_start, platforms_date_time_end, active, stripe_id 
                    FROM platforms_date_time_slots";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $allData = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($allData);
            } else {
                echo json_encode(["message" => "No data found"]);
            }
        } else {
            echo json_encode(["message" => "Failed to update data"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>