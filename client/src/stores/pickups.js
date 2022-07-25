import Vue from 'vue'
import { listRegisteredPickups, listPickupOptions } from '@/api/pickups'

export const store = Vue.observable({
  registred: [],
  options: [],
})

export const getters = {
  getRegistered () {
    return store.registred
  },
  getOptions () {
    return store.options
  },
}

export const mutations = {
  async fetchRegistered (id) {
    store.registred = await listRegisteredPickups(id)
  },
  async fetchOptions (amount = 10) {
    store.options = await listPickupOptions(amount)
  },
}

export default { store, getters, mutations }
