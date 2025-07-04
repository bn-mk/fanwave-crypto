# Cryptocurrency API Documentation

This API provides access to real-time cryptocurrency data fetched from CoinGecko and stored in your database.

## Base URL
```
http://your-domain.com/api
```

## Endpoints

### 1. Get Top Cryptocurrencies by Market Cap

**GET** `/crypto/top` or `/cryptocurrencies/top`

Returns the top cryptocurrencies sorted by market cap.

**Parameters:**
- `limit` (optional): Number of cryptocurrencies to return (1-100, default: 10)

**Example Request:**
```bash
curl "http://localhost/api/crypto/top?limit=10"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "bitcoin",
      "symbol": "BTC",
      "name": "Bitcoin",
      "image": "https://coin-images.coingecko.com/coins/images/1/large/bitcoin.png",
      "current_price": 43250.50,
      "market_cap": 847200000000,
      "market_cap_rank": 1,
      "price_change_24h": 1250.30,
      "price_change_percentage_24h": 2.95,
      "total_volume": 25300000000,
      "circulating_supply": 19500000,
      "formatted_price": "$43,250.50",
      "formatted_market_cap": "$847.20B",
      "last_updated": "2025-07-04T11:08:59.000000Z"
    }
  ],
  "meta": {
    "count": 10,
    "limit": 10,
    "endpoint": "top",
    "last_updated": "2025-07-04T11:08:59.000000Z"
  }
}
```

### 2. Get All Cryptocurrencies (Paginated)

**GET** `/cryptocurrencies`

Returns a paginated list of cryptocurrencies.

**Parameters:**
- `limit` (optional): Number of cryptocurrencies to return (1-100, default: 10)

**Example Request:**
```bash
curl "http://localhost/api/cryptocurrencies?limit=20"
```

### 3. Get Specific Cryptocurrency

**GET** `/crypto/{coinId}` or `/cryptocurrencies/{coinId}`

Returns detailed information about a specific cryptocurrency.

**Parameters:**
- `coinId`: The CoinGecko ID of the cryptocurrency (e.g., "bitcoin", "ethereum")

**Example Request:**
```bash
curl "http://localhost/api/crypto/bitcoin"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": "bitcoin",
    "symbol": "BTC",
    "name": "Bitcoin",
    "image": "https://coin-images.coingecko.com/coins/images/1/large/bitcoin.png",
    "current_price": 43250.50,
    "market_cap": 847200000000,
    "market_cap_rank": 1,
    "fully_diluted_valuation": 910000000000,
    "total_volume": 25300000000,
    "high_24h": 43800.00,
    "low_24h": 42100.00,
    "price_change_24h": 1250.30,
    "price_change_percentage_24h": 2.95,
    "market_cap_change_24h": 25000000000,
    "market_cap_change_percentage_24h": 3.05,
    "circulating_supply": 19500000,
    "total_supply": 19500000,
    "max_supply": 21000000,
    "ath": 69000.00,
    "ath_change_percentage": -37.32,
    "ath_date": "2021-11-10T14:24:11.849Z",
    "atl": 67.81,
    "atl_change_percentage": 63700.45,
    "atl_date": "2013-07-06T00:00:00.000Z",
    "last_updated": "2025-07-04T11:08:59.000000Z",
    "formatted_price": "$43,250.50",
    "formatted_market_cap": "$847.20B"
  }
}
```

### 4. Search Cryptocurrencies

**GET** `/cryptocurrencies/search`

Search cryptocurrencies by name, symbol, or coin ID.

**Parameters:**
- `query` (required): Search term (1-50 characters)
- `limit` (optional): Number of results to return (1-50, default: 20)

**Example Request:**
```bash
curl "http://localhost/api/cryptocurrencies/search?query=bitcoin&limit=5"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "coin_id": "bitcoin",
      "symbol": "BTC",
      "name": "Bitcoin",
      "image": "https://coin-images.coingecko.com/coins/images/1/large/bitcoin.png",
      "current_price": 43250.50,
      "market_cap": 847200000000,
      "market_cap_rank": 1,
      "price_change_percentage_24h": 2.95
    }
  ],
  "meta": {
    "query": "bitcoin",
    "count": 1,
    "limit": 5
  }
}
```

### 5. Get Statistics

**GET** `/cryptocurrencies/stats`

Returns general statistics about the cryptocurrency data.

**Example Request:**
```bash
curl "http://localhost/api/cryptocurrencies/stats"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_cryptocurrencies": 80,
    "last_data_update": "2025-07-04 11:08:59",
    "highest_market_cap": 847200000000,
    "average_price": 4451.72,
    "data_freshness": "52 minutes after"
  }
}
```

## Response Format

All API responses follow this structure:

### Success Response
```json
{
  "success": true,
  "data": { /* response data */ },
  "meta": { /* additional metadata */ }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error (only in debug mode)"
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## HTTP Status Codes

- `200 OK`: Successful request
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation error
- `500 Internal Server Error`: Server error

## Data Fields

### Cryptocurrency Object Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` / `coin_id` | string | CoinGecko unique identifier |
| `symbol` | string | Currency symbol (e.g., "BTC") |
| `name` | string | Full name (e.g., "Bitcoin") |
| `image` | string | URL to currency logo |
| `current_price` | float | Current price in USD |
| `market_cap` | integer | Market capitalization |
| `market_cap_rank` | integer | Ranking by market cap |
| `price_change_24h` | float | 24h price change in USD |
| `price_change_percentage_24h` | float | 24h price change percentage |
| `total_volume` | float | 24h trading volume |
| `circulating_supply` | float | Circulating supply |
| `total_supply` | float | Total supply |
| `max_supply` | float | Maximum supply |
| `ath` | float | All-time high price |
| `atl` | float | All-time low price |
| `formatted_price` | string | Formatted price for display |
| `formatted_market_cap` | string | Formatted market cap for display |
| `last_updated` | string | ISO 8601 timestamp |

## Rate Limiting

Currently, there are no rate limits applied to the API endpoints. However, it's recommended to implement rate limiting in production environments.

## Data Updates

The cryptocurrency data is automatically updated every 10 minutes from the CoinGecko API. The `last_updated` field in the response indicates when the data was last refreshed.

## Examples

### Get Top 10 Cryptocurrencies
```bash
curl "http://localhost/api/crypto/top"
```

### Get Specific Cryptocurrency Details
```bash
curl "http://localhost/api/crypto/ethereum"
```

### Search for Cryptocurrencies
```bash
curl "http://localhost/api/cryptocurrencies/search?query=eth"
```

### Get API Statistics
```bash
curl "http://localhost/api/cryptocurrencies/stats"
```
