import Vue from 'vue'
import {
  listStoresForCurrentUser,
  getStoreMetaData,
  getStoreMember,
  getStoreInformation,
  getStorePermissions,
  listStoreTeamMembershipRequests,
} from '@/api/stores'
import { getRegionOptions } from '@/api/regions'

export const STORE_TEAM_STATE = Object.freeze({
  UNVERIFIED: 0,
  ACTIVE: 1,
  JUMPER: 2,
})
export const store = Vue.observable({
  stores: [],
  metadata: {},
  storeMember: [],
  storeInformation: null,
  permissions: null,
  regionPickupRule: {},
  applications: [],
})

export const getters = {
  getAll () {
    return store.stores.length > 0 ? store.stores : []
  },

  getOthers () {
    const others = Array.from(store.stores).filter(s => !s.isManaging && s.membershipStatus === STORE_TEAM_STATE.ACTIVE)
    return others.length > 0 ? others : []
  },

  getManaging () {
    const managing = Array.from(store.stores).filter(s => s.isManaging)
    return managing.length > 0 ? managing : []
  },

  getJumping () {
    const jumping = Array.from(store.stores).filter(s => s.membershipStatus === STORE_TEAM_STATE.JUMPER)
    return jumping.length > 0 ? jumping : []
  },

  hasStores () {
    return store.stores.length > 0
  },

  getStoreCategoryTypes () {
    return store.metadata.categories ?? []
  },

  getStoreConvinceStatusTypes () {
    return store.metadata.convinceStatus ?? []
  },

  getStoreWeightTypes () {
    return store.metadata.weight ?? []
  },

  getStoreCooperationStatus () {
    return store.metadata.status ?? []
  },

  getGrocerieTypes () {
    return store.metadata.groceries ?? []
  },

  getStoreChains () {
    return store.metadata.storeChains ?? []
  },

  getPublicTimes () {
    return store.metadata.publicTimes ?? []
  },

  getMaxCountPickupSlot () {
    return store.metadata.maxCountPickupSlot ?? 0
  },

  has (id) {
    return store.stores.find(store => store.id === id)
  },
  getStoreMember () {
    return store.storeMember
  },
  getStoreInformation () {
    return store.storeInformation
  },
  getStorePermissions () {
    return store.permissions
  },
  getStoreRegionOptions () {
    return store.regionPickupRule
  },
  getStoreApplications () {
    return store.applications
  },
}

export const mutations = {
  async fetch (force = false) {
    if (!store.length || force) {
      store.stores = await listStoresForCurrentUser()
      store.metadata = await getStoreMetaData()
    }
  },
  async loadStoreMember (storeId) {
    store.storeMember = await getStoreMember(storeId)
  },
  async loadStoreInformation (storeId) {
    store.storeInformation = await getStoreInformation(storeId)
  },
  async loadPermissions (storeId) {
    store.permissions = await getStorePermissions(storeId)
  },
  async loadGetRegionOptions (regionId) {
    store.regionPickupRule = await getRegionOptions(regionId)
  },
  async loadStoreApplications (storeId) {
    store.applications = await listStoreTeamMembershipRequests(storeId)
  },
}

export default { store, getters, mutations }
