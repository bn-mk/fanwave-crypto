export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const searchQuery = query.query as string
  const limit = query.limit || '20'

  if (!searchQuery) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Search query is required'
    })
  }

  try {
    // Make the API call server-side
    const response = await $fetch(`http://localhost/api/cryptocurrencies/search?query=${encodeURIComponent(searchQuery)}&limit=${limit}`)
    return response
  } catch (error: any) {
    console.error('Server API search error:', error)
    throw createError({
      statusCode: error.status || 500,
      statusMessage: error.message || 'Failed to search cryptocurrencies'
    })
  }
})
