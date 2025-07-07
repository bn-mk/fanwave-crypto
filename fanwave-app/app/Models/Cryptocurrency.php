<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cryptocurrency extends Model
{
    protected $fillable = [
        'coin_id',
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
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'market_cap' => 'integer',
        'market_cap_rank' => 'integer',
        'fully_diluted_valuation' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'high_24h' => 'decimal:8',
        'low_24h' => 'decimal:8',
        'price_change_24h' => 'decimal:8',
        'price_change_percentage_24h' => 'decimal:4',
        'market_cap_change_24h' => 'decimal:2',
        'market_cap_change_percentage_24h' => 'decimal:4',
        'circulating_supply' => 'decimal:2',
        'total_supply' => 'decimal:2',
        'max_supply' => 'decimal:2',
        'ath' => 'decimal:8',
        'ath_change_percentage' => 'decimal:4',
        'ath_date' => 'datetime',
        'atl' => 'decimal:8',
        'atl_change_percentage' => 'decimal:4',
        'atl_date' => 'datetime',
        'last_updated' => 'datetime',
    ];

    /**
     * Scope to get top cryptocurrencies by market cap rank
     */
    public function scopeTopByMarketCap(\Illuminate\Database\Eloquent\Builder $query, int $limit = 10): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('market_cap_rank')
                     ->orderBy('market_cap_rank')
                     ->limit($limit);
    }

    /**
     * Get formatted price with currency symbol
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->current_price, 2);
    }

    /**
     * Get formatted market cap
     */
    public function getFormattedMarketCapAttribute(): string
    {
        $marketCap = $this->market_cap ?? 1e12; // Default to 1T for null values
        
        if ($marketCap >= 1e12) {
            return '$' . number_format($marketCap / 1e12, 2) . 'T';
        } elseif ($marketCap >= 1e9) {
            return '$' . number_format($marketCap / 1e9, 2) . 'B';
        } elseif ($marketCap >= 1e6) {
            return '$' . number_format($marketCap / 1e6, 2) . 'M';
        }
        return '$' . number_format($marketCap, 2);
    }
}
