<template>
  <div class="crypto-page">
    <div class="container">
      <header class="page-header">
        <NuxtLink to="/" class="back-btn">← Back to Home</NuxtLink>
        <h1 class="page-title">Top 10 Cryptocurrencies</h1>
        <p class="page-subtitle">Track the biggest digital currencies by market cap</p>
      </header>

      <div class="crypto-grid">
        <div 
          v-for="crypto in cryptoData" 
          :key="crypto.id"
          class="crypto-card"
        >
          <div class="crypto-header">
            <div class="crypto-info">
              <div class="crypto-icon">{{ crypto.symbol }}</div>
              <div class="crypto-details">
                <h3 class="crypto-name">{{ crypto.name }}</h3>
                <span class="crypto-symbol">{{ crypto.symbol }}</span>
              </div>
            </div>
            <div class="crypto-rank">#{{ crypto.rank }}</div>
          </div>
          
          <div class="crypto-stats">
            <div class="stat-item">
              <span class="stat-label">Price</span>
              <span class="stat-value price">${{ crypto.price }}</span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">24h Change</span>
              <span 
                :class="['stat-value', 'change', crypto.change24h >= 0 ? 'positive' : 'negative']"
              >
                {{ crypto.change24h >= 0 ? '+' : '' }}{{ crypto.change24h }}%
              </span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">Market Cap</span>
              <span class="stat-value">${{ crypto.marketCap }}</span>
            </div>
            
            <div class="stat-item">
              <span class="stat-label">Volume (24h)</span>
              <span class="stat-value">${{ crypto.volume24h }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Dummy data for top 10 cryptocurrencies
const cryptoData = [
  {
    id: 1,
    name: 'Bitcoin',
    symbol: 'BTC',
    rank: 1,
    price: '43,250.00',
    change24h: 2.45,
    marketCap: '847.2B',
    volume24h: '25.3B'
  },
  {
    id: 2,
    name: 'Ethereum',
    symbol: 'ETH',
    rank: 2,
    price: '2,650.00',
    change24h: -1.23,
    marketCap: '318.7B',
    volume24h: '12.8B'
  },
  {
    id: 3,
    name: 'Tether',
    symbol: 'USDT',
    rank: 3,
    price: '1.00',
    change24h: 0.01,
    marketCap: '95.4B',
    volume24h: '42.1B'
  },
  {
    id: 4,
    name: 'BNB',
    symbol: 'BNB',
    rank: 4,
    price: '315.20',
    change24h: 3.67,
    marketCap: '47.2B',
    volume24h: '1.8B'
  },
  {
    id: 5,
    name: 'Solana',
    symbol: 'SOL',
    rank: 5,
    price: '98.45',
    change24h: -2.18,
    marketCap: '42.8B',
    volume24h: '2.1B'
  },
  {
    id: 6,
    name: 'XRP',
    symbol: 'XRP',
    rank: 6,
    price: '0.6180',
    change24h: 4.32,
    marketCap: '33.5B',
    volume24h: '1.4B'
  },
  {
    id: 7,
    name: 'USDC',
    symbol: 'USDC',
    rank: 7,
    price: '1.00',
    change24h: -0.02,
    marketCap: '32.1B',
    volume24h: '5.8B'
  },
  {
    id: 8,
    name: 'Cardano',
    symbol: 'ADA',
    rank: 8,
    price: '0.4890',
    change24h: 1.89,
    marketCap: '17.2B',
    volume24h: '654M'
  },
  {
    id: 9,
    name: 'Dogecoin',
    symbol: 'DOGE',
    rank: 9,
    price: '0.0823',
    change24h: -0.45,
    marketCap: '11.8B',
    volume24h: '892M'
  },
  {
    id: 10,
    name: 'Avalanche',
    symbol: 'AVAX',
    rank: 10,
    price: '24.67',
    change24h: 5.12,
    marketCap: '9.7B',
    volume24h: '312M'
  }
]
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
