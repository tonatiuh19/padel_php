<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('vendor/autoload.php');

\Stripe\Stripe::setApiKey('sk_test_51QIiddAC7jSBO0hE9ZMm7BdWcDmOPorNoXvxsDk6DPeIAgGiZSVbSWIsqQ7cP1ZBl5sNGYMWAoJ5ISHxoePy7RD70007cw7HC4'); // Replace with your actual secret key

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$amount = $data['amount'];

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'mxn',
    ]);

    echo json_encode(['clientSecret' => $paymentIntent->client_secret]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>