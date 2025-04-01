<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['productType']) && isset($params['id_platform'])) {
        $type = $params['productType'];
        $id_platform = $params['id_platform'];

        // Select all active products from the database filtered by type and id_platform
        $sql = "SELECT a.name, a.price, a.description, a.stripe_id, a.active, a.type, a.created, b.id_platform, b.title 
                FROM platforms_products as a
                INNER JOIN platforms_fields as b on b.id_platforms_field=a.id_platforms_field
                WHERE a.active = 1 AND a.type = ? AND b.id_platform = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $type, $id_platform);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $products = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($products);
        } else {
            echo json_encode([]);
        }

        $stmt->close();
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}

$conn->close();
?>