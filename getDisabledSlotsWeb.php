<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['date']) && isset($params['id_platforms_field']) && isset($params['id_platform'])) {
        $date = $params['date'];
        $id_platforms_field = $params['id_platforms_field'];
        $id_platform = $params['id_platform'];

        // Query to get disabled slots for the specified date
        $sql = "SELECT platforms_date_time_start 
                FROM platforms_date_time_slots 
                WHERE DATE(platforms_date_time_start) = ? AND id_platforms_field = ? AND active IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $date, $id_platforms_field);
        $stmt->execute();
        $result = $stmt->get_result();

        $disabledSlots = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $time = explode(' ', $row['platforms_date_time_start'])[1];
                $disabledSlots[] = $time;
            }
        }
        $stmt->close();

        // Query to get data from platforms_disabled_dates with additional filter
        $sql = "SELECT a.start_date_time, a.end_date_time, a.active 
                FROM platforms_disabled_dates as a
                INNER JOIN platforms_fields as b on b.id_platforms_field = a.id_platforms_field
                WHERE a.id_platforms_field = ? AND DATE(a.start_date_time) = ? AND b.id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $id_platforms_field, $date, $id_platform);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['active'] == 2) {
                    $startDateTime = new DateTime($row['start_date_time']);
                    $endDateTime = new DateTime($row['end_date_time']);
                    $interval = new DateInterval('PT1H30M');
                    $period = new DatePeriod($startDateTime, $interval, $endDateTime);

                    foreach ($period as $dt) {
                        $disabledSlots[] = $dt->format('H:i:s');
                    }
                }
            }
        }

        $stmt->close();

        echo json_encode($disabledSlots);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>