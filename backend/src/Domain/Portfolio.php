<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

final readonly class Portfolio
{
    private array $allocation;

    /**
     * @param Stock[] $stocks
     * @param array<string, float|int> $allocation ticker => percentage (0-100)
     */
    public function __construct(
        private array $stocks,
        array $allocation,
    ) {
        $this->allocation = $this->normalizeTickers($allocation);

        $this->assertAllocationNotEmptyWhenPortfolioHasValue();
        $this->assertNoNegativePercentages();
        $this->assertAllocationSumsTo100();
        $this->assertUniqueTickers();
        $this->assertAllocationTickersExist();
    }

    /**
     * @param array<string, float|int> $allocation
     * @return array<string, float|int>
     */
    private function normalizeTickers(array $allocation): array
    {
        $normalized = [];

        foreach ($allocation as $ticker => $percentage) {
            $normalized[strtoupper(trim((string) $ticker))] = $percentage;
        }

        return $normalized;
    }

    private function assertAllocationNotEmptyWhenPortfolioHasValue(): void
    {
        if ($this->allocation === [] && $this->totalValue() > 0) {
            throw new InvalidArgumentException('La distribución objetivo no puede estar vacía cuando el portafolio tiene valor.');
        }
    }

    private function assertNoNegativePercentages(): void
    {
        foreach ($this->allocation as $percentage) {
            if ($percentage < 0) {
                throw new InvalidArgumentException('Los porcentajes de la distribución objetivo no pueden ser negativos.');
            }
        }
    }

    private function assertAllocationSumsTo100(): void
    {
        if ($this->allocation !== [] && abs(array_sum($this->allocation) - 100) > 1e-6) {
            throw new InvalidArgumentException('Los porcentajes de la distribución objetivo deben sumar 100.');
        }
    }

    private function assertUniqueTickers(): void
    {
        foreach (array_count_values($this->stockTickers()) as $ticker => $count) {
            if ($count > 1) {
                throw new InvalidArgumentException("El ticker {$ticker} está duplicado en las acciones.");
            }
        }
    }

    private function assertAllocationTickersExist(): void
    {
        $tickers = $this->stockTickers();

        foreach (array_keys($this->allocation) as $ticker) {
            if (!in_array($ticker, $tickers, true)) {
                throw new InvalidArgumentException("El ticker {$ticker} de la distribución objetivo no está en las acciones.");
            }
        }
    }

    /**
     * @return string[]
     */
    private function stockTickers(): array
    {
        return array_map(fn (Stock $stock): string => $stock->ticker, $this->stocks);
    }

    /**
     * Sells first, then buys; alphabetical by ticker within each group.
     *
     * @param array{ticker: string, action: string, amount: float, shares: float} $recommendation
     * @return array{int, string}
     */
    private function sortKey(array $recommendation): array
    {
        $group = $recommendation['action'] === Action::SELL->value ? 0 : 1;

        return [$group, $recommendation['ticker']];
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
            fn (array $a, array $b): int => $this->sortKey($a) <=> $this->sortKey($b),
        );

        return $recommendations;
    }
}
