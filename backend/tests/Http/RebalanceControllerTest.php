<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\RebalanceController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RebalanceControllerTest extends TestCase
{
    public function test_a_valid_request_returns_200_with_total_value_and_recommendations(): void
    {
        $response = (new RebalanceController())->handle(json_encode([
            'stocks' => [
                ['ticker' => 'META', 'quantity' => 10, 'price' => 100],
                ['ticker' => 'AAPL', 'quantity' => 10, 'price' => 100],
            ],
            'allocation' => ['META' => 40, 'AAPL' => 60],
        ]));

        $this->assertSame(200, $response['status']);
        $this->assertEqualsWithDelta(2000.0, $response['body']['totalValue'], 1e-9);
        $this->assertCount(2, $response['body']['recommendations']);
        $this->assertSame('META', $response['body']['recommendations'][0]['ticker']);
        $this->assertSame('SELL', $response['body']['recommendations'][0]['action']);
        $this->assertEqualsWithDelta(200.0, $response['body']['recommendations'][0]['amount'], 1e-9);
        $this->assertEqualsWithDelta(2.0, $response['body']['recommendations'][0]['shares'], 1e-9);
        $this->assertSame('AAPL', $response['body']['recommendations'][1]['ticker']);
        $this->assertSame('BUY', $response['body']['recommendations'][1]['action']);
    }

    public function test_a_domain_error_returns_422_with_the_domain_message(): void
    {
        $response = (new RebalanceController())->handle(json_encode([
            'stocks' => [['ticker' => 'META', 'quantity' => 10, 'price' => 100]],
            'allocation' => ['META' => 90],
        ]));

        $this->assertSame(422, $response['status']);
        $this->assertSame(
            ['error' => 'Allocation percentages must sum to 100.'],
            $response['body'],
        );
    }

    #[DataProvider('wronglyTypedRequests')]
    public function test_a_request_with_wrongly_typed_values_returns_400(array $payload): void
    {
        $response = (new RebalanceController())->handle(json_encode($payload));

        $this->assertSame(400, $response['status']);
        $this->assertSame(['error' => 'Invalid request.'], $response['body']);
    }

    public static function wronglyTypedRequests(): array
    {
        $stock = ['ticker' => 'META', 'quantity' => 10, 'price' => 100];
        $allocation = ['META' => 100];

        return [
            'quantity as text' => [['stocks' => [[...$stock, 'quantity' => 'abc']], 'allocation' => $allocation]],
            'quantity as boolean' => [['stocks' => [[...$stock, 'quantity' => true]], 'allocation' => $allocation]],
            'quantity as array' => [['stocks' => [[...$stock, 'quantity' => [10]]], 'allocation' => $allocation]],
            'price as text' => [['stocks' => [[...$stock, 'price' => 'abc']], 'allocation' => $allocation]],
            'price as boolean' => [['stocks' => [[...$stock, 'price' => true]], 'allocation' => $allocation]],
            'price as array' => [['stocks' => [[...$stock, 'price' => [100]]], 'allocation' => $allocation]],
            'ticker as number' => [['stocks' => [[...$stock, 'ticker' => 123]], 'allocation' => $allocation]],
            'ticker as array' => [['stocks' => [[...$stock, 'ticker' => ['META']]], 'allocation' => $allocation]],
            'allocation value as text' => [['stocks' => [$stock], 'allocation' => ['META' => 'abc']]],
            'allocation value as boolean' => [['stocks' => [$stock], 'allocation' => ['META' => true]]],
            'allocation value as array' => [['stocks' => [$stock], 'allocation' => ['META' => [100]]]],
            'allocation value as null' => [['stocks' => [$stock], 'allocation' => ['META' => null]]],
            'stocks item is not an object' => [['stocks' => ['META'], 'allocation' => $allocation]],
        ];
    }

    #[DataProvider('malformedRequests')]
    public function test_a_malformed_request_returns_400(string $rawBody): void
    {
        $response = (new RebalanceController())->handle($rawBody);

        $this->assertSame(400, $response['status']);
        $this->assertSame(['error' => 'Invalid request.'], $response['body']);
    }

    public static function malformedRequests(): array
    {
        return [
            'empty body' => [''],
            'invalid json' => ['{not json'],
            'json is not an object' => ['"hello"'],
            'missing stocks' => [json_encode(['allocation' => ['META' => 100]])],
            'missing allocation' => [json_encode(['stocks' => []])],
            'stock without price' => [json_encode([
                'stocks' => [['ticker' => 'META', 'quantity' => 10]],
                'allocation' => ['META' => 100],
            ])],
        ];
    }
}
