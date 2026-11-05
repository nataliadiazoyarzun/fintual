<?php

declare(strict_types=1);

use App\Http\RebalanceController;

require __DIR__ . '/../vendor/autoload.php';

// Orígenes separados por coma. Sin la variable, se usan los de desarrollo local.
function allowedOrigins(): array
{
    $configured = getenv('ALLOWED_ORIGINS');

    if ($configured === false || trim($configured) === '') {
        return ['http://localhost:5173', 'http://127.0.0.1:5173'];
    }

    return array_values(array_filter(array_map('trim', explode(',', $configured))));
}

function send(int $status, ?array $body = null): never
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, allowedOrigins(), true)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    http_response_code($status);

    if ($body !== null) {
        header('Content-Type: application/json');
        echo json_encode($body);
    }

    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path !== '/rebalance') {
    send(404, ['error' => 'No encontrado.']);
}

if ($method === 'OPTIONS') {
    send(204);
}

if ($method !== 'POST') {
    header('Allow: POST, OPTIONS');
    send(405, ['error' => 'Método no permitido.']);
}

$response = (new RebalanceController())->handle((string) file_get_contents('php://input'));

send($response['status'], $response['body']);
