<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $building       = $_POST['building'];
    $location       = $_POST['location'];
    $description    = $_POST['description'];
    $repair         = $_POST['repair'];
    $status         = $_POST['status'];
    $start_time     = null;
    $finish_time    = null;
    $imageAfter     = null;
    $imageBefore    = null;

    if (isset($_POST['start_time'])) {
        $formattedDate = DateTime::createFromFormat('d M Y', $_POST['start_time']);
        $start_time = $formattedDate->format('Y-m-d H:i:s');
    }

    if (isset($_POST['finish_time'])) {
        $formattedDate = DateTime::createFromFormat('d M Y', $_POST['finish_time']);
        $finish_time = $formattedDate->format('Y-m-d H:i:s');
    }

    if (isset($_FILES['image_before'])) {
        $imageBefore    = $_FILES['image_before'];
    }

    if (isset($_FILES['image_after'])) {
        $imageAfter    = $_FILES['image_after'];
    }

    # get token in header
    $token = getallheaders()['Authorization'];

    if ($token) {
        $decode = base64_decode($token);
        $payload = json_decode($decode, true);
        $expired = DateTime::createFromFormat('Y-m-d H:i:s', $payload['exp']);

        $currentDate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        #check expired token
        if ($expired >= $currentDate) {
            # allowed mime type file
            $allowed = array('png', 'jpg', 'webp');

            $allowImgBefore = false;
            $moveBefore = false;
            $nameSaveBefore = null;

            if ($imageBefore != null) {
                $xBefore = explode('.', $imageBefore['name']);
                $mimeFileBefore = strtolower(end($xBefore));
                $uuidBefore = generateUuid();
                $nameSaveBefore = 'images/work_report/' . $uuidBefore . '.' . $mimeFileBefore;
                $fileTmpBefore = $imageBefore['tmp_name'];
                $moveBefore = move_uploaded_file($fileTmpBefore, $nameSaveBefore);
                $allowImgBefore = in_array($mimeFileBefore, $allowed);
            }

            $allowImgAfter = false;
            $moveAfter = false;
            $nameSaveAfter = null;

            if ($imageAfter != null) {
                $xAfter = explode('.', $imageAfter['name']);
                $mimeFileAfter = strtolower(end($xAfter));
                $uuidAfter = generateUuid();
                $nameSaveAfter = 'images/work_report/' . $uuidAfter . '.' . $mimeFileAfter;
                $fileTmpAfter = $imageAfter['tmp_name'];
                $moveAfter = move_uploaded_file($fileTmpAfter, $nameSaveAfter);
                $allowImgAfter = in_array($mimeFileAfter, $allowed);
            }

            #check allowed image
            if ($allowImgBefore && $allowImgAfter) {
                if ($moveBefore && $moveAfter) {
                    $id = generateUuid();
                    $userId = $payload['id'];
                    # Request for update data for the reports table

                    $query =  "INSERT INTO work_report (id, user_id, building, location, description, repair, status, image_before, image_after, start_time, finish_time) VALUES ('$id','$userId','$building','$location','$description','$repair','$status','$nameSaveBefore','$nameSaveAfter','$start_time','$finish_time')";

                    # Execute the query
                    $data = $conn->query($query);

                    if ($data === true) {
                        http_response_code(200);
                        echo json_encode(array('success' => true, 'message' => 'Successfully create data'),);
                    } else {
                        http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Failed to create data'));
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Failed to handle image'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'File type is not allowed'));
            }
        } else {
            http_response_code(401);
            echo json_encode(array('success' => false, 'message' => 'The credential you are using has expired'),);
        }
    } else {
        http_response_code(401);
        echo json_encode(array('success' => false, 'message' => 'It specifies the user will have no access permission'),);
    }
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method Not Allowed error'));
}
