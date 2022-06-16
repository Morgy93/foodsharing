import Vue from 'vue'
import { getBaskets, getBasketsNearby } from '@/api/baskets'

export const store = Vue.observable({
  own: [],
  nearby: [],
  radius: 45,
})

export const getters = {
  getOwn () {
    return store.own
  },
  getNearby (amount = 10) {
    return store.nearby.slice(0, amount)
  },
  getRadius () {
    return store.radius
  },
}

export const mutations = {
  async fetchOwn () {
    store.own = await getBaskets()
  },
  async fetchNearby ({ lat, lon } = {}, distance = store.radius) {
    store.nearby = await getBasketsNearby(parseFloat(lat), parseFloat(lon), distance)
    return store.nearby
  },
  async fetchGermany () {
    return await this.fetchNearby({ lat: 50.89, lon: 10.13 }, 50)
  },
}

export default { store, getters, mutations }
