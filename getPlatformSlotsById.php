<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $now = date("Y-m-d"); // Define $now at the beginning of the POST block
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_field']) && isset($params['date'])) {
        $id_platforms_field = $params['id_platforms_field'];
        $date = $params['date'];

        $cleanupSql = "DELETE FROM platforms_date_time_slots 
        WHERE active = 2 AND platforms_date_time_inserted < NOW() - INTERVAL 3 MINUTE";
        $conn->query($cleanupSql);

        // Fetch platform field details
        $sql = "SELECT id_platforms_field, id_platform, title, active FROM platforms_fields WHERE id_platforms_field = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_field);
        $stmt->execute();
        $fieldResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($fieldResult) {
            // Fetch platform date time slots
            $sql = "SELECT id_platforms_date_time_slot, id_platforms_field, platforms_date_time_start, platforms_date_time_end, active 
                    FROM platforms_date_time_slots 
                    WHERE id_platforms_field = ? AND DATE(platforms_date_time_start) = ? 
                    ORDER BY platforms_date_time_start ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id_platforms_field, $date);
            $stmt->execute();
            $slotsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Organize slots into active and idle
            $activeSlots = [];
            $idleSlots = [];
            foreach ($slotsResult as $slot) {
                if ($slot['active'] == 1) {
                    $activeSlots[] = $slot;
                } elseif ($slot['active'] == 2) {
                    $idleSlots[] = $slot;
                }
            }

            $sql = "SELECT id_platforms_disabled_date, start_date_time, end_date_time, active 
                    FROM platforms_disabled_dates 
                    WHERE id_platforms_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_field);
            $stmt->execute();
            $markedDatesResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Generate slots
            $slots = generateDatesArray($now, array_merge($activeSlots, $idleSlots));
            $transformedMarkedDates = transformMarkedDates($markedDatesResult);

            // Structure the response
            $response = [
                'id_platforms_field' => $fieldResult['id_platforms_field'],
                'title' => $fieldResult['title'],
                'today' => $now,
                'fullToday' => date("Y-m-d H:i:s"),
                'markedDates' => $transformedMarkedDates,
                'slots' => $slots
            ];

            $res = json_encode($response, JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(["message" => "Invalid id_platforms_field"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();


function transformMarkedDates($markedDatesResult)
{
    $transformed = [];

    foreach ($markedDatesResult as $date) {
        $dateKey = explode(' ', $date['start_date_time'])[0];
        $dotColor = $date['active'] == 1 ? 'red' : 'blue';

        $transformed[$dateKey] = [
            'marked' => true,
            'dotColor' => $dotColor,
            'activeOpacity' => 0,
            'id_platforms_disabled_date' => $date['id_platforms_disabled_date'],
            'start_date_time' => $date['start_date_time'],
            'end_date_time' => $date['end_date_time'],
            'active' => $date['active']
        ];
    }

    return $transformed;
}

// Generate dates array
function generateDatesArray($currentDateNow, $slots)
{
    $datesArray = [];
    $startDate = new DateTime($currentDateNow);
    $endDate = clone $startDate;
    $endDate->modify('+0 days');

    $currentDate = clone $startDate;

    while ($currentDate <= $endDate) {
        $datesArray[] = $currentDate->format('Y-m-d');
        $currentDate->modify('+1 day');
    }

    return removeTimeSlotsWith17OrLessInName($slots, [], $datesArray[0]);
}

// Remove time slots with 17 or less in name
function removeTimeSlotsWith17OrLessInName($slots, $items, $dateNow, $actualHour = null)
{
    if ($actualHour === null) {
        $actualHour = (int) (new DateTime())->format('H');
    }

    if (isset($items[$dateNow])) {
        $items[$dateNow] = array_filter($items[$dateNow], function ($slot) use ($actualHour) {
            $timePart = explode("T", $slot['name'])[1];
            $hour = (int) explode(":", $timePart)[0];
            return $hour + 1 > $actualHour;
        });
    }

    return transformData($slots, $items);
}

// Transform data
function transformData($data, $items)
{
    $result = [];

    foreach ($data as $item) {
        $startDate = explode(" ", $item['platforms_date_time_start'])[0];
        $endDate = explode(" ", $item['platforms_date_time_end'])[0];
        $startTime = explode(" ", $item['platforms_date_time_start'])[1];
        $endTime = explode(" ", $item['platforms_date_time_end'])[1];

        if (!isset($result[$startDate])) {
            $result[$startDate] = [];
        }

        if ($item['active'] != 0) {
            $result[$startDate][] = [
                'active' => $item['active'],
                'height' => 50,
                'name' => "{$startDate}T{$startTime} - {$startDate}T{$endTime}",
            ];
        }
    }

    return mergeData($result, $items);
}

// Merge data
function mergeData($firstData, $secondData)
{
    $mergedData = $secondData;

    foreach ($firstData as $date => $entries) {
        if (!isset($mergedData[$date])) {
            $mergedData[$date] = [];
        }

        $timeslotMap = [];

        foreach ($mergedData[$date] as $entry) {
            $timeslotMap[$entry['name']] = $entry;
        }

        foreach ($entries as $entry) {
            $timeslotMap[$entry['name']] = $entry;
        }

        $mergedData[$date] = array_values($timeslotMap);
    }

    return $mergedData;
}
?>