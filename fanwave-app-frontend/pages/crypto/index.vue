<template>
  <div class="crypto-page">
    <div class="container">
      <header class="page-header">
        <NuxtLink to="/" class="back-btn">← Back to Home</NuxtLink>
        <h1 class="page-title">{{ isSearching ? 'Search Results' : 'Top 10 Cryptocurrencies' }}</h1>
        <p class="page-subtitle">{{ isSearching ? `Search results for "${searchQuery}"` : 'Track the biggest digital currencies by market cap' }}</p>
        
        <!-- Search Bar -->
        <div class="search-container">
          <div class="search-box">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search cryptocurrencies by name or symbol..."
              class="search-input"
              @input="handleSearchInput"
              @keyup.enter="performSearch"
            />
            <button 
              @click="() => { console.log('Search button clicked'); performSearch(); }" 
              class="search-button"
              :disabled="!searchQuery.trim()"
            >
              🔍
            </button>
            <button 
              v-if="isSearching"
              @click="clearSearch" 
              class="clear-button"
            >
              ✕
            </button>
          </div>
        </div>
        
        <div v-if="lastUpdated && !isSearching" class="last-updated">
          Last updated: {{ formatDate(lastUpdated) }}
        </div>
      </header>

      <!-- Loading State -->
      <div v-if="(pending && !isSearching) || (searchPending && isSearching)" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">{{ isSearching ? 'Searching cryptocurrencies...' : 'Loading cryptocurrency data...' }}</p>
      </div>

      <!-- Error State -->
      <div v-else-if="(error && !isSearching) || (searchError && isSearching)" class="error-container">
        <p class="error-message">{{ isSearching ? searchError : error }}</p>
        <button @click="refresh()" class="retry-button">Try Again</button>
      </div>

      <!-- Success State - Search Results or Top 10 -->
      <div v-else-if="(cryptoData && !isSearching) || (searchResults && isSearching)" class="crypto-grid fade-in">
        <div 
          v-for="crypto in (isSearching ? searchResults : cryptoData)" 
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
        <p class="error-message">{{ isSearching ? `No cryptocurrencies found for "${searchQuery}"` : 'No cryptocurrency data available' }}</p>
        <button @click="refresh()" class="retry-button">{{ isSearching ? 'Search Again' : 'Reload' }}</button>
        <button v-if="isSearching" @click="clearSearch()" class="clear-search-button">Show Top 10</button>
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

// Search state
const searchQuery = ref('')
const isSearching = ref(false)
const searchResults = ref<CryptoCurrency[]>([])
const searchPending = ref(false)
const searchError = ref<string | null>(null)
let searchTimeout: NodeJS.Timeout | null = null

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
  if (isSearching.value) {
    performSearch()
  } else {
    fetchData()
  }
}

// Search functions
const performSearch = async () => {
  if (!searchQuery.value.trim()) return
  
  try {
    console.log('Starting search for:', searchQuery.value)
    searchPending.value = true
    searchError.value = null
    isSearching.value = true
    
    const url = `/api/crypto/search?query=${encodeURIComponent(searchQuery.value)}&limit=20`
    console.log('Search URL:', url)
    
    const response = await $fetch(url, {
      timeout: 10000 // 10 second timeout
    })
    
    console.log('Search response:', response)
    
    // Handle the response data structure
    if (response.success && response.data) {
      // Map search results to match the expected format  
      const results = response.data.map((crypto: any) => {
        const price = parseFloat(crypto.current_price) || 0
        return {
          id: crypto.coin_id || crypto.id,
          symbol: crypto.symbol,
          name: crypto.name,
          image: crypto.image,
          current_price: price,
          market_cap: crypto.market_cap || 0,
          market_cap_rank: crypto.market_cap_rank || 0,
          price_change_24h: crypto.price_change_24h || 0,
          price_change_percentage_24h: parseFloat(crypto.price_change_percentage_24h) || 0,
          total_volume: crypto.total_volume || 0,
          circulating_supply: crypto.circulating_supply || 0,
          formatted_price: `$${price.toFixed(2)}`,
          formatted_market_cap: formatMarketCap(crypto.market_cap || 0),
          last_updated: crypto.last_updated || new Date().toISOString()
        }
      })
      searchResults.value = results
      console.log('Search results mapped:', results.length, 'items')
    } else {
      searchResults.value = []
      console.log('No search results found')
    }
  } catch (err: any) {
    searchError.value = err.data?.message || err.message || 'Failed to search cryptocurrencies'
    console.error('Error searching cryptocurrencies:', err)
    console.error('Full error details:', {
      status: err.status,
      statusText: err.statusText,
      data: err.data,
      message: err.message
    })
  } finally {
    searchPending.value = false
    console.log('Search completed, pending:', searchPending.value)
  }
}

const handleSearchInput = () => {
  console.log('Search input changed:', searchQuery.value)
  
  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  // If search query is empty, clear search
  if (!searchQuery.value.trim()) {
    console.log('Search query empty, clearing search')
    clearSearch()
    return
  }
  
  // Temporarily disable debouncing for debugging
  console.log('Performing immediate search for:', searchQuery.value)
  performSearch()
}

const clearSearch = () => {
  searchQuery.value = ''
  isSearching.value = false
  searchResults.value = []
  searchError.value = null
  
  if (searchTimeout) {
    clearTimeout(searchTimeout)
    searchTimeout = null
  }
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

const formatMarketCap = (value: number): string => {
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
  margin-bottom: 1rem;
}

.search-container {
  margin: 2rem 0;
  display: flex;
  justify-content: center;
}

.search-box {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 50px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 0.5rem;
  max-width: 600px;
  width: 100%;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}

.search-box:focus-within {
  border-color: #667eea;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  border-radius: 50px;
  background: transparent;
}

.search-input::placeholder {
  color: #a0aec0;
}

.search-button {
  background: #667eea;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-left: 0.5rem;
}

.search-button:hover:not(:disabled) {
  background: #5a67d8;
  transform: scale(1.05);
}

.search-button:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
  transform: none;
}

.clear-button {
  background: #e53e3e;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-left: 0.5rem;
  font-size: 0.875rem;
}

.clear-button:hover {
  background: #c53030;
  transform: scale(1.05);
}

.clear-search-button {
  padding: 0.5rem 1rem;
  background: #38a169;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  margin-left: 1rem;
}

.clear-search-button:hover {
  background: #2f855a;
  transform: translateY(-2px);
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
  
  .search-container {
    margin: 1.5rem 0;
  }
  
  .search-box {
    padding: 0.25rem;
  }
  
  .search-input {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .search-button, .clear-button {
    width: 35px;
    height: 35px;
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
  
  .search-container {
    margin: 1rem 0;
  }
  
  .search-input {
    font-size: 0.8rem;
    padding: 0.5rem;
  }
  
  .search-button, .clear-button {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
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
