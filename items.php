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
        if ($expired >= $currentDate && $payload['role']) {
            // Query to retrieve the username and password from the users table
            $query = "SELECT * FROM items";

            // Execute the query
            $data = $conn->query($query);

            if ($data !== false && $data->num_rows > 0) {
                $items = array();

                while ($row = $data->fetch_assoc()) {
                    array_push($items, array(
                        'id'    => $row['id'],
                        'name'  => $row['name'],
                        'total' => $row['total'],
                    ));
                }

                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Successfully fetched data', 'data' => $items,),);
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
