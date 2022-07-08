import Vue from 'vue'
import { getMailUnreadCount } from '@/api/mailbox'
import { getDetails } from '@/api/user'
import serverData from '@/helper/server-data'

export const store = Vue.observable({
  mailUnreadCount: 0,
  details: {},
  locations: serverData.locations || {},
  user: serverData.user,
  permissions: serverData.permissions,
  isLoggedIn: serverData.user?.id !== null,
})

export const getters = {
  isLoggedIn () {
    return store.isLoggedIn
  },
  isSleeping () {
    return store.details?.sleeping
  },
  isFoodsaver () {
    return store.user?.isFoodsaver
  },
  getUser () {
    return store.user
  },
  getUserId () {
    return store.user?.id
  },
  getUserDetails () {
    return store.details
  },
  getAvatar () {
    return store.user?.avatar
  },
  getUserFirstName  () {
    return store.user?.firstname
  },
  getUserLastName () {
    return store.user?.lastname || ''
  },
  hasHomeRegion () {
    return store.user?.homeRegionId > 0
  },
  getHomeRegion () {
    return store.user?.homeRegionId
  },
  getHomeRegionName () {
    return store.details?.regionName
  },
  hasCalendarToken () {
    return store.user?.hasCalendarToken !== null || false
  },
  getMailBox () {
    return store.user?.mailBoxId
  },
  hasMailBox () {
    return store.user?.mailBoxId > 0 || false
  },
  getMailUnreadCount () {
    if (store.mailUnreadCount > 0) {
      return store.mailUnreadCount < 99 ? store.mailUnreadCount : '99+'
    }
    return null
  },
  getStats () {
    return store.details?.stats || {}
  },
  hasLocations () {
    return store.locations.lat !== 0 && store.locations.lng !== 0
  },
  getLocations () {
    return store.locations || { lat: 0, lng: 0 }
  },
  getPermissions () {
    return store.permissions || {}
  },
  hasAdminPermissions () {
    const permissions = Object.entries(store.permissions)
    return permissions.some(([key, value]) => !['mayAdministrateUserProfile', 'mayEditUserProfile', 'addStore'].includes(key) && value)
  },
}

export const mutations = {
  async fetchDetails () {
    try {
      store.details = await getDetails()
    } catch (e) {
      store.details = null
    }
  },

  async fetchMailUnreadCount () {
    store.mailUnreadCount = await getMailUnreadCount()
  },
}

export default { store, getters, mutations }
