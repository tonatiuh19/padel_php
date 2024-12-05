<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

function generateTimeSlots($start, $end, $range, $disabledSlots, $selectedDate)
{
    $slots = [];
    $now = new DateTime($selectedDate);
    $currentDate = $now->format('Y-m-d');
    $currentHour = (int) $now->format('H');
    $currentMinutes = (int) $now->format('i');

    for ($hour = $start; $hour <= $end; $hour += $range) {
        $fullHour = floor($hour);
        $minutes = ($hour % 1) * 60;
        $formattedHour = $fullHour < 10 ? "0$fullHour" : $fullHour;
        $formattedMinutes = $minutes == 0 ? "00" : $minutes;
        $time = "$formattedHour:$formattedMinutes:00";
        if (!in_array($time, $disabledSlots)) {
            $slots[] = "$currentDate $time";
        }
    }
    return $slots;
}

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_field'])) {
        $id_platforms_field = $params['id_platforms_field'];
        $now = new DateTime();
        $tomorrow = (clone $now)->modify('+1 day');
        $dayAfterTomorrow = (clone $tomorrow)->modify('+1 day');

        // Query to get disabled slots for tomorrow
        $sql = "SELECT platforms_date_time_start 
                FROM platforms_date_time_slots 
                WHERE id_platforms_field = ? AND active IN (1, 2) AND platforms_date_time_start BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_platforms_field, $tomorrow->format('Y-m-d 00:00:00'), $dayAfterTomorrow->format('Y-m-d 00:00:00'));
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

        // Query to get data from platforms_disabled_dates for tomorrow
        $sql = "SELECT start_date_time, end_date_time, active 
                FROM platforms_disabled_dates 
                WHERE id_platforms_field = ? AND start_date_time BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_platforms_field, $tomorrow->format('Y-m-d 00:00:00'), $dayAfterTomorrow->format('Y-m-d 00:00:00'));
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

        // Generate all possible slots for tomorrow
        $availableSlots = generateTimeSlots(8, 23, 1.5, $disabledSlots, $tomorrow->format('Y-m-d 00:00:00'));

        // Select 4 random available slots
        if (count($availableSlots) > 4) {
            $randomKeys = array_rand($availableSlots, 4);
            $randomSlots = array_map(function ($key) use ($availableSlots) {
                return $availableSlots[$key];
            }, $randomKeys);
        } else {
            $randomSlots = $availableSlots;
        }

        echo json_encode($randomSlots);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>