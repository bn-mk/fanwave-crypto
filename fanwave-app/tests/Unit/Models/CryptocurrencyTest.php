<?php

namespace Tests\Unit\Models;

use App\Models\Cryptocurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptocurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_cryptocurrency(): void
    {
        $cryptocurrency = Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'image' => 'https://example.com/bitcoin.png',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'last_updated' => now(),
        ]);

        $this->assertDatabaseHas('cryptocurrencies', [
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
        ]);

        $this->assertEquals('bitcoin', $cryptocurrency->coin_id);
        $this->assertEquals('BTC', $cryptocurrency->symbol);
        $this->assertEquals('Bitcoin', $cryptocurrency->name);
    }

    public function test_it_casts_numeric_fields_correctly(): void
    {
        $cryptocurrency = Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => '50000.12345678',
            'market_cap' => '1000000000000',
            'market_cap_rank' => '1',
            'price_change_percentage_24h' => '2.5432',
            'last_updated' => now(),
        ]);

        $this->assertIsNumeric($cryptocurrency->current_price);
        $this->assertEquals('50000.12345678', $cryptocurrency->current_price);

        $this->assertIsInt($cryptocurrency->market_cap);
        $this->assertEquals(1000000000000, $cryptocurrency->market_cap);

        $this->assertIsInt($cryptocurrency->market_cap_rank);
        $this->assertEquals(1, $cryptocurrency->market_cap_rank);

        $this->assertEquals('2.5432', $cryptocurrency->price_change_percentage_24h);
    }

    public function test_it_casts_datetime_fields_correctly(): void
    {
        $now = now();
        $cryptocurrency = Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'ath_date' => $now,
            'atl_date' => $now,
            'last_updated' => $now,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $cryptocurrency->ath_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $cryptocurrency->atl_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $cryptocurrency->last_updated);
    }

    public function test_it_can_scope_top_by_market_cap(): void
    {
        Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'last_updated' => now(),
        ]);

        Cryptocurrency::create([
            'coin_id' => 'ethereum',
            'symbol' => 'ETH',
            'name' => 'Ethereum',
            'current_price' => 3000.00,
            'market_cap' => 360000000000,
            'market_cap_rank' => 2,
            'last_updated' => now(),
        ]);

        Cryptocurrency::create([
            'coin_id' => 'tether',
            'symbol' => 'USDT',
            'name' => 'Tether',
            'current_price' => 1.00,
            'market_cap' => 80000000000,
            'market_cap_rank' => 3,
            'last_updated' => now(),
        ]);

        Cryptocurrency::create([
            'coin_id' => 'unknown',
            'symbol' => 'UNK',
            'name' => 'Unknown',
            'current_price' => 1.00,
            'market_cap' => 1000000,
            'market_cap_rank' => null,
            'last_updated' => now(),
        ]);

        $topCryptos = Cryptocurrency::topByMarketCap(2)->get();

        $this->assertCount(2, $topCryptos);
        $this->assertEquals('bitcoin', $topCryptos[0]->coin_id);
        $this->assertEquals('ethereum', $topCryptos[1]->coin_id);

        $topCryptosDefault = Cryptocurrency::topByMarketCap()->get();
        $this->assertCount(3, $topCryptosDefault);
    }

    public function test_it_has_formatted_price_attribute(): void
    {
        $cryptocurrency = Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.12,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$50,000.12', $cryptocurrency->formatted_price);
    }

    public function test_it_has_formatted_market_cap_attribute(): void
    {
        $bitcoin = Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$1.00T', $bitcoin->formatted_market_cap);

        $ethereum = Cryptocurrency::create([
            'coin_id' => 'ethereum',
            'symbol' => 'ETH',
            'name' => 'Ethereum',
            'current_price' => 3000.00,
            'market_cap' => 360000000000,
            'market_cap_rank' => 2,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$360.00B', $ethereum->formatted_market_cap);

        $smallCoin = Cryptocurrency::create([
            'coin_id' => 'small-coin',
            'symbol' => 'SMALL',
            'name' => 'Small Coin',
            'current_price' => 1.00,
            'market_cap' => 50000000,
            'market_cap_rank' => 100,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$50.00M', $smallCoin->formatted_market_cap);

        $tinyCoin = Cryptocurrency::create([
            'coin_id' => 'tiny-coin',
            'symbol' => 'TINY',
            'name' => 'Tiny Coin',
            'current_price' => 0.01,
            'market_cap' => 500000,
            'market_cap_rank' => 1000,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$500,000.00', $tinyCoin->formatted_market_cap);
    }

    public function test_it_handles_null_market_cap_in_formatting(): void
    {
        $cryptocurrency = Cryptocurrency::create([
            'coin_id' => 'test-coin',
            'symbol' => 'TEST',
            'name' => 'Test Coin',
            'current_price' => 1.00,
            'market_cap' => null,
            'market_cap_rank' => null,
            'last_updated' => now(),
        ]);

        $this->assertEquals('$1.00T', $cryptocurrency->formatted_market_cap);
    }

    public function test_it_can_be_mass_assigned(): void
    {
        $data = [
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'image' => 'https://example.com/bitcoin.png',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'fully_diluted_valuation' => 1050000000000,
            'total_volume' => 25000000000,
            'high_24h' => 51000.00,
            'low_24h' => 49000.00,
            'price_change_24h' => 1000.00,
            'price_change_percentage_24h' => 2.04,
            'market_cap_change_24h' => 20000000000,
            'market_cap_change_percentage_24h' => 2.04,
            'circulating_supply' => 19000000.00,
            'total_supply' => 19000000.00,
            'max_supply' => 21000000.00,
            'ath' => 69000.00,
            'ath_change_percentage' => -27.54,
            'ath_date' => now(),
            'atl' => 0.05,
            'atl_change_percentage' => 9999.9999,
            'atl_date' => now(),
            'last_updated' => now(),
        ];

        $cryptocurrency = Cryptocurrency::create($data);

        foreach (array_keys($data) as $key) {
            if (!in_array($key, ['ath_date', 'atl_date', 'last_updated'])) {
                $this->assertEquals($data[$key], $cryptocurrency->{$key});
            }
        }

        $this->assertDatabaseHas('cryptocurrencies', [
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
        ]);
    }

    public function test_it_excludes_cryptocurrencies_without_market_cap_rank_from_top_scope(): void
    {
        Cryptocurrency::create([
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.00,
            'market_cap' => 1000000000000,
            'market_cap_rank' => 1,
            'last_updated' => now(),
        ]);

        Cryptocurrency::create([
            'coin_id' => 'unranked',
            'symbol' => 'UNR',
            'name' => 'Unranked Coin',
            'current_price' => 1.00,
            'market_cap' => 1000000,
            'market_cap_rank' => null,
            'last_updated' => now(),
        ]);

        $topCryptos = Cryptocurrency::topByMarketCap()->get();

        $this->assertCount(1, $topCryptos);
        $this->assertEquals('bitcoin', $topCryptos[0]->coin_id);
    }
}
