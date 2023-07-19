import { get, patch, post } from './base'

export async function listChains () {
  const chains = await get('/chains')
  // format chain to match keys
  return chains.map(chain => {
    const changeKey = (key, newKey) => {
      chain[newKey] = chain[key]
      delete chain[key]
    }
    changeKey('store_count', 'stores')
    changeKey('headquarters_city', 'headquarters')
    return chain
  })
}

export async function listChainStores (chainId) {
  return await get(`/chains/${chainId}/stores`)
}

export async function createChain (data) {
  return await post('/chains', data)
}

export async function editChain (id, data) {
  return await patch(`/chains/${id}`, data)
}
