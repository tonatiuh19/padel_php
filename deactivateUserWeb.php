<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_user']) && isset($params['motivation'])) {
        $id_platforms_user = $params['id_platforms_user'];
        $motivation = $params['motivation'];
        $date_deactivation = date('Y-m-d H:i:s'); // Set to current timestamp

        // Start transaction
        $conn->begin_transaction();

        // Update the user to deactivated in platforms_users
        $sqlUpdateUser = "UPDATE platforms_users SET active = 1 WHERE id_platforms_user = ?";
        $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
        $stmtUpdateUser->bind_param("i", $id_platforms_user);
        $updateUserSuccess = $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        if ($updateUserSuccess) {
            // Insert into platforms_users_deactivated
            $sqlInsertDeactivated = "INSERT INTO platforms_users_deactivated (id_platforms_user, date_deactivation, motivation) 
                                     VALUES (?, ?, ?)";
            $stmtInsertDeactivated = $conn->prepare($sqlInsertDeactivated);
            $stmtInsertDeactivated->bind_param("iss", $id_platforms_user, $date_deactivation, $motivation);
            $insertDeactivatedSuccess = $stmtInsertDeactivated->execute();
            $stmtInsertDeactivated->close();

            if ($insertDeactivatedSuccess) {
                // Commit transaction
                $conn->commit();
                echo json_encode(true);
            } else {
                // Rollback transaction in case of error
                $conn->rollback();
                echo json_encode(false);
            }
        } else {
            // Rollback transaction in case of error
            $conn->rollback();
            echo json_encode(false);
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>