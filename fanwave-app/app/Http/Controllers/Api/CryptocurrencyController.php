<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\CryptocurrencyServiceInterface;
use App\Http\Resources\CryptocurrencyResource;
use App\Http\Requests\Api\CryptocurrencyRequest;
use App\Services\CryptocurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CryptocurrencyController extends Controller
{
    protected $cryptocurrencyService;

    public function __construct(CryptocurrencyServiceInterface $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
    }


    /**
     * Get top cryptocurrencies by market cap.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function top(CryptocurrencyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $limit = $validated['limit'] ?? 10;
        
        $cryptocurrencies = $this->cryptocurrencyService->getTopByMarketCap($limit);

        return response()->json([
            'success' => true,
            'data' => CryptocurrencyResource::collection($cryptocurrencies),
            'meta' => [
                'count' => $cryptocurrencies->count(),
                'limit' => $limit,
                'endpoint' => 'top',
                'last_updated' => $cryptocurrencies->first()->last_updated ?? null
            ]
        ]);
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
            $cryptocurrency = $this->cryptocurrencyService->getById($coinId);
            
            if (!$cryptocurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cryptocurrency not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new CryptocurrencyResource($cryptocurrency)
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
    public function search(CryptocurrencyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $query = $validated['query'];
        $limit = $validated['limit'] ?? 20;
        
        $cryptocurrencies = $this->cryptocurrencyService->search($query, $limit);

        return response()->json([
            'success' => true,
            'data' => CryptocurrencyResource::collection($cryptocurrencies),
            'meta' => [
                'query' => $query,
                'count' => $cryptocurrencies->count(),
                'limit' => $limit
            ]
        ]);
    }

    /**
     * Get statistics about the cryptocurrency data.
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->cryptocurrencyService->getStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
