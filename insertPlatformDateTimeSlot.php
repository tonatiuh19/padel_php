<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_field']) && isset($params['platforms_date_time_start']) && isset($params['active']) && isset($params['id_platforms_user'])) {
        $id_platforms_field = $params['id_platforms_field'];
        $platforms_date_time_start = $params['platforms_date_time_start'];
        $active = $params['active'];
        $id_platforms_user = $params['id_platforms_user'];

        // Calculate platforms_date_time_end as an hour and a half after platforms_date_time_start
        $startDateTime = new DateTime($platforms_date_time_start);
        $endDateTime = clone $startDateTime;
        $endDateTime->modify('+1 hour 30 minutes');
        $platforms_date_time_end = $endDateTime->format('Y-m-d H:i:s');

        $platforms_date_time_inserted = (new DateTime())->format('Y-m-d H:i:s');

        // Insert data into the database
        $sql = "INSERT INTO `platforms_date_time_slots`(`id_platforms_field`, `platforms_date_time_start`, `platforms_date_time_end`, `active`, `id_platforms_user`, `platforms_date_time_inserted`) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $id_platforms_field, $platforms_date_time_start, $platforms_date_time_end, $active, $id_platforms_user, $platforms_date_time_inserted);

        if ($stmt->execute()) {
            // Fetch the inserted data
            $insertedId = $stmt->insert_id;
            $stmt->close();

            $sql = "SELECT id_platforms_date_time_slot, id_platforms_field, platforms_date_time_start, platforms_date_time_end, active, id_platforms_user 
                    FROM platforms_date_time_slots 
                    WHERE id_platforms_date_time_slot = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $insertedId);
            $stmt->execute();
            $insertedData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            echo json_encode($insertedData);
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