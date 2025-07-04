<?php

namespace App\Jobs;

use App\Models\Cryptocurrency;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchCryptocurrencyData implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting cryptocurrency data fetch from CoinGecko API');
            
            $baseUrl = config('services.coingecko.root_url', env('COIN_GECKO_ROOT_URL'));
            $apiKey = config('services.coingecko.api_key', env('COIN_GECKO_API_KEY'));
            
            if (!$baseUrl) {
                throw new \Exception('CoinGecko API URL not configured');
            }
            
            // Build the request URL
            $url = rtrim($baseUrl, '/') . '/coins/markets';
            
            // Prepare request parameters
            $params = [
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => 100, // Fetch top 100 cryptocurrencies
                'page' => 1,
                'sparkline' => false,
                'price_change_percentage' => '24h'
            ];
            
            // Add API key if available
            if ($apiKey) {
                $params['x_cg_demo_api_key'] = $apiKey;
            }
            
            // Make HTTP request with timeout
            $response = Http::timeout(30)
                ->retry(3, 2000) // Retry 3 times with 2 second delay
                ->get($url, $params);
            
            if (!$response->successful()) {
                throw new \Exception('CoinGecko API request failed: ' . $response->status() . ' - ' . $response->body());
            }
            
            $cryptocurrencies = $response->json();
            
            if (empty($cryptocurrencies)) {
                Log::warning('No cryptocurrency data received from CoinGecko API');
                return;
            }
            
            Log::info('Received ' . count($cryptocurrencies) . ' cryptocurrencies from CoinGecko API');
            
            // Process and store each cryptocurrency
            foreach ($cryptocurrencies as $crypto) {
                $this->storeCryptocurrency($crypto);
            }
            
            Log::info('Successfully updated cryptocurrency data');
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch cryptocurrency data: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
    
    /**
     * Store or update cryptocurrency data in database
     */
    private function storeCryptocurrency(array $cryptoData): void
    {
        try {
            $data = [
                'coin_id' => $cryptoData['id'],
                'symbol' => strtoupper($cryptoData['symbol']),
                'name' => $cryptoData['name'],
                'image' => $cryptoData['image'] ?? null,
                'current_price' => $cryptoData['current_price'],
                'market_cap' => $cryptoData['market_cap'],
                'market_cap_rank' => $cryptoData['market_cap_rank'],
                'fully_diluted_valuation' => $cryptoData['fully_diluted_valuation'],
                'total_volume' => $cryptoData['total_volume'],
                'high_24h' => $cryptoData['high_24h'],
                'low_24h' => $cryptoData['low_24h'],
                'price_change_24h' => $cryptoData['price_change_24h'],
                'price_change_percentage_24h' => $cryptoData['price_change_percentage_24h'],
                'market_cap_change_24h' => $cryptoData['market_cap_change_24h'],
                'market_cap_change_percentage_24h' => $cryptoData['market_cap_change_percentage_24h'],
                'circulating_supply' => $cryptoData['circulating_supply'],
                'total_supply' => $cryptoData['total_supply'],
                'max_supply' => $cryptoData['max_supply'],
                'ath' => $cryptoData['ath'],
                'ath_change_percentage' => $cryptoData['ath_change_percentage'],
                'ath_date' => isset($cryptoData['ath_date']) ? Carbon::parse($cryptoData['ath_date']) : null,
                'atl' => $cryptoData['atl'],
                'atl_change_percentage' => $cryptoData['atl_change_percentage'],
                'atl_date' => isset($cryptoData['atl_date']) ? Carbon::parse($cryptoData['atl_date']) : null,
                'last_updated' => isset($cryptoData['last_updated']) ? Carbon::parse($cryptoData['last_updated']) : now(),
            ];
            
            // Use updateOrCreate to insert new or update existing records
            Cryptocurrency::updateOrCreate(
                ['coin_id' => $cryptoData['id']],
                $data
            );
            
        } catch (\Exception $e) {
            Log::error('Failed to store cryptocurrency: ' . $cryptoData['id'] ?? 'unknown', [
                'error' => $e->getMessage(),
                'data' => $cryptoData
            ]);
            // Don't throw here, continue with other cryptocurrencies
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FetchCryptocurrencyData job failed permanently', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
