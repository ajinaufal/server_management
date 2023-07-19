<?php
global $conn;
#initial db
$host = '127.0.0.1';
$usernamedb = 'root';
$passworddb = 'root';
$dbname = 'manajemen';
$portdb = '8889';

// Create a connection to MySQL
$conn = new mysqli($host, $usernamedb, $passworddb, $dbname, $portdb);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $conn->connect_error, 'token' => null]);
}

function generateUuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
