import Vue from 'vue'
import { listCurrentPolls } from '@/api/voting'

export const store = Vue.observable({
  polls: null,
})

export const getters = {
  getPolls () {
    return store.polls
  },
}

export const mutations = {
  async fetchPolls () {
    store.polls = await listCurrentPolls()
  },
}

export default { store, getters, mutations }
