<?php

declare(strict_types=1);

use App\Http\RebalanceController;

require __DIR__ . '/../vendor/autoload.php';

const ALLOWED_ORIGINS = ['http://localhost:5173', 'http://127.0.0.1:5173'];

function send(int $status, ?array $body = null): never
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, ALLOWED_ORIGINS, true)) {
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
    send(404, ['error' => 'Not found.']);
}

if ($method === 'OPTIONS') {
    send(204);
}

if ($method !== 'POST') {
    header('Allow: POST, OPTIONS');
    send(405, ['error' => 'Method not allowed.']);
}

$response = (new RebalanceController())->handle((string) file_get_contents('php://input'));

send($response['status'], $response['body']);
