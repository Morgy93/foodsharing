import Vue from 'vue'
import { listStoresForCurrentUser } from '@/api/stores'

export const store = Vue.observable({
  stores: [],
})

export const getters = {
  getAll () {
    return store.stores.length > 0 ? store.stores : []
  },

  getOthers () {
    const others = Array.from(store.stores).filter(s => !s.isManaging && s.membershipStatus === 1)
    return others.length > 0 ? others : []
  },

  getManaging () {
    const managing = Array.from(store.stores).filter(s => s.isManaging)
    return managing.length > 0 ? managing : []
  },

  getJumping () {
    const jumping = Array.from(store.stores).filter(s => s.membershipStatus === 2)
    return jumping.length > 0 ? jumping : []
  },

  hasStores () {
    return store.stores.length > 0
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
