# Cryptocurrency Data Fetching System

This system automatically fetches cryptocurrency data from the CoinGecko API every 10 minutes and stores it in your database.

## Components Created

### 1. Database Migration
- **File**: `database/migrations/2025_07_04_110025_create_cryptocurrencies_table.php`
- **Purpose**: Creates the `cryptocurrencies` table with all necessary fields for storing CoinGecko data
- **Fields**: coin_id, symbol, name, image, current_price, market_cap, market_cap_rank, and many more

### 2. Cryptocurrency Model
- **File**: `app/Models/Cryptocurrency.php`
- **Features**:
  - Proper field casting for decimals and dates
  - `topByMarketCap()` scope for getting top cryptocurrencies
  - Helper methods for formatted price and market cap display
  - Mass assignable fields configuration

### 3. FetchCryptocurrencyData Job
- **File**: `app/Jobs/FetchCryptocurrencyData.php`
- **Features**:
  - Fetches top 100 cryptocurrencies from CoinGecko API
  - Handles API errors with retry logic (3 attempts)
  - Stores/updates data using `updateOrCreate()` to avoid duplicates
  - Comprehensive logging for debugging
  - Uses your configured `COIN_GECKO_ROOT_URL` and `COIN_GECKO_API_KEY`

### 4. Console Command
- **File**: `app/Console/Commands/FetchCryptocurrencyCommand.php`
- **Usage**:
  ```bash
  # Queue the job (background processing)
  sail artisan crypto:fetch
  
  # Run synchronously (immediate execution)
  sail artisan crypto:fetch --sync
  ```

### 5. Scheduled Task
- **File**: `routes/console.php`
- **Schedule**: Every 10 minutes
- **Features**: Automatic logging of success/failure

### 6. Service Configuration
- **File**: `config/services.php`
- **Configuration**: CoinGecko API settings

## Environment Configuration

Make sure your `.env` file contains:
```env
COIN_GECKO_API_KEY="your-api-key-here"
COIN_GECKO_ROOT_URL="https://api.coingecko.com/api/v3/"
```

## How to Use

### Manual Execution
```bash
# Run immediately (synchronous)
sail artisan crypto:fetch --sync

# Queue for background processing
sail artisan crypto:fetch
```

### Automatic Scheduling
The job runs automatically every 10 minutes. To enable scheduling:

1. **For production**: Add to your crontab:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **For development**: Run the scheduler manually:
   ```bash
   sail artisan schedule:work
   ```

### Queue Processing
If using queued jobs, make sure a queue worker is running:
```bash
sail artisan queue:work
```

## Checking the Data

View scheduled tasks:
```bash
sail artisan schedule:list
```

Check stored data:
```bash
sail artisan tinker
>>> App\Models\Cryptocurrency::count()
>>> App\Models\Cryptocurrency::topByMarketCap(10)->get(['name', 'symbol', 'current_price'])
```

## Features

### Data Fields Stored
- Basic info: name, symbol, image
- Pricing: current_price, high_24h, low_24h
- Market data: market_cap, market_cap_rank, total_volume
- Changes: price_change_24h, price_change_percentage_24h
- Supply: circulating_supply, total_supply, max_supply
- All-time records: ath (all-time high), atl (all-time low)
- Timestamps: ath_date, atl_date, last_updated

### Error Handling
- API request retries (3 attempts with 2-second delay)
- Comprehensive logging
- Graceful failure handling
- Individual cryptocurrency error isolation

### Performance Features
- Database indexes on frequently queried fields
- Batch processing of all cryptocurrencies
- Memory-efficient data processing
- Configurable timeout settings

## API Rate Limits

The job respects CoinGecko's rate limits:
- Free tier: 30 requests/minute
- Pro tier: Higher limits with API key
- Built-in retry logic for temporary failures

## Monitoring

Check logs for job execution:
```bash
sail artisan tail
```

Monitor failed jobs:
```bash
sail artisan queue:failed
```

## Testing

The system was successfully tested and is currently storing cryptocurrency data from the CoinGecko API.
