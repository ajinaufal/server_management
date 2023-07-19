<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    # get token in header
    $token = getallheaders()['Authorization'];

    if ($token) {
        $decode = base64_decode($token);
        $payload = json_decode($decode, true);
        $expired = DateTime::createFromFormat('Y-m-d H:i:s', $payload['exp']);
        $currentDate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        # check expired token
        if ($expired >= $currentDate) {

            # Execute the query
            $search = $conn->query("SELECT * FROM `crash_report` WHERE id='$id' LIMIT 1");

            $row = $search->fetch_assoc();
            $img = $row['image'];


            if (!empty($img)) {
                if (file_exists($img)) {
                    if (!unlink($img)) {
                        http_response_code(404);
                        echo json_encode(array('success' => false, 'message' => 'Failed delete file'));
                    };
                }
            }

            # Execute the query
            $dataDelete = $conn->query("DELETE FROM crash_report WHERE id='$id'");

            if ($dataDelete === true) {
                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Successfully delete report'),);
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Failed delete report'));
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
