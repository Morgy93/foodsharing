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
    const others = store.stores.filter(s => !s.isManaging && !s.isJumping)
    return others.length > 0 ? others : []
  },

  getManaging () {
    const managing = store.stores.filter(s => s.isManaging)
    return managing.length > 0 ? managing : []
  },

  getJumping () {
    const jumping = store.stores.filter(s => s.isJumping)
    return jumping.length > 0 ? jumping : []
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
