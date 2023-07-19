<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = $_POST['id'];
    $description    = $_POST['description'];
    $status         = $_POST['status'];
    $time           = null;
    $image          = $_FILES['image'] ?? null;

    if (isset($_POST['time'])) {
        $formattedDate = DateTime::createFromFormat('d M Y', $_POST['time']);
        $time = $formattedDate->format('Y-m-d H:i:s');
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

            $allowImg = false;
            $move = false;
            $nameSave = null;

            if ($image != null) {
                $x = explode('.', $image['name']);
                $mimeFile = strtolower(end($x));
                echo $mimeFile;
                $uuid = generateUuid();
                $nameSave = 'images/crash_report/' . $uuid . '.' . $mimeFile;
                $fileTmp = $image['tmp_name'];
                $move = move_uploaded_file($fileTmp, $nameSave);
                $allowImg = in_array($mimeFile, $allowed);
            }

            #check allowed image
            if ($allowImg) {
                if ($move) {
                    $id = generateUuid();
                    $userId = $payload['id'];

                    # Request for update data for the reports table
                    $query = "INSERT INTO crash_report(id, user_id, description, image, status, time) VALUES ('$id','$userId','$description','$nameSave','$status','$time')";

                    # Execute the query
                    $data = $conn->query($query);

                    if ($data === true) {
                        http_response_code(200);
                        echo json_encode(array('success' => true, 'message' => 'Successfully update data'),);
                    } else {
                        http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Failed to update data'));
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
