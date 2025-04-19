<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['date']) && isset($params['id_platforms_field'])) {
        $date = $params['date'];
        $id_platforms_field = $params['id_platforms_field'];

        $disabledSlots = [];

        // Query to get reserved slots from platforms_date_time_slots
        $sql = "SELECT platforms_date_time_start 
                FROM platforms_date_time_slots 
                WHERE DATE(platforms_date_time_start) = ? AND id_platforms_field = ? AND active IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $date, $id_platforms_field);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $time = explode(' ', $row['platforms_date_time_start'])[1];
                $disabledSlots[] = $time;
            }
        }
        $stmt->close();

        // Query to get reserved slots from platforms_fields_classes_users
        $sql = "SELECT a.platforms_date_time_start 
                FROM platforms_fields_classes_users as a
                INNER JOIN platforms_disabled_dates as b on b.id_platforms_disabled_date=a.id_platforms_disabled_date
                WHERE DATE(a.platforms_date_time_start) = ? AND b.id_platforms_field = ? AND a.active IN (1, 2)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $date, $id_platforms_field);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $time = explode(' ', $row['platforms_date_time_start'])[1];
                $disabledSlots[] = $time;
            }
        }
        $stmt->close();

        // Query to get data from platforms_disabled_dates
        $sql = "SELECT start_date_time, end_date_time, active 
                FROM platforms_disabled_dates 
                WHERE id_platforms_field = ? AND DATE(start_date_time) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_platforms_field, $date);
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