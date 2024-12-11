<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

use Stripe\StripeClient;

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms']) && isset($params['full_name']) && isset($params['age']) && isset($params['date_of_birth']) && isset($params['email']) && isset($params['phone_number_code']) && isset($params['phone_number']) && isset($params['type'])) {
        $id_platforms = $params['id_platforms'];
        $full_name = $params['full_name'];
        $age = $params['age'];
        $date_of_birth = $params['date_of_birth'];
        $email = $params['email'];
        $phone_number_code = $params['phone_number_code'];
        $phone_number = $params['phone_number'];
        $type = $params['type'];
        $date_created = date('Y-m-d H:i:s');

        $phone_number_w_code = $phone_number_code . $phone_number;

        // Check if user already exists
        $sql = "SELECT id_platforms_user, stripe_id FROM platforms_users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $existing_user_id = $userData['id_platforms_user'];
            $existing_stripe_id = $userData['stripe_id'];

            // Delete the existing user
            $sql = "DELETE FROM platforms_users WHERE id_platforms_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $existing_user_id);
            $stmt->execute();
            $stmt->close();

            // Delete the existing Stripe customer
            $stripe = new StripeClient('sk_test_51QIiddAC7jSBO0hE9ZMm7BdWcDmOPorNoXvxsDk6DPeIAgGiZSVbSWIsqQ7cP1ZBl5sNGYMWAoJ5ISHxoePy7RD70007cw7HC4');
            $stripe->customers->delete($existing_stripe_id);
        }

        // Insert data into platforms_users without Stripe ID
        $sql = "INSERT INTO platforms_users (id_platforms, full_name, age, date_of_birth, email, phone_number_code, phone_number, type, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isissssis", $id_platforms, $full_name, $age, $date_of_birth, $email, $phone_number_code, $phone_number, $type, $date_created);

        if ($stmt->execute()) {
            $id_platforms_user = $stmt->insert_id;
            $stmt->close();

            // Create Stripe customer
            $stripe = new StripeClient('sk_test_51QIiddAC7jSBO0hE9ZMm7BdWcDmOPorNoXvxsDk6DPeIAgGiZSVbSWIsqQ7cP1ZBl5sNGYMWAoJ5ISHxoePy7RD70007cw7HC4');
            try {
                $customer = $stripe->customers->create([
                    'name' => $full_name,
                    'phone' => $phone_number_w_code,
                    'email' => $email
                ]);
                $stripe_id = $customer->id;

                if (!empty($stripe_id) && $stripe_id != '0') {
                    // Update the stripe_id in the database
                    $sql = "UPDATE platforms_users SET stripe_id = ? WHERE id_platforms_user = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $stripe_id, $id_platforms_user);
                    $stmt->execute();
                    $stmt->close();

                    // Fetch and return all users
                    $sql = "SELECT a.id_platforms_user, a.id_platforms, a.full_name, a.age, a.date_of_birth, a.email, a.phone_number_code, a.phone_number, a.stripe_id, a.type 
                            FROM platforms_users as a
                            WHERE a.type <> 3 AND a.id_platforms = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_platforms);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $data = [];
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $data[] = $row;
                        }
                    }
                    $stmt->close();

                    echo json_encode($data);
                } else {
                    echo json_encode(["message" => "Failed to create Stripe customer: Invalid Stripe ID"]);
                }
            } catch (Exception $e) {
                echo json_encode(["message" => "Failed to create Stripe customer: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["message" => "Failed to create user"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>