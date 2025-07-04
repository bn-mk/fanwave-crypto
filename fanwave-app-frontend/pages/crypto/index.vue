<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <header class="text-center mb-12">
        <NuxtLink to="/" class="inline-block mb-4 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-full transition-all duration-300 hover:-translate-y-1 font-medium no-underline">
          ← Back to Home
        </NuxtLink>
        <h1 class="text-3xl md:text-5xl font-bold text-gray-800 mb-2">
          {{ isSearching ? 'Search Results' : 'Top 10 Cryptocurrencies' }}
        </h1>
        <p class="text-lg md:text-xl text-gray-600 mb-4">
          {{ isSearching ? `Search results for "${searchQuery}"` : 'Track the biggest digital currencies by market cap' }}
        </p>
        
        <!-- Search Bar -->
        <div class="my-8 flex justify-center">
          <div class="flex items-center bg-white rounded-full shadow-lg border-2 border-gray-200 focus-within:border-primary-500 focus-within:shadow-xl transition-all duration-300 p-2 max-w-2xl w-full">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search cryptocurrencies by name or symbol..."
              class="flex-1 border-none outline-none px-4 py-3 text-base rounded-full bg-transparent placeholder-gray-400"
              @input="handleSearchInput"
              @keyup.enter="performSearch"
            />
            <button 
              @click="() => { console.log('Search button clicked'); performSearch(); }" 
              class="bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-full w-10 h-10 flex items-center justify-center transition-all duration-300 hover:scale-105 ml-2"
              :disabled="!searchQuery.trim()"
            >
              🔍
            </button>
            <button 
              v-if="isSearching"
              @click="clearSearch" 
              class="bg-red-500 hover:bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center transition-all duration-300 hover:scale-105 ml-2 text-sm"
            >
              ✕
            </button>
          </div>
        </div>
        
        <div v-if="lastUpdated && !isSearching" class="text-sm text-gray-500 italic mt-2">
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
      <div v-else-if="(cryptoData && !isSearching) || (searchResults && isSearching)" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-8 fade-in">
        <div 
          v-for="crypto in (isSearching ? searchResults : cryptoData)" 
          :key="crypto.id"
          class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group"
          @click="handleCardClick(crypto.id)"
        >
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center overflow-hidden">
                <img 
                  v-if="crypto.image" 
                  :src="crypto.image" 
                  :alt="crypto.name"
                  class="w-full h-full object-cover rounded-full"
                  @error="handleImageError"
                />
                <span v-else class="text-white font-bold text-xs">{{ crypto.symbol }}</span>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-800 group-hover:text-primary-600 transition-colors">{{ crypto.name }}</h3>
                <span class="text-sm text-gray-500 font-medium">{{ crypto.symbol }}</span>
              </div>
            </div>
            <div class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-semibold">
              #{{ crypto.market_cap_rank }}
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
              <span class="text-sm text-gray-500 font-medium">Price</span>
              <span class="text-lg font-semibold text-primary-600">{{ crypto.formatted_price }}</span>
            </div>
            
            <div class="flex flex-col gap-1">
              <span class="text-sm text-gray-500 font-medium">24h Change</span>
              <span 
                :class="[
                  'text-lg font-semibold',
                  crypto.price_change_percentage_24h >= 0 ? 'text-success-500' : 'text-danger-500'
                ]"
              >
                {{ formatPercentage(crypto.price_change_percentage_24h) }}%
              </span>
            </div>
            
            <div class="flex flex-col gap-1">
              <span class="text-sm text-gray-500 font-medium">Market Cap</span>
              <span class="text-base font-semibold text-gray-800">{{ crypto.formatted_market_cap }}</span>
            </div>
            
            <div class="flex flex-col gap-1">
              <span class="text-sm text-gray-500 font-medium">Volume (24h)</span>
              <span class="text-base font-semibold text-gray-800">{{ formatVolume(crypto.total_volume) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="error-container">
        <p class="error-message">{{ isSearching ? `No cryptocurrencies found for "${searchQuery}"` : 'No cryptocurrency data available' }}</p>
        <button @click="refresh()" class="retry-button">{{ isSearching ? 'Search Again' : 'Reload' }}</button>
        <button v-if="isSearching" @click="clearSearch()" class="px-4 py-2 bg-success-500 hover:bg-success-600 text-white rounded-lg font-medium transition-all duration-300 hover:-translate-y-0.5 ml-4">Show Top 10</button>
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
