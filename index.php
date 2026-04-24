<?php

require __DIR__ . '/vendor/autoload.php';

use Src\Database\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

$movementParam = trim($_GET['movimento'] ?? '');

if ($movementParam === '') {
    http_response_code(400);
    echo json_encode(['error' => 'O parâmetro "movimento" é obrigatório.']);
    exit;
}

if (strlen($movementParam) > 255) {
    http_response_code(400);
    echo json_encode(['error' => 'O parâmetro "movimento" é inválido.']);
    exit;
}

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
