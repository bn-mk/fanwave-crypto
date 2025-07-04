export interface CryptoCurrency {
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

export interface ApiResponse<T> {
  success: boolean
  data: T
  meta?: {
    count: number
    limit: number
    endpoint?: string
    last_updated?: string
  }
}

export interface ApiError {
  success: false
  message: string
  error?: string
}

export const useCrypto = () => {
  const config = useRuntimeConfig()
  
  // Get top cryptocurrencies by market cap
  const getTopCryptocurrencies = async (limit: number = 10): Promise<CryptoCurrency[]> => {
    try {
      const response = await $fetch<ApiResponse<CryptoCurrency[]>>(`${config.public.apiBase}/crypto/top?limit=${limit}`)
      
      return response.data
    } catch (error: any) {
      console.error('Failed to fetch top cryptocurrencies:', error)
      throw new Error(error?.data?.message || error?.message || 'Failed to fetch cryptocurrency data')
    }
  }
  
  // Get specific cryptocurrency by ID
  const getCryptocurrency = async (coinId: string): Promise<CryptoCurrency> => {
    try {
      const { data } = await $fetch<ApiResponse<CryptoCurrency>>(`${config.public.apiBase}/crypto/${coinId}`)
      
      return data
    } catch (error: any) {
      console.error(`Failed to fetch cryptocurrency ${coinId}:`, error)
      throw new Error(error.data?.message || 'Failed to fetch cryptocurrency data')
    }
  }
  
  // Search cryptocurrencies
  const searchCryptocurrencies = async (query: string, limit: number = 20): Promise<CryptoCurrency[]> => {
    try {
      const { data } = await $fetch<ApiResponse<CryptoCurrency[]>>(`${config.public.apiBase}/cryptocurrencies/search`, {
        params: { query, limit }
      })
      
      return data
    } catch (error: any) {
      console.error('Failed to search cryptocurrencies:', error)
      throw new Error(error.data?.message || 'Failed to search cryptocurrency data')
    }
  }
  
  // Get cryptocurrency statistics
  const getCryptocurrencyStats = async () => {
    try {
      const { data } = await $fetch<ApiResponse<{
        total_cryptocurrencies: number
        last_data_update: string
        highest_market_cap: number
        average_price: number
        data_freshness: string
      }>>(`${config.public.apiBase}/cryptocurrencies/stats`)
      
      return data
    } catch (error: any) {
      console.error('Failed to fetch cryptocurrency stats:', error)
      throw new Error(error.data?.message || 'Failed to fetch cryptocurrency statistics')
    }
  }
  
  return {
    getTopCryptocurrencies,
    getCryptocurrency,
    searchCryptocurrencies,
    getCryptocurrencyStats
  }
}
