import Vue from 'vue'
import { getMailUnreadCount } from '@/api/mailbox'
import { getDetails } from '@/api/user'
import serverData from '@/scripts/server-data'
console.log(serverData)
export const store = Vue.observable({
  mailUnreadCount: 0,
  details: {},
  coordinates: serverData.coordinates || {},
  user: serverData.user,
  permissions: serverData.permissions,
  isLoggedIn: serverData.user.id !== null,
})

export const getters = {
  isLoggedIn () {
    return store.isLoggedIn
  },
  isFoodsaver () {
    return store.user?.isFoodsaver
  },
  getUser () {
    return store.user
  },
  getUserDetails () {
    return store.details
  },
  getAvatar () {
    return store.user?.avatar
  },
  getUserName () {
    return store.user?.firstname
  },
  getUserId () {
    return store.user?.id
  },
  hasHomeRegion () {
    return store.user?.homeRegionId > 0
  },
  getHomeRegion () {
    return store.user?.homeRegionId
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
    return store.user?.stats || {}
  },
  hasCoordinates () {
    return store.coordinates.lat !== 0 && store.coordinates.lng !== 0
  },
  getCoordinates () {
    return store.coordinates || { lat: 0, lng: 0 }
  },
  getPermissions () {
    return store.permissions || {}
  },
  hasAdminPermissions () {
    const permissions = Object.entries(store.permissions)
    return permissions.some(([key, value]) => key !== 'mayEditUserProfile' && value)
  },
  hasRegion () {
    return store.user?.regionId > 0 && store.user?.regionName.length > 0
  },
}

export const mutations = {
  async fetchDetails () {
    try {
      store.userDetails = await getDetails()
    } catch (e) {
      store.userDetails = null
    }
  },

  async fetchMailUnreadCount () {
    store.mailUnreadCount = await getMailUnreadCount()
  },
}

export default { store, getters, mutations }
