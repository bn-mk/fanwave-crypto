<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\CryptocurrencyServiceInterface;
use App\Http\Resources\CryptocurrencyResource;
use App\Services\CryptocurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CryptocurrencyController extends Controller
{
    protected $cryptocurrencyService;

    public function __construct(CryptocurrencyServiceInterface $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
    }

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
            
            $cryptocurrencies = $this->cryptocurrencyService->getTopByMarketCap($limit);

            return response()->json([
                'success' => true,
                'data' => CryptocurrencyResource::collection($cryptocurrencies),
                'meta' => [
                    'count' => $cryptocurrencies->count(),
                    'limit' => $limit,
                    'last_updated' => $cryptocurrencies->first()->last_updated ?? null
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
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:50',
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            $query = $request->get('query');
            $limit = $request->get('limit', 20);
            
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
