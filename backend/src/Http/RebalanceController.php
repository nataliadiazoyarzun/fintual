<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\Portfolio;
use App\Domain\Stock;
use InvalidArgumentException;
use JsonException;

final class RebalanceController
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function handle(string $rawBody): array
    {
        $data = $this->decode($rawBody);

        if ($data === null) {
            return ['status' => 400, 'body' => ['error' => 'Solicitud inválida.']];
        }

        try {
            $stocks = array_map(
                fn (array $item): Stock => new Stock($item['ticker'], $item['quantity'], $item['price']),
                $data['stocks'],
            );
            $portfolio = new Portfolio($stocks, $data['allocation']);

            return [
                'status' => 200,
                'body' => [
                    'totalValue' => $portfolio->totalValue(),
                    'recommendations' => $portfolio->rebalance(),
                ],
            ];
        } catch (InvalidArgumentException $e) {
            return ['status' => 422, 'body' => ['error' => $e->getMessage()]];
        }
    }

    /**
     * Returns the decoded payload, or null when its structure is invalid.
     */
    private function decode(string $rawBody): ?array
    {
        try {
            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($data) || !is_array($data['stocks'] ?? null) || !is_array($data['allocation'] ?? null)) {
            return null;
        }

        foreach ($data['stocks'] as $item) {
            if (
                !is_array($item)
                || !is_string($item['ticker'] ?? null)
                || !$this->isNumber($item['quantity'] ?? null)
                || !$this->isNumber($item['price'] ?? null)
            ) {
                return null;
            }
        }

        foreach ($data['allocation'] as $percentage) {
            if (!$this->isNumber($percentage)) {
                return null;
            }
        }

        return $data;
    }

    private function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }
}
