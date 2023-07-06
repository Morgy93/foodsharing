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
    console.log(storeStore, this.stores)
    setTimeout(() => {
    console.log(storeStore, this.stores)
    }, 2500)
    if (!this.stores.length) {
      console.log('fetch stores')
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
