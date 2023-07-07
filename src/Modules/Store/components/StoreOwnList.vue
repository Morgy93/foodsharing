<template>
  <div>
    <StoreListComponent
      :is-managing-enabled="isManagingEnabled"
      :stores="stores"
    />
  </div>
</template>

<script>
import StoreListComponent from './StoreListComponent.vue'
import { hideLoader, showLoader } from '@/script'
import { useStoreStore } from '@/stores/store'

const storeStore = useStoreStore()

export default {
  components: { StoreListComponent },
  data () {
    return {
      isManagingEnabled: true,
    }
  },
  computed: {
    stores: () => storeStore.userStores
  },
  async created () {
    if (!this.stores.length) {
      showLoader()
      this.isBusy = true
      await Promise.all([
        storeStore.fetchUserStoreRelations(),
        storeStore.fetchStoresForCurrentUser()
      ])
      this.isBusy = false
      hideLoader()
    }
  },
}
</script>
