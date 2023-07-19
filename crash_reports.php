<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    #get token in header
    $token = getallheaders()['Authorization'];

    if ($token) {
        $decode = base64_decode($token);
        $payload = json_decode($decode, true);
        $expired = DateTime::createFromFormat('Y-m-d H:i:s', $payload['exp']);
        $currentDate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        #check expired token
        if ($expired >= $currentDate) {
            # Execute the query
            $data = $conn->query("SELECT cr.*, u.username FROM crash_report cr JOIN users u ON cr.user_id = u.id");

            if ($data !== false && $data->num_rows > 0) {
                $reports = array();

                while ($row = $data->fetch_assoc()) {
                    array_push($reports, array(
                        'id'            => $row['id'],
                        'userId'        => $row['user_id'],
                        'username'      => $row['username'],
                        'description'   => $row['description'],
                        'image'         => $row['image'],
                        'status'        => $row['status'],
                        'time'          => $row['time'],
                    ));
                }

                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Successfully fetched data', 'data' => $reports,),);
            } else {
                http_response_code(200);
                echo json_encode(array('success' => false, 'message' => 'No data available', 'data' => []));
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
