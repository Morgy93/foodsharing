import Vue from 'vue'
import { listRegisteredPickups, listPickupOptions, getRegularPickup, listPickups } from '@/api/pickups'

export const store = Vue.observable({
  registred: [],
  options: [],
  regularPickup: [],
  pickups: [],
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
  getPickups () {
    return store.pickups
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
  async loadPickups (storeId) {
    store.pickups = await listPickups(storeId)
  },
}

export default { store, getters, mutations }
