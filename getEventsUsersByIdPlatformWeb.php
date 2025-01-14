<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platform']) && isset($params['id_platforms_disabled_date'])) {
        $id_platform = $params['id_platform'];
        $id_platforms_disabled_date = $params['id_platforms_disabled_date'];

        // Query to fetch the specified data with additional where clause
        $sql = "SELECT a.id_platforms_fields_events_users, a.id_platforms_user, a.id_platforms_disabled_date, a.stripe_id, a.active, a.validated, a.platforms_fields_events_users_inserted, 
                       b.full_name, b.email, c.id_platforms_field, c.start_date_time, c.end_date_time, d.id_platform, d.title
                FROM platforms_fields_events_users as a
                INNER JOIN platforms_users as b on b.id_platforms_user = a.id_platforms_user
                INNER JOIN platforms_disabled_dates as c on c.id_platforms_disabled_date = a.id_platforms_disabled_date
                INNER JOIN platforms_fields as d on d.id_platforms_field = c.id_platforms_field
                WHERE d.id_platform = ? AND a.id_platforms_disabled_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_platform, $id_platforms_disabled_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $eventUsers = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $eventUsers[] = $row;
            }
        }
        $stmt->close();

        echo json_encode($eventUsers);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>