import Vue from 'vue'

export const store = Vue.observable({
  show: false,
})

export const getters = {
  get () {
    return store.show
  },
}

export const mutations = {
  show () {
    store.show = true
  },
  hide () {
    store.show = false
  },
  toggle () {
    store.show = !store.show
  },
}

export default { store, getters, mutations }
