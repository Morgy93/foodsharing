import Vue from 'vue'
import { getBaskets, getBasketsNearby, listBasketCoordinates } from '@/api/baskets'

export const store = Vue.observable({
  own: [],
  nearby: [],
  radius: 45,
  allCoordinates: [],
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
  getRequestdCount () {
    return store.own.map(basket => basket.requests.length).reduce((a, b) => a + b, 0)
  },
  getAllBasketCoordinates () {
    return store.allCoordinates
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
  async fetchAllCoordinates () {
    store.allCoordinates = await listBasketCoordinates()
  },
}

export default { store, getters, mutations }
