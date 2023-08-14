import { get, patch, post } from './base'

export async function listChains () {
  return await get('/chains')
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
