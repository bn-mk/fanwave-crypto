// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  devtools: { enabled: true },
  
  runtimeConfig: {
    public: {
      apiBase: '/api'
    }
  },
  
  css: ['~/assets/css/main.css']
})
