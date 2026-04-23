<?php

require __DIR__ . '/vendor/autoload.php';

use Src\Database\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

try {
    $pdo = Connection::make();

    $stmt = $pdo->query('SELECT * FROM user');

    echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
