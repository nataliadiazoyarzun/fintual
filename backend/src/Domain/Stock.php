<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

final readonly class Stock
{
    public string $ticker;

    public function __construct(
        string $ticker,
        public float $quantity,
        public float $currentPrice,
    ) {
        $normalized = strtoupper(trim($ticker));

        if ($normalized === '') {
            throw new InvalidArgumentException('Ticker must not be empty.');
        }

        if ($currentPrice <= 0) {
            throw new InvalidArgumentException('Current price must be greater than zero.');
        }

        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity must not be negative.');
        }

        $this->ticker = $normalized;
    }

    public function value(): float
    {
        return $this->quantity * $this->currentPrice;
    }
}
