import Vue from 'vue'
import { getMapMarkers } from '@/api/map'

export const store = Vue.observable({
  baskets: [],
  stores: [],
  communities: [],
  foodshare_points: [],
})

function formatMarkerWithType (marker, type) {
  return marker.map(m => ({
    ...m,
    type: type,
  }))
}

export const getters = {
  getBaskets () {
    return formatMarkerWithType(store.baskets, 'baskets')
  },
  getStores () {
    return formatMarkerWithType(store.stores, 'stores')
  },
  getCommunities () {
    return formatMarkerWithType(store.communities, 'communities')
  },
  getFoodsharePoints () {
    return formatMarkerWithType(store.foodshare_points, 'foodshare_points')
  },
  getMarkers (type) {
    switch (type) {
      case 'baskets':
        return getters.getBaskets()
      case 'stores':
        return getters.getStores()
      case 'communities':
        return getters.getCommunities()
      case 'fairteiler':
        return getters.getFoodsharePoints()
    }
  },
}

export const mutations = {
  async fetchByType (type) {
    console.log(type)
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
      case 'fairteiler':
        await mutations.fetchFoodsharePoints()
        break
    }
    return getters.getMarkers(type)
  },
  async fetchBaskets () {
    if (store.baskets.length > 0) return
    const fetch = await getMapMarkers(['baskets'])
    store.baskets = fetch.baskets
  },
  async fetchCommunities () {
    if (store.communities.length > 0) return
    const fetch = await getMapMarkers(['communities'])
    store.communities = fetch.communities
  },
  async fetchFoodsharePoints () {
    if (store.foodshare_points.length > 0) return
    const fetch = await getMapMarkers(['fairteiler'])
    store.foodshare_points = fetch.fairteiler
  },
  async fetchStores () {
    if (store.betriebe.length > 0) return
    const fetch = await getMapMarkers(['betriebe'])
    const stores = fetch.betriebe
    for (const store of stores) {
      const find = store.stores.find(s => s.id === store.id)
      if (!find) {
        store.stores.push(store)
      }
    }
  },
}

export default { store, getters, mutations }
