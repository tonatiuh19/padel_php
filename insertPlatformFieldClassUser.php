<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user']) && isset($params['id_platforms_disabled_date']) && isset($params['platforms_date_time_start']) && isset($params['price']) && isset($params['active']) && isset($params['validated'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $id_platforms_disabled_date = $params['id_platforms_disabled_date'];
        $platforms_date_time_start = $params['platforms_date_time_start'];
        $price = $params['price'];
        $active = $params['active'];
        $validated = $params['validated'];
        $platforms_fields_classes_users_inserted = date('Y-m-d H:i:s');

        // Extract type from platforms_disabled_dates
        $sql = "SELECT a.type FROM platforms_disabled_dates as a WHERE a.id_platforms_disabled_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_disabled_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $type = $result->fetch_assoc()['type'];
        $stmt->close();

        // Calculate platforms_date_time_end based on type
        $startDateTime = new DateTime($platforms_date_time_start);
        $endDateTime = clone $startDateTime;

        if ($type == 1 ) {
            $endDateTime->modify('+1 hour');
        } elseif ($type == 2) {
            $endDateTime->modify('+1 hour 30 minutes');
        } else {
            $endDateTime->modify('+30 minutes');
        }

        $platforms_date_time_end = $endDateTime->format('Y-m-d H:i:s');
        

        // Insert data into platforms_fields_classes_users
        $sql = "INSERT INTO `platforms_fields_classes_users` (`id_platforms_user`, `id_platforms_disabled_date`, `platforms_date_time_start`, `platforms_date_time_end`, `price`, `active`, `validated`, `platforms_fields_classes_users_inserted`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissdiii", $id_platforms_user, $id_platforms_disabled_date, $platforms_date_time_start, $platforms_date_time_end, $price, $active, $validated, $platforms_fields_classes_users_inserted);

        if ($stmt->execute()) {
            // Fetch the inserted data
            $insertedId = $stmt->insert_id;
            $stmt->close();

            $sql = "SELECT id_platforms_fields_classes_users, id_platforms_user, id_platforms_disabled_date, platforms_date_time_start, platforms_date_time_end, price, active, validated, platforms_fields_classes_users_inserted 
                    FROM platforms_fields_classes_users 
                    WHERE id_platforms_fields_classes_users = ?";
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