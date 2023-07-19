<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    #get username and password in post
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Query to retrieve the username and password from the users table
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";

        // Execute the query
        $data = $conn->query($query);

        if ($data !== false && $data->num_rows > 0) {
            #make exp date for token
            $currentDate = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            $currentDate->modify('+7 days');
            $exp = $currentDate->format('Y-m-d H:i:s');

            #Fetch the first row from the result
            $row = $data->fetch_assoc();

            if ($data->num_rows > 0) {
                #success login
                $payloadJwt = [
                    'id'        => $row['id'],
                    'username'  => $row['username'],
                    'role'      => $row['role'],
                    'exp'       => $exp,
                ];

                $jwt = base64_encode(json_encode($payloadJwt));

                http_response_code(200);
                echo json_encode(array('success' => true, 'message' => 'Your login was successful', 'token' => $jwt));
            } else {
                #login failed
                http_response_code(401);
                echo json_encode(array('success' => false, 'message' => 'You are not registered', 'token' => null));
            }
        } else {
            #login failed
            http_response_code(401);
            echo json_encode(array('success' => false, 'message' => 'You are not registered', 'token' => null));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('success' => false, 'message' => 'username and password not available'));
    }

    // Close the connection
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method Not Allowed error'));
}
