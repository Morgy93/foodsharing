import Vue from 'vue'
import { listStoresForCurrentUser } from '@/api/stores'

export const store = Vue.observable({
  stores: [],
})

export const getters = {
  get () {
    return store.stores.length > 0 ? store.stores : []
  },

  getOthers () {
    const others = store.stores.filter(s => !s.isManaging && !s.isWaiting)
    return others.length > 0 ? others : []
  },

  getManaging () {
    const managing = store.stores.filter(s => s.isManaging)
    return managing.length > 0 ? managing : []
  },

  getWaiting () {
    const waiting = store.stores.filter(s => s.isWaiting)
    return waiting.length > 0 ? waiting : []
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
