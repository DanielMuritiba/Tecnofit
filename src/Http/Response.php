<?php

namespace Src\Http;

class Response
{
    /** Sends a 200 JSON response and terminates execution. */
    public static function success(array $data): void
    {
        http_response_code(200);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /** Sends a JSON error response with the given HTTP status code and terminates execution. */
    public static function error(string $message, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
        exit;
    }
}
