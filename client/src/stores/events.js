import Vue from 'vue'

export const store = Vue.observable({
  invites: [],
  accepted: [],
})

export const getters = {
  getInvited () {
    return store.invites
  },
  getAccepted () {
    return store.accepted
  },
}

export const mutations = {
  setInvited (events) {
    store.invites = events
  },
  setAccepted (events) {
    store.accepted = events
  },
}

export default { store, getters, mutations }
