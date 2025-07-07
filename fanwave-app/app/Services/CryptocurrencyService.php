<?php

namespace App\Services;

use App\Interfaces\CryptocurrencyServiceInterface;
use App\Models\Cryptocurrency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class CryptocurrencyService implements CryptocurrencyServiceInterface
{
    /**
     * Get top cryptocurrencies by market capitalization.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopByMarketCap(int $limit = 10): Collection
    {
        $cacheKey = "cryptocurrencies_top_{$limit}";
        $cacheDuration = 60; // in seconds

        return Cache::remember($cacheKey, $cacheDuration, function () use ($limit) {
            return Cryptocurrency::topByMarketCap($limit)
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
        });
    }

    /**
     * Search for cryptocurrencies by name, symbol, or coin_id.
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function search(string $query, int $limit = 20): Collection
    {
        return Cryptocurrency::where('name', 'LIKE', "%{$query}%")
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
    }
    
    /**
     * Get statistics about the cryptocurrency data.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $cacheKey = 'cryptocurrency_statistics';
        $cacheDuration = 60 * 5; // 5 minutes

        return Cache::remember($cacheKey, $cacheDuration, function () {
            $totalCount = Cryptocurrency::count();
            $lastUpdated = Cryptocurrency::max('last_updated');
            $topMarketCap = Cryptocurrency::max('market_cap');
            $averagePrice = Cryptocurrency::avg('current_price');

            return [
                'total_cryptocurrencies' => $totalCount,
                'last_data_update' => $lastUpdated,
                'highest_market_cap' => $topMarketCap,
                'average_price' => round($averagePrice, 2),
                'data_freshness' => $lastUpdated ? now()->diffForHumans($lastUpdated) : null
            ];
        });
    }
    
    /**
     * Get a single cryptocurrency by its coin ID.
     *
     * @param string $coinId
     * @return Cryptocurrency|null
     */
    public function getById(string $coinId): ?Cryptocurrency
    {
        return Cryptocurrency::where('coin_id', $coinId)->first();
    }

}
