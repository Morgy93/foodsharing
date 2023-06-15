import Vue from 'vue'
import { listRegisteredPickups, listPickupOptions, getRegularPickup } from '@/api/pickups'

export const store = Vue.observable({
  registred: [],
  options: [],
  regularPickup: [],
})

export const getters = {
  getRegistered () {
    return store.registred
  },
  getOptions () {
    return store.options
  },
  getRegularPickup () {
    return store.regularPickup
  },
}

export const mutations = {
  async fetchRegistered (id) {
    store.registred = await listRegisteredPickups(id)
  },
  async fetchRegularPickup (storeId) {
    store.regularPickup = await getRegularPickup(storeId)
  },
  async fetchOptions (amount = 10) {
    store.options = await listPickupOptions(amount)
  },
}

export default { store, getters, mutations }
