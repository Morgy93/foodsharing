import Vue from 'vue'
import { listChains, listChainStores, createChain, editChain } from '@/api/chains'

export const store = Vue.observable({
  chains: null,
  stores: null,
})

export const getters = {
  getChains: () => store.chains,
  getStores: () => store.stores,
}

export const mutations = {
  async fetchChains () {
    store.chains = await listChains()
  },
  async fetchChainStores (chainId) {
    store.stores = null
    store.stores = await listChainStores(chainId)
  },
  async createChain (data) {
    const fetched = await createChain(data)
    if (store.chains === null) {
      store.chains = []
    }
    store.chains.push(fetched)
  },
  async editChain (id, data) {
    const editedChain = await editChain(id, data)
    store.chains = store.chains.map(chainItem => chainItem.chain.id === id ? editedChain : chainItem)
  },
}

export default { store, getters, mutations }
