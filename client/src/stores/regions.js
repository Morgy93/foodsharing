import Vue from 'vue'
import DataStore from './user'

export const store = Vue.observable({
  regions: [],
})

export const getters = {
  get () {
    return store.regions
  },
  getHomeRegion () {
    return store.regions.find(r => r.id === DataStore.getters.getRegionId())
  },
}

export const mutations = {
  set (regions) {
    store.regions = regions
  },
}

export default { store, getters, mutations }
