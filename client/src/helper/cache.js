export async function getCacheInterval (cacheRequestName, rateLimitInterval) {
  const cache = await caches.open('foodsharing.network')
  const cacheRequestNameWithSlash = '/' + cacheRequestName
  const lastFetchTimeRequestName = `${cacheRequestNameWithSlash}_lastFetchTime`
  const cachedLastFetchTime = await cache.match(lastFetchTimeRequestName)
  const lastFetchTime = cachedLastFetchTime ? parseInt(await cachedLastFetchTime.text()) : 0
  const currentTime = Date.now()
  const timeSinceLastFetch = currentTime - lastFetchTime
  return timeSinceLastFetch >= rateLimitInterval
}

export async function setCache (cacheRequestName, cacheValue) {
  const cache = await caches.open('foodsharing.network')
  const response = new Response(JSON.stringify(cacheValue))
  const cacheRequestNameWithSlash = '/' + cacheRequestName
  await cache.put(cacheRequestNameWithSlash, response)
  const lastFetchTimeRequestName = `${cacheRequestNameWithSlash}_lastFetchTime`
  const currentTime = Date.now()
  const timeResponse = new Response(currentTime.toString())
  await cache.put(lastFetchTimeRequestName, timeResponse)
}

export async function getCache (cacheRequestName) {
  const cache = await caches.open('foodsharing.network')
  const cacheRequestNameWithSlash = '/' + cacheRequestName
  const cacheResponse = await cache.match(cacheRequestNameWithSlash)
  return cacheResponse ? JSON.parse(await cacheResponse.text()) : null
}
