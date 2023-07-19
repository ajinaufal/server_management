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
            $dataRequest = $conn->query("SELECT r.id, r.user_id, r.name, r.total, r.created_at, u.username FROM request r JOIN users u ON r.user_id = u.id");

            if ($dataRequest !== false && $dataRequest->num_rows > 0) {
                $request = array();

                while ($row = $dataRequest->fetch_assoc()) {
                    array_push($request, array(
                        'id'            => $row['id'],
                        'user_id'       => $row['user_id'],
                        'user_name'     => $row['username'],
                        'name'          => $row['name'],
                        'total'         => $row['total'],
                        'created_at'    => $row['created_at'],
                    ));
                }

                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Successfully fetched data', 'data' => $request,),);
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
