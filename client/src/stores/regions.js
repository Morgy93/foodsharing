import Vue from 'vue'
import { joinRegion, listRegionChildren, listRegionMembers } from '@/api/regions'
import { url } from '@/helper/urls'

export const store = Vue.observable({
  regions: [],
  choosedRegionChildren: [],
  memberList: [],

})

export const getters = {
  get () {
    return store.regions
  },

  getChoosedRegionChildren () {
    return store.choosedRegionChildren
  },

  find (regionId) {
    return store.regions.find(region => region.id === regionId)
  },
  getMemberList () {
    return store.memberList
  },
}

export const mutations = {
  set (regions) {
    store.regions = regions
  },

  async fetchChoosedRegionChildren (regionId) {
    store.choosedRegionChildren = await listRegionChildren(regionId)
    return store.choosedRegionChildren
  },

  async joinRegion (regionId) {
    await joinRegion(regionId)
    document.location.href = url('relogin_and_redirect_to_url', url('region_forum', regionId))
  },
  async fetchMemberList (regionId) {
    store.memberList = await listRegionMembers(regionId)
  },
}

export default { store, getters, mutations }
