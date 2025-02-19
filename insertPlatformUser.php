<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

use Stripe\StripeClient;

$method = $_SERVER['REQUEST_METHOD'];

function convertDateOfBirth($date)
{
    $months = [
        'enero' => '01',
        'febrero' => '02',
        'marzo' => '03',
        'abril' => '04',
        'mayo' => '05',
        'junio' => '06',
        'julio' => '07',
        'agosto' => '08',
        'septiembre' => '09',
        'octubre' => '10',
        'noviembre' => '11',
        'diciembre' => '12'
    ];

    $dateParts = explode(' de ', $date);
    $day = $dateParts[0];
    $month = $months[$dateParts[1]];
    $year = $dateParts[2];

    return "$year-$month-$day";
}

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['full_name']) && isset($params['age']) && isset($params['date_of_birth']) && isset($params['email']) && isset($params['phone_number_code']) && isset($params['phone_number']) && isset($params['type']) && isset($params['id_platforms'])) {
        $full_name = $params['full_name'];
        $age = $params['age'];
        $date_of_birth = convertDateOfBirth($params['date_of_birth']);
        $phone_number = $params['phone_number'];
        $phone_number_code = $params['phone_number_code'];
        $email = $params['email'];
        $type = $params['type'];
        $id_platforms = $params['id_platforms'];
        $date_created = date('Y-m-d H:i:s');

        $phone_number_w_code = $phone_number_code . $phone_number;

        // Query to get the secret API key from the database
        $sql = "SELECT a.key_string 
                FROM platforms_keys as a
                INNER JOIN platforms_environments as b on b.type = a.title AND b.test = a.test
                WHERE a.type = 'secret'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $secretKey = $row['key_string'];

            // Create Stripe customer
            $stripe = new StripeClient($secretKey);
            $customer = $stripe->customers->create([
                'name' => $full_name,
                'phone' => $phone_number_w_code,
                'email' => $email
            ]);
            $stripe_id = $customer->id;

            // Insert data into platforms_users
            $sql = "INSERT INTO `platforms_users`(`full_name`, `age`, `date_of_birth`, `phone_number_code`, `phone_number`, `email`, `stripe_id`, `type`, `date_created`, `id_platforms`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssssssi", $full_name, $age, $date_of_birth, $phone_number_code, $phone_number, $email, $stripe_id, $type, $date_created, $id_platforms);

            if ($stmt->execute()) {
                $id_platforms_user = $stmt->insert_id;
                $stmt->close();

                // Fetch and return the user data
                $sql = "SELECT id_platforms_user, full_name, age, date_of_birth, email, phone_number, phone_number_code, stripe_id, type, date_created, id_platforms, active 
                        FROM platforms_users 
                        WHERE id_platforms_user = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_platforms_user);
                $stmt->execute();
                $userData = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                echo json_encode($userData);
            } else {
                echo json_encode(false);
            }
        } else {
            echo json_encode(['error' => 'API key not found']);
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>