import Vue from 'vue'

export const store = Vue.observable({
  groups: [],
})

export const getters = {
  get () {
    return store.groups
  },
}

export const mutations = {
  set (groups) {
    store.groups = groups
  },
}

export default { store, getters, mutations }
