<?php

namespace Tests\Feature\Api;

use App\Models\Cryptocurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CryptocurrencyApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test cryptocurrency data
        $this->createTestCryptocurrencies();
    }

    /** @test */
    public function it_can_get_top_cryptocurrencies()
    {
        $response = $this->getJson('/api/crypto/top');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'symbol',
                        'name',
                        'image',
                        'current_price',
                        'market_cap',
                        'market_cap_rank',
                        'price_change_24h',
                        'price_change_percentage_24h',
                        'total_volume',
                        'circulating_supply',
                        'formatted_price',
                        'formatted_market_cap',
                        'last_updated'
                    ]
                ],
                'meta' => [
                    'count',
                    'limit',
                    'endpoint',
                    'last_updated'
                ]
            ])
            ->assertJson([
                'success' => true,
                'meta' => [
                    'endpoint' => 'top',
                    'limit' => 10,
                    'count' => 3
                ]
            ]);

        // Verify data is ordered by market cap rank
        $data = $response->json('data');
        $this->assertEquals('bitcoin', $data[0]['id']);
        $this->assertEquals('ethereum', $data[1]['id']);
        $this->assertEquals('tether', $data[2]['id']);
    }

    /** @test */
    public function it_can_get_top_cryptocurrencies_with_custom_limit()
    {
        $response = $this->getJson('/api/crypto/top?limit=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'limit' => 2,
                    'count' => 2
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_validates_limit_parameter_for_top_cryptocurrencies()
    {
        // Test invalid limit
        $response = $this->getJson('/api/crypto/top?limit=101');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error'
            ])
            ->assertJsonValidationErrors(['limit']);
    }

    /** @test */
    public function it_can_get_specific_cryptocurrency_by_id()
    {
        $response = $this->getJson('/api/crypto/bitcoin');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'symbol',
                    'name',
                    'image',
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'fully_diluted_valuation',
                    'total_volume',
                    'high_24h',
                    'low_24h',
                    'price_change_24h',
                    'price_change_percentage_24h',
                    'market_cap_change_24h',
                    'market_cap_change_percentage_24h',
                    'circulating_supply',
                    'total_supply',
                    'max_supply',
                    'ath',
                    'ath_change_percentage',
                    'ath_date',
                    'atl',
                    'atl_change_percentage',
                    'atl_date',
                    'last_updated',
                    'formatted_price',
                    'formatted_market_cap'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 'bitcoin',
                    'symbol' => 'BTC',
                    'name' => 'Bitcoin'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_cryptocurrency()
    {
        $response = $this->getJson('/api/crypto/non-existent-coin');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Cryptocurrency not found'
            ]);
    }

    /** @test */
    public function it_can_search_cryptocurrencies_by_name()
    {
        $response = $this->getJson('/api/cryptocurrencies/search?query=bitcoin');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'coin_id',
                        'symbol',
                        'name',
                        'image',
                        'current_price',
                        'market_cap',
                        'market_cap_rank',
                        'price_change_percentage_24h'
                    ]
                ],
                'meta' => [
                    'query',
                    'count',
                    'limit'
                ]
            ])
            ->assertJson([
                'success' => true,
                'meta' => [
                    'query' => 'bitcoin',
                    'count' => 1
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('bitcoin', $data[0]['coin_id']);
    }

    /** @test */
    public function it_can_search_cryptocurrencies_by_symbol()
    {
        $response = $this->getJson('/api/cryptocurrencies/search?query=BTC');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'query' => 'BTC',
                    'count' => 1
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('BTC', $data[0]['symbol']);
    }

    /** @test */
    public function it_validates_search_query_parameter()
    {
        // Test missing query
        $response = $this->getJson('/api/cryptocurrencies/search');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error'
            ])
            ->assertJsonValidationErrors(['query']);

        // Test empty query
        $response = $this->getJson('/api/cryptocurrencies/search?query=');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);

        // Test query too long
        $longQuery = str_repeat('a', 51);
        $response = $this->getJson("/api/cryptocurrencies/search?query={$longQuery}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    /** @test */
    public function it_can_get_cryptocurrency_statistics()
    {
        $response = $this->getJson('/api/cryptocurrencies/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_cryptocurrencies',
                    'last_data_update',
                    'highest_market_cap',
                    'average_price',
                    'data_freshness'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_cryptocurrencies' => 3,
                    'highest_market_cap' => 1000000000000, // Bitcoin's market cap
                ]
            ]);
    }

    /** @test */
    public function it_can_get_cryptocurrencies_index()
    {
        $response = $this->getJson('/api/cryptocurrencies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'coin_id',
                        'symbol',
                        'name',
                        'image',
                        'current_price',
                        'market_cap',
                        'market_cap_rank',
                        'price_change_24h',
                        'price_change_percentage_24h',
                        'total_volume',
                        'last_updated'
                    ]
                ],
                'meta' => [
                    'count',
                    'limit',
                    'last_updated'
                ]
            ])
            ->assertJson([
                'success' => true,
                'meta' => [
                    'limit' => 10,
                    'count' => 3
                ]
            ]);
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        $response = $this->getJson('/api/cryptocurrencies/search?query=nonexistent');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'meta' => [
                    'query' => 'nonexistent',
                    'count' => 0
                ]
            ]);
    }

    /** @test */
    public function it_returns_correct_data_types()
    {
        $response = $this->getJson('/api/crypto/bitcoin');
        $data = $response->json('data');

        // Test numeric fields are properly typed
        $this->assertIsFloat($data['current_price']);
        $this->assertIsInt($data['market_cap']);
        $this->assertIsInt($data['market_cap_rank']);
        $this->assertIsFloat($data['price_change_24h']);
        $this->assertIsFloat($data['price_change_percentage_24h']);
        
        // Test string fields
        $this->assertIsString($data['id']);
        $this->assertIsString($data['symbol']);
        $this->assertIsString($data['name']);
        $this->assertIsString($data['formatted_price']);
        $this->assertIsString($data['formatted_market_cap']);
    }

    /** @test */
    public function it_formats_prices_correctly()
    {
        $response = $this->getJson('/api/crypto/bitcoin');
        $data = $response->json('data');

        // Test price formatting
        $this->assertStringStartsWith('$', $data['formatted_price']);
        $this->assertStringStartsWith('$', $data['formatted_market_cap']);
        
        // Test market cap formatting (should be in T for trillion)
        $this->assertStringEndsWith('T', $data['formatted_market_cap']);
    }

    /**
     * Create test cryptocurrency data
     */
    private function createTestCryptocurrencies(): void
    {
        $cryptocurrencies = [
            [
                'coin_id' => 'bitcoin',
                'symbol' => 'BTC',
                'name' => 'Bitcoin',
                'image' => 'https://example.com/bitcoin.png',
                'current_price' => 50000.12345678,
                'market_cap' => 1000000000000, // 1T
                'market_cap_rank' => 1,
                'fully_diluted_valuation' => 1050000000000,
                'total_volume' => 25000000000,
                'high_24h' => 51000.00,
                'low_24h' => 49000.00,
                'price_change_24h' => 1000.12345678,
                'price_change_percentage_24h' => 2.04,
                'market_cap_change_24h' => 20000000000,
                'market_cap_change_percentage_24h' => 2.04,
                'circulating_supply' => 19000000.00,
                'total_supply' => 19000000.00,
                'max_supply' => 21000000.00,
                'ath' => 69000.00,
                'ath_change_percentage' => -27.54,
                'ath_date' => now()->subMonths(6),
                'atl' => 0.05,
                'atl_change_percentage' => 9999.99,
                'atl_date' => now()->subYears(10),
                'last_updated' => now(),
            ],
            [
                'coin_id' => 'ethereum',
                'symbol' => 'ETH',
                'name' => 'Ethereum',
                'image' => 'https://example.com/ethereum.png',
                'current_price' => 3000.00,
                'market_cap' => 360000000000, // 360B
                'market_cap_rank' => 2,
                'fully_diluted_valuation' => 360000000000,
                'total_volume' => 15000000000,
                'high_24h' => 3100.00,
                'low_24h' => 2900.00,
                'price_change_24h' => 50.00,
                'price_change_percentage_24h' => 1.69,
                'market_cap_change_24h' => 6000000000,
                'market_cap_change_percentage_24h' => 1.69,
                'circulating_supply' => 120000000.00,
                'total_supply' => 120000000.00,
                'max_supply' => null,
                'ath' => 4800.00,
                'ath_change_percentage' => -37.5,
                'ath_date' => now()->subMonths(8),
                'atl' => 0.42,
                'atl_change_percentage' => 7141.85,
                'atl_date' => now()->subYears(8),
                'last_updated' => now(),
            ],
            [
                'coin_id' => 'tether',
                'symbol' => 'USDT',
                'name' => 'Tether',
                'image' => 'https://example.com/tether.png',
                'current_price' => 1.00,
                'market_cap' => 80000000000, // 80B
                'market_cap_rank' => 3,
                'fully_diluted_valuation' => 80000000000,
                'total_volume' => 40000000000,
                'high_24h' => 1.002,
                'low_24h' => 0.998,
                'price_change_24h' => 0.001,
                'price_change_percentage_24h' => 0.1,
                'market_cap_change_24h' => 80000000,
                'market_cap_change_percentage_24h' => 0.1,
                'circulating_supply' => 80000000000.00,
                'total_supply' => 80000000000.00,
                'max_supply' => null,
                'ath' => 1.32,
                'ath_change_percentage' => -24.24,
                'ath_date' => now()->subYears(3),
                'atl' => 0.57,
                'atl_change_percentage' => 75.44,
                'atl_date' => now()->subYears(6),
                'last_updated' => now(),
            ]
        ];

        foreach ($cryptocurrencies as $crypto) {
            Cryptocurrency::create($crypto);
        }
    }
}
