<?php

require __DIR__ . '/vendor/autoload.php';

use Src\Database\Connection;
use Src\Ranking\RankingRepository;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

if(count($_GET) == 0){
    http_response_code(400);
    echo json_encode(['error' => 'É esperado ao menos um parâmetro.']);
    exit;
}elseif(count($_GET) > 1){
    http_response_code(400);
    echo json_encode(['error' => 'Numero de parâmetros indevido']);
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
    $rankingRepository = new RankingRepository($pdo);
    $ranking = $rankingRepository->getByMovement($movementParam);

    if ($ranking === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Movimento não encontrado.']);
        exit;
    }

    echo json_encode($ranking, JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
