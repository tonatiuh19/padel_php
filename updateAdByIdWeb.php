<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");

require_once('db_cnn/cnn.php');
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_platforms_ad']) && isset($_POST['platforms_ad_title']) && isset($_POST['active'])) {
        $id_platforms_ad = $_POST['id_platforms_ad'];
        $platforms_ad_title = $_POST['platforms_ad_title'];
        $active = $_POST['active'];
        $platforms_ad_image = '';

        if (isset($_POST['platforms_ad_image']) && !empty($_POST['platforms_ad_image'])) {
            // Use the provided image string
            $platforms_ad_image = $_POST['platforms_ad_image'];
        } elseif (isset($_FILES['platforms_ad_image'])) {
            $folder_path = "../assets/ads/";
            if (!file_exists($folder_path)) {
                mkdir($folder_path, 0777, true);
            }

            $filename = uniqid() . '_' . basename($_FILES['platforms_ad_image']['name']);
            $newname = $folder_path . $filename;
            $fileOk = 1;
            $types = array('image/jpeg', 'image/jpg', 'image/png');

            if (in_array($_FILES["platforms_ad_image"]["type"], $types)) {
                if (move_uploaded_file($_FILES['platforms_ad_image']['tmp_name'], $newname)) {
                    $platforms_ad_image = str_replace('../', 'https://garbrix.com/padel/', $newname);
                } else {
                    $response = array(
                        "status" => "2",
                        "message" => "Something happened while uploading the image!"
                    );
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = array(
                    "status" => "3",
                    "message" => "Invalid file type!"
                );
                echo json_encode($response);
                exit;
            }
        }

        // Update the ad
        $sql = "UPDATE platforms_ads SET platforms_ad_title = ?, active = ?, platforms_ad_image = ? WHERE id_platforms_ad = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $platforms_ad_title, $active, $platforms_ad_image, $id_platforms_ad);
        if ($stmt->execute()) {
            $stmt->close(); // Close the statement after update

            // Fetch all ads for the specified platform with active = 1 or 2
            $sql = "SELECT a.id_platforms_ad, a.id_platform, a.platforms_ad_title, a.platforms_ad_image, a.active 
                    FROM platforms_ads as a 
                    WHERE a.id_platform = (SELECT id_platform FROM platforms_ads WHERE id_platforms_ad = ?) AND a.active IN (1, 2)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_platforms_ad);
            $stmt->execute();
            $adsResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Structure the response
            $response = $adsResult;
        } else {
            $response = array(
                "status" => "2",
                "message" => "Failed to update ad: " . $stmt->error
            );
            $stmt->close(); // Ensure the statement is closed in case of error
        }
    } else {
        $response = array(
            "status" => "error",
            "error" => true,
            "message" => "Invalid input data"
        );
    }
} else {
    $response = array(
        "status" => "error",
        "error" => true,
        "message" => "Invalid request method"
    );
}

echo json_encode($response);
$conn->close();
?>