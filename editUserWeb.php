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

    if (isset($params['id_platforms_user']) && isset($params['full_name']) && isset($params['age']) && isset($params['date_of_birth']) && isset($params['email']) && isset($params['phone_number_code']) && isset($params['phone_number']) && isset($params['type']) && isset($params['id_platforms'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $full_name = $params['full_name'];
        $age = $params['age'];
        $date_of_birth = $params['date_of_birth'];
        $email = $params['email'];
        $phone_number_code = $params['phone_number_code'];
        $phone_number = $params['phone_number'];
        $type = $params['type'];
        $id_platforms = $params['id_platforms'];

        // Fetch the existing Stripe customer ID
        $sql = "SELECT stripe_id FROM platforms_users WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_platforms_user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $stripe_id = $userData['stripe_id'];
        } else {
            echo json_encode(["message" => "User not found"]);
            $stmt->close();
            exit;
        }
        $stmt->close();

        $stripe = new StripeClient('sk_test_51QIiddAC7jSBO0hE9ZMm7BdWcDmOPorNoXvxsDk6DPeIAgGiZSVbSWIsqQ7cP1ZBl5sNGYMWAoJ5ISHxoePy7RD70007cw7HC4');
        $phone_number_w_code = $phone_number_code . $phone_number;

        // Check if stripe_id is null, 0, or empty and create a new Stripe customer if necessary
        if (empty($stripe_id) || $stripe_id == '0') {
            $customer = $stripe->customers->create([
                'name' => $full_name,
                'phone' => $phone_number_w_code,
                'email' => $email
            ]);
            $stripe_id = $customer->id;

            // Update the stripe_id in the database
            $sql = "UPDATE platforms_users SET stripe_id = ? WHERE id_platforms_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $stripe_id, $id_platforms_user);
            $stmt->execute();
            $stmt->close();
        } else {
            // Update existing Stripe customer
            $stripe->customers->update($stripe_id, [
                'name' => $full_name,
                'phone' => $phone_number_w_code,
                'email' => $email
            ]);
        }

        // Update data in platforms_users
        $sql = "UPDATE platforms_users 
                SET full_name = ?, age = ?, date_of_birth = ?, email = ?, phone_number_code = ?, phone_number = ?, type = ? 
                WHERE id_platforms_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssii", $full_name, $age, $date_of_birth, $email, $phone_number_code, $phone_number, $type, $id_platforms_user);

        if ($stmt->execute()) {
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
            echo json_encode(["message" => "Failed to update user"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>