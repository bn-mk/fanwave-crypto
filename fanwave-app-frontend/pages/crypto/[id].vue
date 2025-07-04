<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Loading State -->
      <div v-if="pending" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">Loading cryptocurrency details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <div class="flex gap-4 justify-center items-center">
          <button @click="refresh()" class="retry-button">Try Again</button>
          <NuxtLink to="/crypto" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-all duration-300 hover:-translate-y-0.5 no-underline inline-block">
            Back to List
          </NuxtLink>
        </div>
      </div>

      <!-- Success State -->
      <div v-else-if="cryptoDetail" class="fade-in">
        <!-- Header -->
        <header class="mb-12">
          <NuxtLink to="/crypto" class="inline-block mb-8 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-full transition-all duration-300 hover:-translate-y-1 font-medium no-underline">
            ← Back to Cryptocurrencies
          </NuxtLink>
          
          <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-200">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-8">
              <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center overflow-hidden">
                  <img 
                    v-if="cryptoDetail.image" 
                    :src="cryptoDetail.image" 
                    :alt="cryptoDetail.name"
                    class="w-full h-full object-cover rounded-full"
                    @error="handleImageError"
                  />
                  <span v-else class="text-white font-bold text-xl">{{ cryptoDetail.symbol }}</span>
                </div>
                <div>
                  <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">{{ cryptoDetail.name }}</h1>
                  <span class="text-xl text-gray-600 font-semibold">{{ cryptoDetail.symbol }}</span>
                  <div class="text-sm text-primary-600 mt-2 font-medium">#{{ cryptoDetail.market_cap_rank }} by Market Cap</div>
                </div>
              </div>
              
              <div class="text-center lg:text-right">
                <div class="text-4xl md:text-5xl font-bold text-primary-600 mb-2">{{ cryptoDetail.formatted_price }}</div>
                <div 
                  :class="[
                    'text-xl font-semibold',
                    cryptoDetail.price_change_percentage_24h >= 0 ? 'text-success-500' : 'text-danger-500'
                  ]"
                >
                  {{ formatPercentage(cryptoDetail.price_change_percentage_24h) }}% (24h)
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8 mb-8">
          <!-- Market Data -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b-2 border-gray-200">Market Data</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Market Cap</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ cryptoDetail.formatted_market_cap }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">24h Volume</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatVolume(cryptoDetail.total_volume) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Fully Diluted Valuation</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatVolume(cryptoDetail.fully_diluted_valuation) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Circulating Supply</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatSupply(cryptoDetail.circulating_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
            </div>
          </div>

          <!-- Price Data -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b-2 border-gray-200">Price Data</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">24h High</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatPrice(cryptoDetail.high_24h) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">24h Low</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatPrice(cryptoDetail.low_24h) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">24h Change</span>
                <span 
                  :class="[
                    'text-base font-semibold text-right',
                    cryptoDetail.price_change_24h >= 0 ? 'text-success-500' : 'text-danger-500'
                  ]"
                >
                  {{ formatPrice(cryptoDetail.price_change_24h) }}
                </span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Market Cap Change (24h)</span>
                <span 
                  :class="[
                    'text-base font-semibold text-right',
                    cryptoDetail.market_cap_change_percentage_24h >= 0 ? 'text-success-500' : 'text-danger-500'
                  ]"
                >
                  {{ formatPercentage(cryptoDetail.market_cap_change_percentage_24h) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Supply Information -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b-2 border-gray-200">Supply Information</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Total Supply</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatSupply(cryptoDetail.total_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Max Supply</span>
                <span class="text-base font-semibold text-gray-800 text-right">
                  {{ cryptoDetail.max_supply ? formatSupply(cryptoDetail.max_supply) + ' ' + cryptoDetail.symbol : 'N/A' }}
                </span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">Circulating Supply</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatSupply(cryptoDetail.circulating_supply) }} {{ cryptoDetail.symbol }}</span>
              </div>
            </div>
          </div>

          <!-- All-Time Records -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b-2 border-gray-200">All-Time Records</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">All-Time High</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatPrice(cryptoDetail.ath) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">ATH Change</span>
                <span class="text-base font-semibold text-danger-500 text-right">{{ formatPercentage(cryptoDetail.ath_change_percentage) }}%</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">ATH Date</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatDate(cryptoDetail.ath_date) }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500 font-medium">All-Time Low</span>
                <span class="text-base font-semibold text-gray-800 text-right">{{ formatPrice(cryptoDetail.atl) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center mt-8">
          <p class="text-sm text-gray-500 italic">Last updated: {{ formatDate(cryptoDetail.last_updated) }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="error-container">
        <p class="error-message">Cryptocurrency not found</p>
        <NuxtLink to="/crypto" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-all duration-300 hover:-translate-y-0.5 no-underline inline-block">
          Back to List
        </NuxtLink>
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
