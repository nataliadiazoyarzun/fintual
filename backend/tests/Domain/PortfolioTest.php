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

    public function test_rebalance_sells_the_overweight_stock_and_buys_the_underweight_one(): void
    {
        $portfolio = new Portfolio(
            [new Stock('META', 10, 100), new Stock('AAPL', 10, 100)],
            ['META' => 40, 'AAPL' => 60],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'META', 'SELL', 200.00, 2.0);
        $this->assertRecommendation($recommendations[1], 'AAPL', 'BUY', 200.00, 2.0);
    }

    public function test_rebalance_lists_sells_before_buys_regardless_of_stock_order(): void
    {
        $portfolio = new Portfolio(
            [new Stock('AAPL', 10, 100), new Stock('META', 10, 100)],
            ['META' => 40, 'AAPL' => 60],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertSame('SELL', $recommendations[0]['action']);
        $this->assertSame('BUY', $recommendations[1]['action']);
    }

    private function assertRecommendation(
        array $recommendation,
        string $ticker,
        string $action,
        float $amount,
        float $shares,
    ): void {
        $this->assertSame($ticker, $recommendation['ticker']);
        $this->assertSame($action, $recommendation['action']);
        $this->assertEqualsWithDelta($amount, $recommendation['amount'], 1e-9);
        $this->assertEqualsWithDelta($shares, $recommendation['shares'], 1e-9);
    }
}
