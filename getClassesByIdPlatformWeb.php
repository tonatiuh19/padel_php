<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platform'])) {
        $id_platform = $params['id_platform'];

        // Query to fetch all available classes
        $sql = "SELECT a.id_platforms_disabled_date, a.start_date_time, a.end_date_time, a.active, b.title, b.id_platforms_field, a.title as 'event_title', a.type, c.price
                FROM platforms_disabled_dates as a
                INNER JOIN platforms_fields as b on b.id_platforms_field = a.id_platforms_field
                INNER JOIN platforms_fields_prices as c on c.id_platforms_disabled_date=a.id_platforms_disabled_date
                WHERE b.id_platform = ? AND a.active = 4";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platform);
        $stmt->execute();
        $result = $stmt->get_result();

        $classes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Return the array of classes directly
        echo json_encode($classes);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}

$conn->close();
?>