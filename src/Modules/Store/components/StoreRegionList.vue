<template>
  <div>
    <StoreListComponent
      :is-managing-enabled="isManagingEnabled"
      :stores="stores"
      :show-create-store="showCreateStore"
      :region-id="regionId"
      :region-name="regionName"
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
  props: {
    showCreateStore: { type: Boolean, default: false },
    regionId: { type: Number, default: 0 },
    regionName: { type: String, default: '' },
  },
  data () {
    return {
      isManagingEnabled: false,
    }
  },
  computed: {
    stores: () => storeStore.regionStores
  },
  async created () {
    showLoader()
    this.isBusy = true
    await storeStore.fetchStoresForRegion(this.regionId)
    console.log('stores: ', this.stores)
    this.isBusy = false
    hideLoader()
  },
}
</script>
