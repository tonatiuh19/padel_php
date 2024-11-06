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

    if (isset($params['id_platform']) && isset($params['imageDirectory'])) {
        $id_platform = $params['id_platform'];
        $imageDirectory = $params['imageDirectory'];

        // Fetch platform details
        $sql = "SELECT id_platform, title, start_time, end_time FROM platforms WHERE id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platform);
        $stmt->execute();
        $platformResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Fetch platform fields
        $sql = "SELECT id_platforms_field, id_platform, title, active FROM platforms_fields WHERE id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platform);
        $stmt->execute();
        $fieldsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Fetch platform date time slots
        $slotsResult = [];
        foreach ($fieldsResult as $field) {
            $id_platforms_field = $field['id_platforms_field'];
            $sql = "SELECT id_platforms_date_time_slot, id_platforms_field, platforms_date_time_start, platforms_date_time_end, active 
                    FROM platforms_date_time_slots WHERE id_platforms_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_field);
            $stmt->execute();
            $fieldSlotsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $slotsResult[$id_platforms_field] = $fieldSlotsResult;
            $stmt->close();
        }

        // Fetch images from directory
        function getMediaFiles($directory)
        {
            $mediaFiles = [];
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'mp4', 'avi', 'mov', 'wmv'];
            if (is_dir($directory)) {
                $files = array_diff(scandir($directory), array('.', '..'));
                foreach ($files as $file) {
                    $file_path = realpath($directory . '/' . $file);
                    $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                    if (in_array($file_type, $allowed_types)) {
                        // Generate URL path
                        $url_path = str_replace($_SERVER['DOCUMENT_ROOT'], 'https://garbrix.com', $file_path);
                        $mediaFiles[] = [
                            'name' => $file,
                            'path' => $url_path
                        ];
                    }
                }
            }
            return $mediaFiles;
        }

        // Generate dates array
        function generateDatesArray($currentDateNow, $slots)
        {
            $datesArray = [];
            $startDate = new DateTime($currentDateNow);
            $endDate = clone $startDate;
            $endDate->modify('+8 days');

            $currentDate = clone $startDate;

            while ($currentDate <= $endDate) {
                $datesArray[] = $currentDate->format('Y-m-d');
                $currentDate->modify('+1 day');
            }

            return generateHourlyTimeslots($datesArray, $slots);
        }

        // Generate hourly timeslots
        function generateHourlyTimeslots($items, $slots)
        {
            $newItems = [];

            foreach ($items as $date) {
                $newItems[$date] = [
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T08:00:00 - {$date}T09:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T09:00:00 - {$date}T10:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T10:00:00 - {$date}T11:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T11:00:00 - {$date}T12:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T12:00:00 - {$date}T13:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T13:00:00 - {$date}T14:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T14:00:00 - {$date}T15:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T15:00:00 - {$date}T16:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T16:00:00 - {$date}T17:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T17:00:00 - {$date}T18:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T18:00:00 - {$date}T19:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T19:00:00 - {$date}T20:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T20:00:00 - {$date}T21:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T21:00:00 - {$date}T22:00:00"],
                    ['active' => 0, 'height' => 50, 'name' => "{$date}T22:00:00 - {$date}T23:00:00"],
                ];
            }

            return removeTimeSlotsWith17OrLessInName($slots, $newItems, $items[0]);
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

                $result[$startDate][] = [
                    'active' => $item['active'],
                    'height' => 50,
                    'name' => "{$startDate}T{$startTime} - {$startDate}T{$endTime}",
                ];
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

        // Organize slots into active and idle
        $fieldsWithSlots = [];
        foreach ($fieldsResult as $field) {
            $id_platforms_field = $field['id_platforms_field'];
            $activeSlots = [];
            $idleSlots = [];
            if (isset($slotsResult[$id_platforms_field])) {
                foreach ($slotsResult[$id_platforms_field] as $slot) {
                    if ($slot['active'] == 1) {
                        $activeSlots[] = $slot;
                    } elseif ($slot['active'] == 2) {
                        $idleSlots[] = $slot;
                    }
                }
            }

            // Fetch images for each platform field
            $fieldImageDirectory = $imageDirectory . '/' . $id_platforms_field;
            $carrouselImages = getMediaFiles($fieldImageDirectory);

            // Generate slots
            $slots = generateDatesArray($now, array_merge($activeSlots, $idleSlots));

            $fieldsWithSlots[] = [
                'id_platforms_field' => $id_platforms_field,
                'title' => $field['title'],
                'carrouselImages' => $carrouselImages,
                'active_slots' => $activeSlots,
                'idle_slots' => $idleSlots,
                'slots' => $slots
            ];
        }

        // Structure the response
        $response = [
            'title' => $platformResult['title'],
            'start_time' => $platformResult['start_time'],
            'end_time' => $platformResult['end_time'],
            'platforms_fields' => $fieldsWithSlots
        ];

        $res = json_encode($response, JSON_NUMERIC_CHECK);
        header('Content-type: application/json; charset=utf-8');
        echo $res;
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>