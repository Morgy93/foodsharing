import Vue from 'vue'
import { listStoresForCurrentUser } from '@/api/stores'

export const store = Vue.observable({
  stores: [],
})

export const getters = {
  get () {
    return store.stores.length > 0 ? store.stores : []
  },

  has (id) {
    return store.stores.find(store => store.id === id)
  },
}

export const mutations = {
  async fetch (force = false) {
    if (!store.length || force) {
      store.stores = await listStoresForCurrentUser()
    }
  },
}

export default { store, getters, mutations }
