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
    const config = useRuntimeConfig()
    const response = await $fetch(`${config.public.apiBase}/crypto/${id}`)
    return response
  } catch (error: any) {
    console.error('Server API error:', error)
    throw createError({
      statusCode: error.status || 500,
      statusMessage: error.message || 'Failed to fetch cryptocurrency data'
    })
  }
})
