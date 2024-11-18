<?php

require_once 'C:/wamp64/www/app/Models/Prisma.php';



// Database connection details
$dsn = 'mysql:host=localhost;dbname=boilerplate';
$user = 'root';
$password = '';

$prisma = new Prisma($dsn, $user, $password);

try {
    // Test: Find many users
    echo "Fetching users:\n";
    $users = $prisma->findManyUser();
    print_r($users);

    // Test: Create a user
    echo "Creating a user:\n";
    $newUserId = $prisma->createUser([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'roleId' => 1
    ]);
    echo "New User ID: $newUserId\n";

    // Test: Update a user
    echo "Updating the user:\n";
    $updatedRows = $prisma->updateUser(['name' => 'John Updated'], ['id' => $newUserId]);
    echo "Updated rows: $updatedRows\n";

    // Test: Delete a user
    echo "Deleting the user:\n";
    $deletedRows = $prisma->deleteUser(['id' => $newUserId]);
    echo "Deleted rows: $deletedRows\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
