import Vue from 'vue'
import { joinRegion, listRegionChildren } from '@/api/regions'
import { url } from '@/urls'

export const store = Vue.observable({
  regions: [],
  choosedRegionChildren: [],
  joinRegionModal: {
    isShown: false,
  },
})

export const getters = {
  get () {
    return store.regions
  },

  getChoosedRegionChildren () {
    return store.choosedRegionChildren
  },

  joinRegionModal: {
    isShown () {
      return store.joinRegionModal.isShown
    },
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

  joinRegionModal: {
    show () {
      store.joinRegionModal.isShown = true
    },
    close () {
      store.joinRegionModal.isShown = false
    },
  },
}

export default { store, getters, mutations }
