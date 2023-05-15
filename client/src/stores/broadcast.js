import Vue from 'vue'
import { getContent } from '@/api/content'

export const store = Vue.observable({
  broadcastMessage: null,
})

export const getters = {
  getBroadcastMessage () {
    return store.broadcastMessage
  },
}

export const mutations = {
  async fetch () {
    try {
      store.broadcastMessage = await getContent(51)
    } catch (e) {
      store.broadcastMessage = null
    }
  },
}

export default { store, getters, mutations }
