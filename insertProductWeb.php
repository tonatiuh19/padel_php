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

    if (isset($params['name']) && isset($params['price']) && isset($params['description']) && isset($params['type']) && isset($params['id_platform'])) {
        $name = $params['name'];
        $price = $params['price'];
        $description = $params['description'];
        $type = $params['type'];
        $id_platform = $params['id_platform'];
        $active = isset($params['active']) ? $params['active'] : 1;
        $date_created = date('Y-m-d H:i:s');

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

            // Create product in Stripe
            try {
                $product = $stripe->products->create([
                    'name' => $name,
                    'description' => $description,
                    'default_price' => $price
                ]);

                $stripe_id = $product->id;

                // Insert product into the database
                $sql = "INSERT INTO platforms_products (name, price, description, stripe_id, active, type, id_platform, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdssissi", $name, $price, $description, $stripe_id, $active, $type, $id_platform, $date_created);

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
                        echo json_encode(["message" => "Product created successfully", "products" => $products]);
                    } else {
                        echo json_encode(["message" => "Product created successfully, but no active products found"]);
                    }

                    $stmt->close();
                } else {
                    echo json_encode(["message" => "Failed to insert product into database"]);
                }
            } catch (Exception $e) {
                echo json_encode(["message" => "Failed to create product in Stripe", "error" => $e->getMessage()]);
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