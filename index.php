<?php

require __DIR__ . '/vendor/autoload.php';

use Src\Database\Connection;
use Src\Http\Response;
use Src\Ranking\RankingRepository;

set_exception_handler(function (Throwable $e) {
    header('Content-Type: application/json');
    error_log($e->getMessage());
    Response::error('Erro interno do servidor.', 500);
});

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    Response::error('Método não permitido.', 405);
}

if (count($_GET) === 0) {
    Response::error('É esperado ao menos um parâmetro.', 400);
}

if (count($_GET) > 1) {
    Response::error('Número de parâmetros indevido.', 400);
}

$movementParam = trim($_GET['movimento'] ?? '');

if ($movementParam === '') {
    Response::error('O parâmetro "movimento" é obrigatório.', 400);
}

if (strlen($movementParam) > 255) {
    Response::error('O parâmetro "movimento" é inválido.', 400);
}

$pdo = Connection::make();
$rankingRepository = new RankingRepository($pdo);
$ranking = $rankingRepository->getByMovement($movementParam);

if ($ranking === null) {
    Response::error('Movimento não encontrado.', 404);
}

Response::success($ranking);
