<template>
  <div class="crypto-detail-page">
    <div class="container">
      <!-- Loading State -->
      <div v-if="pending" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">Loading cryptocurrency details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <div class="error-actions">
          <button @click="refresh()" class="retry-button">Try Again</button>
          <NuxtLink to="/crypto" class="back-button">Back to List</NuxtLink>
        </div>
      </div>

      <!-- Success State -->
      <div v-else-if="cryptoDetail" class="crypto-detail fade-in">
        <!-- Header -->
        <header class="crypto-header">
          <NuxtLink to="/crypto" class="back-btn">← Back to Cryptocurrencies</NuxtLink>
          
          <div class="crypto-title-section">
            <div class="crypto-main-info">
              <div class="crypto-icon-large">
                <img 
                  v-if="cryptoDetail.image" 
                  :src="cryptoDetail.image" 
                  :alt="cryptoDetail.name"
                  class="crypto-image-large"
                  @error="handleImageError"
                />
                <span v-else class="crypto-symbol-large">{{ cryptoDetail.symbol }}</span>
              </div>
              <div class="crypto-title-info">
                <h1 class="crypto-name">{{ cryptoDetail.name }}</h1>
                <span class="crypto-symbol">{{ cryptoDetail.symbol }}</span>
                <div class="crypto-rank">#{{ cryptoDetail.market_cap_rank }} by Market Cap</div>
              </div>
            </div>
            
            <div class="crypto-price-section">
              <div class="current-price">{{ cryptoDetail.formatted_price }}</div>
              <div 
                :class="['price-change', cryptoDetail.price_change_percentage_24h >= 0 ? 'positive' : 'negative']"
              >
                {{ formatPercentage(cryptoDetail.price_change_percentage_24h) }}% (24h)
              </div>
            </div>
          </div>
        </header>

        <!-- Main Stats Grid -->
        <div class="stats-grid">
          <!-- Market Data -->
          <div class="stats-card">
            <h3 class="stats-title">Market Data</h3>
            <div class="stats-list">
              <div class="stat-row">
                <span class="stat-label">Market Cap</span>
                <span class="stat-value">{{ cryptoDetail.formatted_market_cap }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">24h Volume</span>
                <span class="stat-value">{{ formatVolume(cryptoDetail.total_volume) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">Fully Diluted Valuation</span>
                <span class="stat-value">{{ formatVolume(cryptoDetail.fully_diluted_valuation) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">Circulating Supply</span>
                <span class="stat-value">{{ formatSupply(cryptoDetail.circulating_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
            </div>
          </div>

          <!-- Price Data -->
          <div class="stats-card">
            <h3 class="stats-title">Price Data</h3>
            <div class="stats-list">
              <div class="stat-row">
                <span class="stat-label">24h High</span>
                <span class="stat-value">{{ formatPrice(cryptoDetail.high_24h) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">24h Low</span>
                <span class="stat-value">{{ formatPrice(cryptoDetail.low_24h) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">24h Change</span>
                <span 
                  :class="['stat-value', cryptoDetail.price_change_24h >= 0 ? 'positive' : 'negative']"
                >
                  {{ formatPrice(cryptoDetail.price_change_24h) }}
                </span>
              </div>
              <div class="stat-row">
                <span class="stat-label">Market Cap Change (24h)</span>
                <span 
                  :class="['stat-value', cryptoDetail.market_cap_change_percentage_24h >= 0 ? 'positive' : 'negative']"
                >
                  {{ formatPercentage(cryptoDetail.market_cap_change_percentage_24h) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Supply Information -->
          <div class="stats-card">
            <h3 class="stats-title">Supply Information</h3>
            <div class="stats-list">
              <div class="stat-row">
                <span class="stat-label">Total Supply</span>
                <span class="stat-value">{{ formatSupply(cryptoDetail.total_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">Max Supply</span>
                <span class="stat-value">
                  {{ cryptoDetail.max_supply ? formatSupply(cryptoDetail.max_supply) + ' ' + cryptoDetail.symbol : 'N/A' }}
                </span>
              </div>
              <div class="stat-row">
                <span class="stat-label">Circulating Supply</span>
                <span class="stat-value">{{ formatSupply(cryptoDetail.circulating_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
            </div>
          </div>

          <!-- All-Time Records -->
          <div class="stats-card">
            <h3 class="stats-title">All-Time Records</h3>
            <div class="stats-list">
              <div class="stat-row">
                <span class="stat-label">All-Time High</span>
                <span class="stat-value">{{ formatPrice(cryptoDetail.ath) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">ATH Change</span>
                <span class="stat-value negative">{{ formatPercentage(cryptoDetail.ath_change_percentage) }}%</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">ATH Date</span>
                <span class="stat-value">{{ formatDate(cryptoDetail.ath_date) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">All-Time Low</span>
                <span class="stat-value">{{ formatPrice(cryptoDetail.atl) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="last-updated-section">
          <p class="last-updated">Last updated: {{ formatDate(cryptoDetail.last_updated) }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="error-container">
        <p class="error-message">Cryptocurrency not found</p>
        <NuxtLink to="/crypto" class="back-button">Back to List</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Get the route parameter
const route = useRoute()
const coinId = computed(() => route.params.id as string)

// Define the detailed interface
interface CryptocurrencyDetail {
  id: string
  symbol: string
  name: string
  image: string
  current_price: number
  market_cap: number
  market_cap_rank: number
  fully_diluted_valuation: number
  total_volume: number
  high_24h: number
  low_24h: number
  price_change_24h: number
  price_change_percentage_24h: number
  market_cap_change_24h: number
  market_cap_change_percentage_24h: number
  circulating_supply: number
  total_supply: number
  max_supply: number
  ath: number
  ath_change_percentage: number
  ath_date: string
  atl: number
  atl_change_percentage: number
  atl_date: string
  last_updated: string
  formatted_price: string
  formatted_market_cap: string
}

// Use useFetch for reactive data fetching
const { data: apiResponse, pending, error: fetchError, refresh: refreshData } = await useFetch(`/api/crypto/${coinId.value}`, {
  key: `crypto-${coinId.value}`,
  watch: [coinId]
})

// Extract the crypto detail from the API response
const cryptoDetail = computed(() => {
  if (apiResponse.value?.success) {
    return apiResponse.value.data as CryptocurrencyDetail
  }
  return null
})

// Extract error message
const error = computed(() => {
  if (fetchError.value) {
    return fetchError.value.message || 'Failed to load cryptocurrency details'
  }
  if (apiResponse.value && !apiResponse.value.success) {
    return apiResponse.value.message || 'Failed to load cryptocurrency details'
  }
  return null
})

// Refresh function for retry button
const refresh = () => {
  refreshData()
}

// Helper functions for formatting
const formatPercentage = (value: number): string => {
  if (value === null || value === undefined) return '0.00'
  const sign = value >= 0 ? '+' : ''
  return `${sign}${value.toFixed(2)}`
}

const formatPrice = (value: number): string => {
  if (!value) return 'N/A'
  return '$' + value.toLocaleString('en-US', {
    minimumFractionDigits: value < 1 ? 6 : 2,
    maximumFractionDigits: value < 1 ? 6 : 2
  })
}

const formatVolume = (value: number): string => {
  if (!value) return 'N/A'
  
  if (value >= 1e12) {
    return '$' + (value / 1e12).toFixed(2) + 'T'
  } else if (value >= 1e9) {
    return '$' + (value / 1e9).toFixed(2) + 'B'
  } else if (value >= 1e6) {
    return '$' + (value / 1e6).toFixed(2) + 'M'
  }
  
  return '$' + value.toLocaleString()
}

const formatSupply = (value: number): string => {
  if (!value) return 'N/A'
  
  if (value >= 1e12) {
    return (value / 1e12).toFixed(2) + 'T'
  } else if (value >= 1e9) {
    return (value / 1e9).toFixed(2) + 'B'
  } else if (value >= 1e6) {
    return (value / 1e6).toFixed(2) + 'M'
  }
  
  return value.toLocaleString()
}

const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Unknown'
  }
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}

// Set page meta
useHead({
  title: () => cryptoDetail.value ? `${cryptoDetail.value.name} (${cryptoDetail.value.symbol}) - Fanwave Digital Crypto` : 'Loading...',
  meta: [
    { 
      name: 'description', 
      content: () => cryptoDetail.value ? `View detailed information about ${cryptoDetail.value.name} including price, market cap, and trading data.` : 'Loading cryptocurrency details...'
    }
  ]
})

// useFetch handles route changes automatically through the watch option
</script>

<style scoped>
.crypto-detail-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.crypto-header {
  margin-bottom: 3rem;
}

.back-btn {
  display: inline-block;
  margin-bottom: 2rem;
  padding: 0.5rem 1rem;
  background-color: #667eea;
  color: white;
  text-decoration: none;
  border-radius: 25px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.back-btn:hover {
  background-color: #5a67d8;
  transform: translateY(-2px);
}

.crypto-title-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.crypto-main-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.crypto-icon-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.crypto-image-large {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.crypto-symbol-large {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.crypto-name {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 0.5rem 0;
}

.crypto-symbol {
  font-size: 1.25rem;
  color: #718096;
  font-weight: 600;
}

.crypto-rank {
  font-size: 0.875rem;
  color: #667eea;
  margin-top: 0.5rem;
  font-weight: 500;
}

.crypto-price-section {
  text-align: right;
}

.current-price {
  font-size: 3rem;
  font-weight: 700;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.price-change {
  font-size: 1.25rem;
  font-weight: 600;
}

.price-change.positive {
  color: #38a169;
}

.price-change.negative {
  color: #e53e3e;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.stats-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.stats-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e2e8f0;
}

.stats-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
  font-weight: 500;
}

.stat-value {
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
  text-align: right;
}

.stat-value.positive {
  color: #38a169;
}

.stat-value.negative {
  color: #e53e3e;
}

.last-updated-section {
  text-align: center;
  margin-top: 2rem;
}

.last-updated {
  font-size: 0.875rem;
  color: #718096;
  font-style: italic;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  align-items: center;
}

.back-button {
  padding: 0.5rem 1rem;
  background-color: #718096;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.back-button:hover {
  background-color: #4a5568;
  transform: translateY(-2px);
}

/* Responsive design */
@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }
  
  .crypto-title-section {
    flex-direction: column;
    gap: 2rem;
    text-align: center;
  }
  
  .crypto-main-info {
    flex-direction: column;
    text-align: center;
  }
  
  .crypto-name {
    font-size: 2rem;
  }
  
  .current-price {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .stats-card {
    padding: 1.5rem;
  }
  
  .stat-row {
    flex-direction: column;
    gap: 0.25rem;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .crypto-name {
    font-size: 1.75rem;
  }
  
  .current-price {
    font-size: 1.75rem;
  }
  
  .crypto-icon-large {
    width: 60px;
    height: 60px;
  }
}
</style>
