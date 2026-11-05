<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Stock;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StockTest extends TestCase
{
    public function test_it_exposes_its_data(): void
    {
        $stock = new Stock('AAPL', 10, 150.5);

        $this->assertSame('AAPL', $stock->ticker);
        $this->assertSame(10.0, $stock->quantity);
        $this->assertSame(150.5, $stock->currentPrice);
    }

    public function test_value_is_quantity_times_current_price(): void
    {
        $stock = new Stock('AAPL', 10, 2.5);

        $this->assertEqualsWithDelta(25.0, $stock->value(), 1e-9);
    }

    public function test_value_supports_fractional_quantities(): void
    {
        $stock = new Stock('META', 0.5, 300);

        $this->assertEqualsWithDelta(150.0, $stock->value(), 1e-9);
    }

    public function test_value_is_zero_when_quantity_is_zero(): void
    {
        $stock = new Stock('META', 0, 300);

        $this->assertSame(0.0, $stock->value());
    }

    public function test_ticker_is_normalized_to_uppercase_and_trimmed(): void
    {
        $stock = new Stock('  aapl ', 1, 10);

        $this->assertSame('AAPL', $stock->ticker);
    }

    #[DataProvider('invalidTickers')]
    public function test_it_rejects_an_empty_ticker(string $ticker): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Stock($ticker, 1, 10);
    }

    public static function invalidTickers(): array
    {
        return [
            'empty' => [''],
            'blank' => ['   '],
        ];
    }

    #[DataProvider('invalidPrices')]
    public function test_it_rejects_a_non_positive_price(float $price): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Stock('AAPL', 1, $price);
    }

    public static function invalidPrices(): array
    {
        return [
            'zero' => [0.0],
            'negative' => [-5.0],
        ];
    }

    public function test_it_rejects_a_negative_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Stock('AAPL', -1, 10);
    }
}
