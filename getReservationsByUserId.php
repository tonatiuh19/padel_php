<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user'])) {
        $id_platforms_user = $params['id_platforms_user'];

        // Query to get active slots for the specified user, ordered by the most recent
        $sql = "SELECT a.id_platforms_date_time_slot, a.id_platforms_field, a.id_platforms_user, a.platforms_date_time_start, a.platforms_date_time_end, a.active, a.stripe_id, a.validated 
                FROM platforms_date_time_slots as a
                WHERE a.active = 1 AND a.id_platforms_user = ?
                ORDER BY a.id_platforms_date_time_slot DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();

        $activeSlots = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $activeSlots[] = $row;
            }
        }
        $stmt->close();

        echo json_encode($activeSlots);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>