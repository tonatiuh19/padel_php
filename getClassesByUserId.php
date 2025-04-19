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
        $today = date("Y-m-d");

        // Query to get active classes for the specified user, ordered by the most recent
        $sql = "SELECT a.id_platforms_fields_classes_users, a.id_platforms_user, a.id_platforms_disabled_date, a.platforms_date_time_start, a.platforms_date_time_end, a.price, a.stripe_id, a.validated, 
                       b.id_platforms_field, c.title as 'cancha', b.title as 'event_title'
                FROM platforms_fields_classes_users as a
                INNER JOIN platforms_disabled_dates as b on b.id_platforms_disabled_date = a.id_platforms_disabled_date
                INNER JOIN platforms_fields as c on c.id_platforms_field = b.id_platforms_field
                WHERE a.active = 1 AND a.id_platforms_user = ? AND DATE(a.platforms_date_time_start) >= ?
                ORDER BY a.id_platforms_fields_classes_users ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_platforms_user, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        $classes = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classes[] = $row;
            }
        }
        $stmt->close();

        // Return the fetched classes
        echo json_encode($classes);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>