<template>
  <div class="crypto-page">
    <div class="container">
      <header class="page-header">
        <NuxtLink to="/" class="back-btn">← Back to Home</NuxtLink>
        <h1 class="page-title">Top 10 Cryptocurrencies</h1>
        <p class="page-subtitle">Track the biggest digital currencies by market cap</p>
        <div v-if="lastUpdated" class="last-updated">
          Last updated: {{ formatDate(lastUpdated) }}
        </div>
      </header>

      <!-- Loading State -->
      <div v-if="pending" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">Loading cryptocurrency data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <button @click="refresh()" class="retry-button">Try Again</button>
      </div>

      <!-- Success State -->
      <div v-else-if="cryptoData" class="crypto-grid fade-in">
        <div 
          v-for="crypto in cryptoData" 
          :key="crypto.id"
          class="crypto-card-link"
          @click="handleCardClick(crypto.id)"
        >
          <div class="crypto-card">
          <div class="crypto-header">
            <div class="crypto-info">
              <div class="crypto-icon">
                <img 
                  v-if="crypto.image" 
                  :src="crypto.image" 
                  :alt="crypto.name"
                  class="crypto-image"
                  @error="handleImageError"
                />
                <span v-else class="crypto-symbol-fallback">{{ crypto.symbol }}</span>
              </div>
              <div class="crypto-details">
                <h3 class="crypto-name">{{ crypto.name }}</h3>
                <span class="crypto-symbol">{{ crypto.symbol }}</span>
              </div>
            </div>
            <div class="crypto-rank">#{{ crypto.market_cap_rank }}</div>
          </div>
          
          <div class="crypto-stats">
            <div class="stat-item">
              <span class="stat-label">Price</span>
              <span class="stat-value price">{{ crypto.formatted_price }}</span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">24h Change</span>
              <span 
                :class="['stat-value', 'change', crypto.price_change_percentage_24h >= 0 ? 'positive' : 'negative']"
              >
                {{ formatPercentage(crypto.price_change_percentage_24h) }}%
              </span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">Market Cap</span>
              <span class="stat-value">{{ crypto.formatted_market_cap }}</span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">Volume (24h)</span>
              <span class="stat-value">{{ formatVolume(crypto.total_volume) }}</span>
            </div>
          </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="error-container">
        <p class="error-message">No cryptocurrency data available</p>
        <button @click="refresh()" class="retry-button">Reload</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Use the crypto composable
const { getTopCryptocurrencies } = useCrypto()

// Define the interface locally
interface CryptoCurrency {
  id: string
  symbol: string
  name: string
  image: string
  current_price: number
  market_cap: number
  market_cap_rank: number
  price_change_24h: number
  price_change_percentage_24h: number
  total_volume: number
  circulating_supply: number
  formatted_price: string
  formatted_market_cap: string
  last_updated: string
}

// Reactive state
const cryptoData = ref<CryptoCurrency[]>([])
const pending = ref(true)
const error = ref<string | null>(null)
const lastUpdated = ref<string | null>(null)

// Fetch cryptocurrency data
const fetchData = async () => {
  try {
    pending.value = true
    error.value = null
    
    const data = await getTopCryptocurrencies(10)
    cryptoData.value = data
    
    // Set last updated from the first item (they should all have similar timestamps)
    if (data.length > 0) {
      lastUpdated.value = data[0].last_updated
    }
  } catch (err: any) {
    error.value = err.message || 'Failed to load cryptocurrency data'
    console.error('Error fetching crypto data:', err)
  } finally {
    pending.value = false
  }
}

// Refresh function for retry button
const refresh = () => {
  fetchData()
}

// Helper functions for formatting
const formatPercentage = (value: number): string => {
  if (value === null || value === undefined) return '0.00'
  const sign = value >= 0 ? '+' : ''
  return `${sign}${value.toFixed(2)}`
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

const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
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
  // The v-else will show the fallback symbol
}

const handleCardClick = (cryptoId: string) => {
  console.log('Card clicked for crypto:', cryptoId)
  console.log('Navigating to:', `/crypto/${cryptoId}`)
  
  // Try programmatic navigation as fallback
  try {
    navigateTo(`/crypto/${cryptoId}`)
  } catch (error) {
    console.error('Navigation error:', error)
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.crypto-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.back-btn {
  display: inline-block;
  margin-bottom: 1rem;
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

.page-title {
  font-size: 3rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  font-size: 1.125rem;
  color: #718096;
  margin-bottom: 2rem;
}

.crypto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.crypto-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
  transition: all 0.3s ease;
  cursor: pointer;
}

.crypto-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
}

.crypto-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.crypto-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.crypto-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.crypto-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 0.875rem;
  overflow: hidden;
}

.crypto-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.crypto-symbol-fallback {
  font-size: 0.75rem;
  font-weight: 700;
}

.last-updated {
  font-size: 0.875rem;
  color: #718096;
  margin-top: 0.5rem;
  font-style: italic;
}

.crypto-details {
  display: flex;
  flex-direction: column;
}

.crypto-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.crypto-symbol {
  font-size: 0.875rem;
  color: #718096;
  font-weight: 500;
}

.crypto-rank {
  background-color: #edf2f7;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
}

.crypto-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
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
}

.stat-value.price {
  font-size: 1.125rem;
  color: #667eea;
}

.stat-value.change.positive {
  color: #38a169;
}

.stat-value.change.negative {
  color: #e53e3e;
}

/* Responsive design */
@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .crypto-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .crypto-card {
    padding: 1rem;
  }
  
  .crypto-stats {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.75rem;
  }
  
  .crypto-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .crypto-info {
    flex-direction: column;
    text-align: center;
  }
}
</style>
