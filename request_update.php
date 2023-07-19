<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    #get username and password in post
    $id = $_POST['id'];
    $name = $_POST['name'];
    $total = $_POST['total'];

    #get token in header
    $token = getallheaders()['Authorization'];

    if ($token) {
        $decode = base64_decode($token);
        $payload = json_decode($decode, true);
        $expired = DateTime::createFromFormat('Y-m-d H:i:s', $payload['exp']);

        $currentDate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        #check expired token
        if ($expired >= $currentDate && $payload['role']) {
            // get current time add items
            $currentDateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            $currentDate = $currentDateTime->format('Y-m-d H:i:s');

            // Query to retrieve the username and password from the users table
            $query = "UPDATE request SET name='$name', total='$total' WHERE id='$id'";

            // Execute the query
            $data = $conn->query($query);

            if ($data == true) {
                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Successfully update request item'),);
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Failed update request item'),);
            }
        } else {
            http_response_code(401);
            echo json_encode(array('success' => false, 'message' => 'It specifies the user will have no access permission.'),);
        }
    } else {
        http_response_code(401);
        echo json_encode(array('success' => false, 'message' => 'It specifies the user will have no access permission.'),);
    }
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method Not Allowed error'));
}
