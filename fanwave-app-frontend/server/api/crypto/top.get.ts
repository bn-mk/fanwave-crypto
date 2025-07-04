export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const limit = query.limit || '10'

  try {
    // Make the API call server-side
    const response = await $fetch(`http://localhost/api/crypto/top?limit=${limit}`)
    return response
  } catch (error: any) {
    console.error('Server API error:', error)
    throw createError({
      statusCode: error.status || 500,
      statusMessage: error.message || 'Failed to fetch cryptocurrency data'
    })
  }
})
