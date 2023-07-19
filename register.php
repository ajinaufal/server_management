<?php
require 'configdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    #get username and password in post
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $induk = $_POST['induk'];

    $uuid = generateUuid();

    $query = "INSERT INTO users (username, password, role, induk, id) VALUES ('$username', '$password', '$role', '$induk', '$uuid')";

    if ($conn->query($query) === TRUE) {
        // Registration successful
        $conn->close();
        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => 'Your register was successful'));
    } else {
        // Registration failed
        $conn->close();
        http_response_code(400);
        echo json_encode(array('success' => true, 'message' => $conn->error));
    }
} else {
    $conn->close();
    http_response_code(405);
    echo json_encode(array('success' => true, 'message' => 'Method Not Allowed error'));
}


