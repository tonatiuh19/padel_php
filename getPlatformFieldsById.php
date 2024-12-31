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

    if (isset($params['id_platform']) && isset($params['imageDirectory']) && isset($params['id_platforms_user'])) {
        $id_platform = $params['id_platform'];
        $imageDirectory = $params['imageDirectory'];
        $id_platforms_user = $params['id_platforms_user'];

        $cleanupSql = "DELETE FROM platforms_date_time_slots 
        WHERE active = 2 AND platforms_date_time_inserted < NOW() - INTERVAL 3 MINUTE";
        $conn->query($cleanupSql);

        // Fetch platform details
        $sql = "SELECT id_platform, title, start_time, end_time FROM platforms WHERE id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platform);
        $stmt->execute();
        $platformResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($platformResult) {
            // Fetch platform fields
            $sql = "SELECT id_platforms_field, id_platform, title, active FROM platforms_fields WHERE id_platform = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platform);
            $stmt->execute();
            $fieldsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

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

            // Organize fields with images
            $fieldsWithImages = [];
            foreach ($fieldsResult as $field) {
                $id_platforms_field = $field['id_platforms_field'];

                // Fetch images for each platform field
                $fieldImageDirectory = $imageDirectory . '/' . $id_platforms_field;
                $carrouselImages = getMediaFiles($fieldImageDirectory);

                $fieldsWithImages[] = [
                    'id_platforms_field' => $id_platforms_field,
                    'title' => $field['title'],
                    'today' => $now,
                    'carrouselImages' => $carrouselImages
                ];
            }

            // Fetch the last reservation for the specified user
            $sql = "SELECT a.id_platforms_date_time_slot, a.id_platforms_field, a.id_platforms_user, a.platforms_date_time_start, a.platforms_date_time_end, a.active, a.stripe_id, a.validated 
            FROM platforms_date_time_slots as a
            WHERE a.validated = 0 AND a.active = 1 AND a.id_platforms_user = ? AND a.platforms_date_time_start > NOW()
            ORDER BY a.platforms_date_time_start ASC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_user);
            $stmt->execute();
            $lastReservation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Structure the response
            $response = [
                'title' => $platformResult['title'],
                'today' => $now,
                'start_time' => $platformResult['start_time'],
                'end_time' => $platformResult['end_time'],
                'platforms_fields' => $fieldsWithImages,
                'last_reservation' => $lastReservation
            ];

            $res = json_encode($response, JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(["message" => "Platform not found"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>