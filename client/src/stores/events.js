import Vue from 'vue'
import { updateInvitationResponse } from '@/api/events'

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
  setInvitationResponse (eventId, status) {
    return updateInvitationResponse(eventId, status)
  },
}

export const EventInvitationResponse = Object.freeze({
  EVENT_INVITATION_RESPONSE_YES: 1,
  EVENT_INVITATION_RESPONSE_MAYBE: 2,
  EVENT_INVITATION_RESPONSE_NO: 3,
})

export default { store, getters, mutations, EventInvitationStatus: EventInvitationResponse }
