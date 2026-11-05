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
            throw new InvalidArgumentException('El ticker no puede estar vacío.');
        }

        if ($currentPrice <= 0) {
            throw new InvalidArgumentException('El precio actual debe ser mayor que cero.');
        }

        if ($quantity < 0) {
            throw new InvalidArgumentException('La cantidad no puede ser negativa.');
        }

        $this->ticker = $normalized;
    }

    public function value(): float
    {
        return $this->quantity * $this->currentPrice;
    }
}
