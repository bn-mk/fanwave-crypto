export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  
  if (!id) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Cryptocurrency ID is required'
    })
  }

  try {
    // Make the API call server-side
    const response = await $fetch(`http://localhost/api/crypto/${id}`)
    return response
  } catch (error: any) {
    console.error('Server API error:', error)
    throw createError({
      statusCode: error.status || 500,
      statusMessage: error.message || 'Failed to fetch cryptocurrency data'
    })
  }
})
