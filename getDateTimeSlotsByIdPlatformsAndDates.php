<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms']) && isset($params['start_date']) && isset($params['end_date'])) {
        $id_platforms = $params['id_platforms'];
        $start_date = $params['start_date'] . ' 00:00:00';
        $end_date = $params['end_date'] . ' 23:59:59';

        $cleanupSql = "DELETE FROM platforms_date_time_slots 
        WHERE active = 2 AND platforms_date_time_inserted < NOW() - INTERVAL 3 MINUTE";
        $conn->query($cleanupSql);

        // Query to fetch data with the specified filters
        $sql = "SELECT a.id_platforms_date_time_slot, a.id_platforms_field, a.id_platforms_user, a.platforms_date_time_start, a.platforms_date_time_end, a.active, a.validated, b.full_name, b.date_of_birth, b.email, c.title
                FROM platforms_date_time_slots as a
                INNER JOIN platforms_users as b on a.id_platforms_user = b.id_platforms_user
                INNER JOIN platforms_fields as c on a.id_platforms_field = c.id_platforms_field
                WHERE b.id_platforms = ? AND a.platforms_date_time_start BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_platforms, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();

        // Query to fetch marked dates with the specified filters
        $sql = "SELECT a.id_platforms_disabled_date, a.start_date_time, a.end_date_time, a.active, b.title, b.id_platforms_field, a.title as 'event_title', a.type
                FROM platforms_disabled_dates as a
                INNER JOIN platforms_fields as b on b.id_platforms_field = a.id_platforms_field
                WHERE b.id_platform = ? AND a.start_date_time BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_platforms, $start_date, $end_date);
        $stmt->execute();
        $markedDatesResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Process marked dates into an array
        $markedDates = [];
        foreach ($markedDatesResult as $date) {
            $dotColor = $date['active'] == 1 ? 'red' : 'blue';

            $markedDates[] = [
                'marked' => true,
                'dotColor' => $dotColor,
                'activeOpacity' => 0,
                'id_platforms_disabled_date' => $date['id_platforms_disabled_date'],
                'id_platforms_field' => $date['id_platforms_field'],
                'title' => $date['title'],
                'start_date_time' => $date['start_date_time'],
                'end_date_time' => $date['end_date_time'],
                'active' => $date['active'],
                'title' => $date['title'],
                'event_title' => $date['event_title'],
                'type' => $date['type'],
            ];
        }

        // Structure the response
        $response = [
            'data' => $data,
            'markedDates' => $markedDates
        ];

        echo json_encode($response);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
