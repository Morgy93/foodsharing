import { defineStore } from 'pinia'
import { get } from '@/api/base'

export const useStoreStore = defineStore('store', {
  state: () => {
    return {
      /**
       * list of stores indexed by id
       */
      storeData: () => {},
      /**
       * list of relations that the current user has to certain stores
       */
      userRelations: null,
      regionId: null,
    }
  },
  getters: {
    /**
     * list of stores
     */
    stores: (state) => Object.values(state.storeData),
    /**
     * list of stores the current user has a relation to
     */
    userStores: (state) => state.stores.filter(store => state.userRelatedStoreIds.includes(store.id)),
    /**
     * list of stores within the region of this.regionId
     */
    regionStores: (state) => state.stores.filter(store => store.region.id === state.regionId),
    userRelatedStoreIds: (state) => {
      if (state.userRelations === null) {
        return []
      } else {
        return state.userRelations.map(relation => relation.id)
      }
    },
  },
  actions: {
    // todo: pulseError(this.$i18n('error_unexpected'))
    async fetchStoresForRegion (regionId = this.regionId) {
      const { stores } = await get(`/region/${regionId}/stores`)
      this.regionId = regionId
      this.addStores(stores)
    },
    async fetchStoresForCurrentUser () {
      const { stores } = await get('/user/current/stores/details')
      this.addStores(stores)
    },
    async fetchUserStoreRelations () {
      // todo: looks like here we are missing stores that we are member in but they don't cooperate
      this.userRelations = await get('/user/current/stores')
    },
    addStores (stores) {
      const patch = { ...this.storeData }
      stores.forEach(store => {
        patch[store.id] = store
      })
      this.$patch({
        storeData: patch,
      })
    },
  },
})

// todo: pulseError(this.$i18n('error_unexpected'))
