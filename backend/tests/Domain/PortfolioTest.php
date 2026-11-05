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

    public function test_it_rejects_a_negative_allocation_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Portfolio(
            [new Stock('META', 10, 100), new Stock('AAPL', 10, 100)],
            ['META' => -10, 'AAPL' => 110],
        );
    }

    #[DataProvider('duplicatedStocks')]
    public function test_it_rejects_duplicated_tickers_in_the_stocks(string $first, string $second): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Portfolio(
            [new Stock($first, 10, 100), new Stock($second, 5, 100)],
            ['AAPL' => 100],
        );
    }

    public static function duplicatedStocks(): array
    {
        return [
            'exact duplicate' => ['AAPL', 'AAPL'],
            'duplicate after normalization' => ['aapl', 'AAPL'],
        ];
    }

    public function test_rebalance_converts_amounts_to_shares_using_each_stocks_own_price(): void
    {
        $portfolio = new Portfolio(
            [new Stock('META', 10, 50), new Stock('AAPL', 1, 500)],
            ['META' => 80, 'AAPL' => 20],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'AAPL', 'SELL', 300.00, 0.6);
        $this->assertRecommendation($recommendations[1], 'META', 'BUY', 300.00, 6.0);
    }

    public function test_rebalance_supports_fractional_shares(): void
    {
        $portfolio = new Portfolio(
            [new Stock('A', 1, 300), new Stock('B', 0, 7)],
            ['A' => 50, 'B' => 50],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'A', 'SELL', 150.00, 0.5);
        $this->assertRecommendation($recommendations[1], 'B', 'BUY', 150.00, 150 / 7);
    }

    public function test_rebalance_sells_entirely_a_stock_missing_from_the_allocation(): void
    {
        $portfolio = new Portfolio(
            [new Stock('A', 10, 100), new Stock('B', 4, 50)],
            ['A' => 100],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'B', 'SELL', 200.00, 4.0);
        $this->assertRecommendation($recommendations[1], 'A', 'BUY', 200.00, 2.0);
    }

    public function test_allocation_keys_are_normalized_like_stock_tickers(): void
    {
        $portfolio = new Portfolio(
            [new Stock('AAPL', 10, 100), new Stock('META', 10, 100)],
            ['aapl' => 60, ' meta ' => 40],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(2, $recommendations);
        $this->assertRecommendation($recommendations[0], 'META', 'SELL', 200.00, 2.0);
        $this->assertRecommendation($recommendations[1], 'AAPL', 'BUY', 200.00, 2.0);
    }

    public function test_it_accepts_an_allocation_whose_float_sum_is_100_within_floating_point_error(): void
    {
        // 0.1 + 64.1 + 35.8 evaluates to 99.999999999999986 in floating point.
        $portfolio = new Portfolio(
            [new Stock('A', 10, 100), new Stock('B', 10, 100), new Stock('C', 10, 100)],
            ['A' => 0.1, 'B' => 64.1, 'C' => 35.8],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertCount(3, $recommendations);
        $this->assertRecommendation($recommendations[0], 'A', 'SELL', 997.00, 9.97);
        $this->assertRecommendation($recommendations[1], 'B', 'BUY', 923.00, 9.23);
        $this->assertRecommendation($recommendations[2], 'C', 'BUY', 74.00, 0.74);
    }

    public function test_rebalance_orders_sells_then_buys_each_group_alphabetically_by_ticker(): void
    {
        $portfolio = new Portfolio(
            [
                new Stock('D', 10, 100),
                new Stock('C', 10, 100),
                new Stock('B', 10, 100),
                new Stock('A', 10, 100),
            ],
            ['A' => 40, 'B' => 10, 'C' => 40, 'D' => 10],
        );

        $recommendations = $portfolio->rebalance();

        $this->assertSame(
            [['B', 'SELL'], ['D', 'SELL'], ['A', 'BUY'], ['C', 'BUY']],
            array_map(fn (array $r): array => [$r['ticker'], $r['action']], $recommendations),
        );
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
