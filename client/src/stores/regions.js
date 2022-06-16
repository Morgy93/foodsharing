import Vue from 'vue'

export const store = Vue.observable({
  regions: [],
})

export const getters = {
  get () {
    return store.regions
  },
}

export const mutations = {
  set (regions) {
    store.regions = regions
  },
}

export default { store, getters, mutations }
