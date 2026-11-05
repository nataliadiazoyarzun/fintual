<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Portfolio
{
    /**
     * @param Stock[] $stocks
     * @param array<string, float|int> $allocation ticker => percentage (0-100)
     */
    public function __construct(
        private array $stocks,
        private array $allocation,
    ) {
    }

    public function totalValue(): float
    {
        $total = 0.0;

        foreach ($this->stocks as $stock) {
            $total += $stock->value();
        }

        return $total;
    }

    public function rebalance(): array
    {
        return [];
    }
}
