import Vue from 'vue'
import { getStoreMarkers, getCommunitiesMarkers, getFoodbasketsMarkers, getFoodsharepointsMarkers } from '@/api/map'

export const store = Vue.observable({
  baskets: [],
  stores: [],
  store_filters: {},
  communities: [],
  foodshare_points: [],
  modal: {},
})

export const getters = {
  getMarkerModalData () {
    return store.modal
  },
  getMarkers (type) {
    const markers = []
    switch (type) {
      case 'baskets':
        markers.push(...store.baskets)
        break
      case 'stores':
        markers.push(...store.stores)
        break
      case 'communities':
        markers.push(...store.communities)
        break
      case 'foodshare_points':
        markers.push(...store.foodshare_points)
        break
    }
  },
}

export const mutations = {
  setMarkerModalData (data) {
    store.modal = data
  },
  async fetchByType (type, options) {
    switch (type) {
      case 'baskets':
        store.baskets = await getFoodbasketsMarkers(options)
        break
      case 'stores':
        store.baskets = await getStoreMarkers(options)
        break
      case 'communities':
        store.baskets = await getCommunitiesMarkers(options)
        break
      case 'foodshare_points':
        store.baskets = await getFoodsharepointsMarkers(options)
        break
    }
  },
}

export default { store, getters, mutations }
