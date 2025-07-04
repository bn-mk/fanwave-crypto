<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('coin_id')->unique(); // CoinGecko ID
            $table->string('symbol');
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('current_price', 20, 8)->nullable();
            $table->bigInteger('market_cap')->nullable();
            $table->integer('market_cap_rank')->nullable();
            $table->decimal('fully_diluted_valuation', 20, 2)->nullable();
            $table->decimal('total_volume', 20, 2)->nullable();
            $table->decimal('high_24h', 20, 8)->nullable();
            $table->decimal('low_24h', 20, 8)->nullable();
            $table->decimal('price_change_24h', 20, 8)->nullable();
            $table->decimal('price_change_percentage_24h', 8, 4)->nullable();
            $table->decimal('market_cap_change_24h', 20, 2)->nullable();
            $table->decimal('market_cap_change_percentage_24h', 8, 4)->nullable();
            $table->decimal('circulating_supply', 20, 2)->nullable();
            $table->decimal('total_supply', 20, 2)->nullable();
            $table->decimal('max_supply', 20, 2)->nullable();
            $table->decimal('ath', 20, 8)->nullable(); // All time high
            $table->decimal('ath_change_percentage', 8, 4)->nullable();
            $table->timestamp('ath_date')->nullable();
            $table->decimal('atl', 20, 8)->nullable(); // All time low
            $table->decimal('atl_change_percentage', 8, 4)->nullable();
            $table->timestamp('atl_date')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('market_cap_rank');
            $table->index('symbol');
            $table->index('last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};
