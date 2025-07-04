<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cryptocurrency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CryptocurrencyController extends Controller
{
    /**
     * Display a listing of cryptocurrencies.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $limit = min(max($limit, 1), 100); // Limit between 1 and 100
            
            $cryptocurrencies = Cryptocurrency::topByMarketCap($limit)
                ->select([
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
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cryptocurrencies,
                'meta' => [
                    'count' => $cryptocurrencies->count(),
                    'limit' => $limit,
                    'last_updated' => $cryptocurrencies->first()?->last_updated ?? null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cryptocurrencies',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get top cryptocurrencies by market cap.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function top(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'sometimes|integer|min:1|max:100'
            ]);

            $limit = $request->get('limit', 10);
            
            $cryptocurrencies = Cryptocurrency::topByMarketCap($limit)
                ->select([
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
                    'circulating_supply',
                    'last_updated'
                ])
                ->get()
                ->map(function ($crypto) {
                    return [
                        'id' => $crypto->coin_id,
                        'symbol' => $crypto->symbol,
                        'name' => $crypto->name,
                        'image' => $crypto->image,
                        'current_price' => (float) $crypto->current_price,
                        'market_cap' => $crypto->market_cap,
                        'market_cap_rank' => $crypto->market_cap_rank,
                        'price_change_24h' => (float) $crypto->price_change_24h,
                        'price_change_percentage_24h' => (float) $crypto->price_change_percentage_24h,
                        'total_volume' => (float) $crypto->total_volume,
                        'circulating_supply' => (float) $crypto->circulating_supply,
                        'formatted_price' => '$' . number_format($crypto->current_price, $crypto->current_price < 1 ? 6 : 2),
                        'formatted_market_cap' => $this->formatMarketCap($crypto->market_cap),
                        'last_updated' => $crypto->last_updated
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $cryptocurrencies,
                'meta' => [
                    'count' => $cryptocurrencies->count(),
                    'limit' => $limit,
                    'endpoint' => 'top',
                    'last_updated' => $cryptocurrencies->first()['last_updated'] ?? null
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top cryptocurrencies',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified cryptocurrency.
     * 
     * @param string $coinId
     * @return JsonResponse
     */
    public function show(string $coinId): JsonResponse
    {
        try {
            $cryptocurrency = Cryptocurrency::where('coin_id', $coinId)->first();
            
            if (!$cryptocurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cryptocurrency not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $cryptocurrency->coin_id,
                    'symbol' => $cryptocurrency->symbol,
                    'name' => $cryptocurrency->name,
                    'image' => $cryptocurrency->image,
                    'current_price' => (float) $cryptocurrency->current_price,
                    'market_cap' => $cryptocurrency->market_cap,
                    'market_cap_rank' => $cryptocurrency->market_cap_rank,
                    'fully_diluted_valuation' => (float) $cryptocurrency->fully_diluted_valuation,
                    'total_volume' => (float) $cryptocurrency->total_volume,
                    'high_24h' => (float) $cryptocurrency->high_24h,
                    'low_24h' => (float) $cryptocurrency->low_24h,
                    'price_change_24h' => (float) $cryptocurrency->price_change_24h,
                    'price_change_percentage_24h' => (float) $cryptocurrency->price_change_percentage_24h,
                    'market_cap_change_24h' => (float) $cryptocurrency->market_cap_change_24h,
                    'market_cap_change_percentage_24h' => (float) $cryptocurrency->market_cap_change_percentage_24h,
                    'circulating_supply' => (float) $cryptocurrency->circulating_supply,
                    'total_supply' => (float) $cryptocurrency->total_supply,
                    'max_supply' => (float) $cryptocurrency->max_supply,
                    'ath' => (float) $cryptocurrency->ath,
                    'ath_change_percentage' => (float) $cryptocurrency->ath_change_percentage,
                    'ath_date' => $cryptocurrency->ath_date,
                    'atl' => (float) $cryptocurrency->atl,
                    'atl_change_percentage' => (float) $cryptocurrency->atl_change_percentage,
                    'atl_date' => $cryptocurrency->atl_date,
                    'last_updated' => $cryptocurrency->last_updated,
                    'formatted_price' => '$' . number_format($cryptocurrency->current_price, $cryptocurrency->current_price < 1 ? 6 : 2),
                    'formatted_market_cap' => $this->formatMarketCap($cryptocurrency->market_cap),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cryptocurrency',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search cryptocurrencies by name or symbol.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:50',
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            $query = $request->get('query');
            $limit = $request->get('limit', 20);
            
            $cryptocurrencies = Cryptocurrency::where('name', 'LIKE', "%{$query}%")
                ->orWhere('symbol', 'LIKE', "%{$query}%")
                ->orWhere('coin_id', 'LIKE', "%{$query}%")
                ->orderBy('market_cap_rank', 'asc')
                ->limit($limit)
                ->select([
                    'coin_id',
                    'symbol',
                    'name',
                    'image',
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'price_change_percentage_24h'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cryptocurrencies,
                'meta' => [
                    'query' => $query,
                    'count' => $cryptocurrencies->count(),
                    'limit' => $limit
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search cryptocurrencies',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get statistics about the cryptocurrency data.
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $totalCount = Cryptocurrency::count();
            $lastUpdated = Cryptocurrency::max('last_updated');
            $topMarketCap = Cryptocurrency::max('market_cap');
            $averagePrice = Cryptocurrency::avg('current_price');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_cryptocurrencies' => $totalCount,
                    'last_data_update' => $lastUpdated,
                    'highest_market_cap' => $topMarketCap,
                    'average_price' => round($averagePrice, 2),
                    'data_freshness' => $lastUpdated ? now()->diffForHumans($lastUpdated) : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Format market cap for display.
     * 
     * @param int|null $marketCap
     * @return string
     */
    private function formatMarketCap(?int $marketCap): string
    {
        if (!$marketCap) {
            return 'N/A';
        }
        
        if ($marketCap >= 1e12) {
            return '$' . number_format($marketCap / 1e12, 2) . 'T';
        } elseif ($marketCap >= 1e9) {
            return '$' . number_format($marketCap / 1e9, 2) . 'B';
        } elseif ($marketCap >= 1e6) {
            return '$' . number_format($marketCap / 1e6, 2) . 'M';
        }
        
        return '$' . number_format($marketCap, 0);
    }
}
