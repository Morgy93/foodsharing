import { get, patch, post } from './base'

export async function listChains () {
  return await get('/chains')
}

export async function listChainStores (chainId) {
  return await get(`/chain/${chainId}/stores`)
}

export async function createChain (data) {
  return await post('/chain', data)
}

export async function editChain (id, data) {
  return await patch(`/chain/${id}`, data)
}
