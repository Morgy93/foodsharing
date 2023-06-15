import Vue from 'vue'
import { joinRegion, listRegionChildren } from '@/api/regions'
import { url } from '@/helper/urls'

export const store = Vue.observable({
  regions: [],
  choosedRegionChildren: [],

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
}

export default { store, getters, mutations }
