import Vue from 'vue'
import { getApiToken } from '@/api/calendar'

export const store = Vue.observable({
  token: null,
})

export const getters = {
  hasToken: () => store.token,
}

export const mutations = {
  async fetchToken () {
    store.token = await getApiToken()
  },
}

export default { store, getters, mutations }
