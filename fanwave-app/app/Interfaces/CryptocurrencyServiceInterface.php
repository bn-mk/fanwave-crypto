<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Cryptocurrency;

interface CryptocurrencyServiceInterface
{
    public function getTopByMarketCap(int $limit = 10): Collection;

    public function search(string $query, int $limit = 20): Collection;

    public function getStatistics(): array;

    public function getById(string $coinId): ?Cryptocurrency;
}

