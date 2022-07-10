import Vue from 'vue'
import { getMapMarkers } from '@/api/map'

export const store = Vue.observable({
  baskets: [],
  stores: [],
  communities: [],
  foodshare_points: [],
  modal: {},
})

function formatMarkerWithType (marker, type) {
  return marker.map(m => ({
    ...m,
    type: type,
  }))
}

export const getters = {
  getMarkerModalData () {
    return store.modal
  },
  getBaskets () {
    return store.baskets
  },
  getStores () {
    return store.stores
  },
  getCommunities () {
    return store.communities
  },
  getFoodsharePoints () {
    return store.foodshare_points
  },
  getMarkers (type, state) {
    switch (type) {
      case 'baskets':
        return getters.getBaskets()
      case 'stores':
        return getters.getStores(state)
      case 'communities':
        return getters.getCommunities()
      case 'foodshare_points':
        return getters.getFoodsharePoints()
    }
  },
}

export const mutations = {
  setMarkerModalData (data) {
    store.modal = data
  },
  async fetchByType (type) {
    switch (type) {
      case 'baskets':
        await mutations.fetchBaskets()
        break
      case 'stores':
        await mutations.fetchStores()
        break
      case 'communities':
        await mutations.fetchCommunities()
        break
      case 'foodshare_points':
        await mutations.fetchFoodsharePoints()
        break
    }
  },
  async fetchBaskets () {
    if (store.baskets.length > 0) return
    const fetch = await getMapMarkers(['baskets'])
    store.baskets = formatMarkerWithType(fetch.baskets, 'baskets')
  },
  async fetchCommunities () {
    if (store.communities.length > 0) return
    const fetch = await getMapMarkers(['communities'])
    store.communities = formatMarkerWithType(fetch.communities, 'communities')
  },
  async fetchFoodsharePoints () {
    if (store.foodshare_points.length > 0) return
    const fetch = await getMapMarkers(['fairteiler'])
    store.foodshare_points = formatMarkerWithType(fetch.fairteiler, 'foodshare_points')
  },
  async fetchStores () {
    if (store.stores.length > 0) return
    const fetch = await getMapMarkers(['betriebe'])
    store.stores = formatMarkerWithType(fetch.betriebe, 'stores')
  },
}

export default { store, getters, mutations }
