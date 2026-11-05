<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Portfolio;
use App\Domain\Stock;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_rebalance_buys_a_stock_in_the_allocation_that_has_zero_quantity(): void
    {
        $portfolio = new Portfolio(
            [new Stock('AAPL', 10, 100), new Stock('META', 0, 50)],
            ['AAPL' => 50, 'META' => 50],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'AAPL', 'SELL', 500.00, 5.0);
        $this->assertRecommendation($recommendations[1], 'META', 'BUY', 500.00, 10.0);
    }

    #[DataProvider('allocationsNotSummingTo100')]
    public function test_it_rejects_an_allocation_that_does_not_sum_to_100(array $allocation): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Portfolio(
            [new Stock('META', 10, 100), new Stock('AAPL', 10, 100)],
            $allocation,
        );
    }

    public static function allocationsNotSummingTo100(): array
    {
        return [
            'below 100' => [['META' => 40, 'AAPL' => 50]],
            'above 100' => [['META' => 60, 'AAPL' => 50]],
        ];
    }

    public function test_it_rejects_an_allocation_ticker_that_is_not_in_the_stocks(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ZZZ');

        new Portfolio(
            [new Stock('AAPL', 10, 100)],
            ['AAPL' => 50, 'ZZZ' => 50],
        );
    }

    public function test_it_rejects_an_empty_allocation_when_stocks_have_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Portfolio([new Stock('AAPL', 10, 100)], []);
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
