<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_products']) && isset($params['name']) && isset($params['price']) && isset($params['description']) && isset($params['type']) && isset($params['id_platform'])) {
        $id_platforms_products = $params['id_platforms_products'];
        $name = $params['name'];
        $price = $params['price'];
        $description = $params['description'];
        $type = $params['type'];
        $id_platform = $params['id_platform'];
        $active = isset($params['active']) ? $params['active'] : 1;

        // Extract Stripe key from the database
        $sql = "SELECT a.key_string 
                FROM platforms_keys as a
                INNER JOIN platforms_environments as b on b.type = a.title AND b.test = a.test
                WHERE a.type = 'secret'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $apiKey = $row['key_string'];

            $stripe = new \Stripe\StripeClient($apiKey);

            // Update product in Stripe
            try {
                $product = $stripe->products->update(
                    $params['stripe_id'],
                    [
                        'name' => $name,
                        'description' => $description,
                        'active' => $active
                    ]
                );

                // Update product in the database
                $sql = "UPDATE platforms_products SET name = ?, price = ?, description = ?, active = ?, type = ?, id_platform = ? WHERE id_platforms_products = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dssissi", $name, $price, $description, $active, $type, $id_platform, $id_platforms_products);

                if ($stmt->execute()) {
                    // Fetch all active products filtered by type and id_platform
                    $sql = "SELECT name, price, description, stripe_id, active, type, created 
                            FROM platforms_products 
                            WHERE active = 1 AND type = ? AND id_platform = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $type, $id_platform);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $products = $result->fetch_all(MYSQLI_ASSOC);
                        echo json_encode(["message" => "Product updated successfully", "products" => $products]);
                    } else {
                        echo json_encode(["message" => "Product updated successfully, but no active products found"]);
                    }

                    $stmt->close();
                } else {
                    echo json_encode(["message" => "Failed to update product in database"]);
                }
            } catch (Exception $e) {
                echo json_encode(["message" => "Failed to update product in Stripe", "error" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["message" => "Failed to retrieve Stripe key"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>