<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
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

            $fieldsWithSlots[] = [
                'id_platforms_field' => $id_platforms_field,
                'title' => $field['title'],
                'carrouselImages' => $carrouselImages,
                'active_slots' => $activeSlots,
                'idle_slots' => $idleSlots
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