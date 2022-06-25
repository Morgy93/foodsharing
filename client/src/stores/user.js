import Vue from 'vue'
import { getMailUnreadCount } from '@/api/mailbox'
import { getDetails } from '@/api/user'

export const store = Vue.observable({
  mailUnreadCount: 0,
  user: null,
  isLoggedIn: document.cookie.split(';').some(cookie => cookie.trim().startsWith('CSRF_TOKEN=')),
})

export const getters = {
  isLoggedIn () {
    return store.isLoggedIn || document.cookie.split(';').some(cookie => cookie.trim().startsWith('CSRF_TOKEN='))
  },
  isFoodsaver () {
    return store.user?.foodsaver
  },
  getUser () {
    return store.user
  },
  hasCalendarToken () {
    return store.user?.hasCalendarToken !== null || false
  },
  getMailBox () {
    return store.user?.mailboxId
  },
  hasMailBox () {
    return store.user?.mailboxId > 0 || false
  },
  getMailUnreadCount () {
    if (store.mailUnreadCount > 0) {
      return store.mailUnreadCount < 99 ? store.mailUnreadCount : '99+'
    }
    return null
  },
  getStats () {
    return store.user?.stats || {}
  },
  hasCoordinates () {
    return store.user?.coordinates.lat !== 0 && store.user?.coordinates.lng !== 0
  },
  getCoordinates () {
    return store.user?.coordinates || {}
  },
  getPermissions () {
    return store.user?.permissions || {}
  },
  hasAdminPermissions () {
    return store.user?.permissions &&
            (store.user.permissions.administrateBlog ||
            store.user.permissions.editQuiz ||
            store.user.permissions.handleReports ||
            store.user.permissions.editContent ||
            store.user.permissions.manageMailboxes ||
            store.user.permissions.administrateNewsletterEmail ||
            store.user.permissions.administrateRegions)
  },
}

export const mutations = {
  async fetchDetails () {
    try {
      store.user = await getDetails()
    } catch (e) {
      store.user = null
    }
  },

  async fetchMailUnreadCount () {
    store.mailUnreadCount = await getMailUnreadCount()
  },
}

export default { store, getters, mutations }
