<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_field']) && isset($params['active']) && isset($params['start_date']) && isset($params['end_date']) && isset($params['id_platforms']) && isset($params['price']) && isset($params['platforms_fields_price_start_time']) && isset($params['platforms_fields_price_end_time']) && isset($params['title']) && isset($params['type'])) {
        $id_platforms_field = $params['id_platforms_field'];
        $active = $params['active'];
        $start_date = $params['start_date'] . ' 00:00:00';
        $end_date = $params['end_date'] . ' 23:59:59';
        $id_platforms = $params['id_platforms'];
        $type = $params['type'];
        $title = $params['title'];
        $now = date("Y-m-d H:i:s");

        $start_date_time = $params['start_date_time'];
        $end_date_time = $params['end_date_time'];

        $price = $params['price'];
        $platforms_fields_price_start_time = $params['platforms_fields_price_start_time'];
        $platforms_fields_price_end_time = $params['platforms_fields_price_end_time'];

        $conn->begin_transaction();

        try {
            // Insert the disabled slot into the database
            $sql = "INSERT INTO platforms_disabled_dates (id_platforms_field, start_date_time, end_date_time, active, title, type) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ississ", $id_platforms_field, $start_date_time, $end_date_time, $active, $title, $type);
            $stmt->execute();
            $id_platforms_disabled_date = $stmt->insert_id;
            $stmt->close();

            // Insert the new price entry
            $sql = "INSERT INTO platforms_fields_prices (id_platforms, id_platforms_field, price, platforms_fields_price_start_time, platforms_fields_price_end_time, active, id_platforms_disabled_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssii", $id_platforms, $id_platforms_field, $price, $platforms_fields_price_start_time, $platforms_fields_price_end_time, $active, $id_platforms_disabled_date);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            // If active = 4, return the specified query result
            if ($active == 4) {
                $sql = "SELECT a.id_platforms_disabled_date, a.start_date_time, a.end_date_time, a.active, b.title, b.id_platforms_field, a.title as 'event_title', a.type, c.price
                        FROM platforms_disabled_dates as a
                        INNER JOIN platforms_fields as b on b.id_platforms_field = a.id_platforms_field
                        INNER JOIN platforms_fields_prices as c on c.id_platforms_disabled_date = a.id_platforms_disabled_date
                        WHERE b.id_platform = ? AND a.active = 4";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_platforms);
                $stmt->execute();
                $result = $stmt->get_result();

                $classes = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                // Return the array of classes directly
                echo json_encode($classes);
                return;
            }

            // Query to fetch data with the specified filters
            $sql = "SELECT a.id_platforms_date_time_slot, a.id_platforms_field, a.id_platforms_user, a.platforms_date_time_start, a.platforms_date_time_end, a.active, a.validated, b.full_name, b.date_of_birth, b.email, c.title
                    FROM platforms_date_time_slots as a
                    INNER JOIN platforms_users as b on a.id_platforms_user = b.id_platforms_user
                    INNER JOIN platforms_fields as c on a.id_platforms_field = c.id_platforms_field
                    INNER JOIN platforms as d on c.id_platform = d.id_platform
                    WHERE d.id_platform = ? AND a.platforms_date_time_start BETWEEN ? AND ?";
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
                    INNER JOIN platforms as c on b.id_platform = c.id_platform
                    WHERE c.id_platform = ? AND a.start_date_time BETWEEN ? AND ?";
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
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["message" => "Failed to insert data: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>