<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Query to fetch all active platforms
    $sql = "SELECT a.id_platform, a.title, a.active 
            FROM platforms as a 
            WHERE a.active = 1";
    $result = $conn->query($sql);

    $platforms = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $platforms[] = $row;
        }
    }

    echo json_encode($platforms);
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>