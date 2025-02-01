<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

function formatDate($date) {
    $months = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];

    $dateTime = new DateTime($date);
    $day = $dateTime->format('d');
    $month = $months[$dateTime->format('m')];
    $year = $dateTime->format('Y');

    return "$day de $month del $year";
}

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms'])) {
        $id_platforms = $params['id_platforms'];

        // Query to fetch privacy terms, title, and privacy terms date for the specified platform
        $sql = "SELECT a.privacy_terms, a.title, a.privacy_terms_date 
                FROM platforms as a 
                WHERE a.id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = $result->fetch_assoc(); // Fetch a single row
        $stmt->close();

        if ($data) {
            // Format the privacy_terms_date
            $data['privacy_terms_date'] = formatDate($data['privacy_terms_date']);
            echo json_encode($data);
        } else {
            echo json_encode(["message" => "No data found for the given platform ID"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>