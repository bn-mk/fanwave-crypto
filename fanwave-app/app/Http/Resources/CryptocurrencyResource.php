<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CryptocurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(\Illuminate\Http\Request $request): array
    {
        return [
            'id' => $this->coin_id,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'image' => $this->image,
            'current_price' => (float) $this->current_price,
            'market_cap' => $this->market_cap,
            'market_cap_rank' => $this->market_cap_rank,
            'fully_diluted_valuation' => (float) $this->fully_diluted_valuation,
            'total_volume' => (float) $this->total_volume,
            'high_24h' => (float) $this->high_24h,
            'low_24h' => (float) $this->low_24h,
            'price_change_24h' => (float) $this->price_change_24h,
            'price_change_percentage_24h' => (float) $this->price_change_percentage_24h,
            'market_cap_change_24h' => (float) $this->market_cap_change_24h,
            'market_cap_change_percentage_24h' => (float) $this->market_cap_change_percentage_24h,
            'circulating_supply' => (float) $this->circulating_supply,
            'total_supply' => (float) $this->total_supply,
            'max_supply' => (float) $this->max_supply,
            'ath' => (float) $this->ath,
            'ath_change_percentage' => (float) $this->ath_change_percentage,
            'ath_date' => $this->ath_date,
            'atl' => (float) $this->atl,
            'atl_change_percentage' => (float) $this->atl_change_percentage,
            'atl_date' => $this->atl_date,
            'last_updated' => $this->last_updated,
            'formatted_price' => '$' . number_format($this->current_price, $this->current_price < 1 ? 6 : 2),
            'formatted_market_cap' => $this->formatMarketCap($this->market_cap),
        ];
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

