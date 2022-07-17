import Vue from 'vue'
import { getUpdates } from '@/api/dashboard'

export const store = Vue.observable({
  updates: [],
})

export const getters = {
  get () {
    return store.updates
  },

}

export const mutations = {
  async fetch (page = 0) {
    store.updates = await getUpdates(page)
    return store.updates
  },
}

export default { store, getters, mutations }
