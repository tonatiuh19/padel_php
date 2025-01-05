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

        $reservations = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
        }
        $stmt->close();

        // Query to get active event reservations for the specified user
        $sql = "SELECT a.id_platforms_fields_events_users, a.id_platforms_user, a.id_platforms_disabled_date, a.stripe_id, a.active, a.validated, a.platforms_fields_events_users_inserted, b.start_date_time, b.end_date_time, b.id_platforms_field 
                FROM platforms_fields_events_users as a 
                INNER JOIN platforms_disabled_dates as b on b.id_platforms_disabled_date = a.id_platforms_disabled_date
                WHERE a.active = 1 AND a.id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();

        $eventReservations = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $eventReservations[] = $row;
            }
        }
        $stmt->close();

        echo json_encode([
            'reservations' => $reservations,
            'eventReservations' => $eventReservations
        ]);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>