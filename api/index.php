<?php

require_once '../app/Models/Prisma.php';

header("Content-Type: application/json");

$dsn = 'mysql:host=localhost;dbname=boilerplate'; // Update your DB details
$user = 'root';
$password = '';

$prisma = new Prisma($dsn, $user, $password);

// Route handling
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

try {
    if ($path === 'users') {
        if ($requestMethod === 'GET') {
            // Fetch all users
            echo json_encode($prisma->findManyUser());
        } elseif ($requestMethod === 'POST') {
            // Create a new user
            $data = json_decode(file_get_contents('php://input'), true);
            $newUserId = $prisma->createUser($data);
            echo json_encode(['id' => $newUserId]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
