<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

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
        if ($allocation === [] && $this->totalValue() > 0) {
            throw new InvalidArgumentException('Allocation must not be empty when the portfolio has value.');
        }

        if ($allocation !== [] && array_sum($allocation) != 100) {
            throw new InvalidArgumentException('Allocation percentages must sum to 100.');
        }

        $tickers = array_map(fn (Stock $stock): string => $stock->ticker, $stocks);

        foreach (array_keys($allocation) as $ticker) {
            if (!in_array($ticker, $tickers, true)) {
                throw new InvalidArgumentException("Allocation ticker {$ticker} is not in the stocks.");
            }
        }
    }

    public function totalValue(): float
    {
        $total = 0.0;

        foreach ($this->stocks as $stock) {
            $total += $stock->value();
        }

        return $total;
    }

    /**
     * @return array<int, array{ticker: string, action: string, amount: float, shares: float}>
     */
    public function rebalance(): array
    {
        $total = $this->totalValue();
        $recommendations = [];

        foreach ($this->stocks as $stock) {
            $targetValue = $total * ($this->allocation[$stock->ticker] ?? 0) / 100;
            $delta = round($targetValue - $stock->value(), 2);

            if ($delta == 0) {
                continue;
            }

            $recommendations[] = [
                'ticker' => $stock->ticker,
                'action' => ($delta > 0 ? Action::BUY : Action::SELL)->value,
                'amount' => abs($delta),
                'shares' => abs($delta) / $stock->currentPrice,
            ];
        }

        usort(
            $recommendations,
            fn (array $a, array $b): int => ($a['action'] === 'SELL' ? 0 : 1) <=> ($b['action'] === 'SELL' ? 0 : 1),
        );

        return $recommendations;
    }
}
