<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Portfolio;
use App\Domain\Stock;
use PHPUnit\Framework\TestCase;

final class PortfolioTest extends TestCase
{
    public function test_total_value_is_the_sum_of_all_stock_values(): void
    {
        $portfolio = new Portfolio(
            [new Stock('META', 10, 100), new Stock('AAPL', 5, 200)],
            ['META' => 50, 'AAPL' => 50],
        );

        $this->assertEqualsWithDelta(2000.0, $portfolio->totalValue(), 1e-9);
    }

    public function test_empty_portfolio_has_zero_value_and_no_recommendations(): void
    {
        $portfolio = new Portfolio([], []);

        $this->assertSame(0.0, $portfolio->totalValue());
        $this->assertSame([], $portfolio->rebalance());
    }

    public function test_already_balanced_portfolio_has_no_recommendations(): void
    {
        $portfolio = new Portfolio(
            [new Stock('META', 10, 100), new Stock('AAPL', 5, 200)],
            ['META' => 50, 'AAPL' => 50],
        );

        $this->assertSame([], $portfolio->rebalance());
    }
}
