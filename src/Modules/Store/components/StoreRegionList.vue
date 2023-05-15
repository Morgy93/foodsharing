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
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/helper/i18n'
import { listRegionStores } from '@/api/regions'

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
      stores: [],
    }
  },
  async mounted () {
    console.log('mounted')
    showLoader()
    this.isBusy = true
    try {
      const values = await listRegionStores(this.regionId)
      this.stores = values.stores
      console.log('stores: ', this.stores)
    } catch (e) {
      pulseError(i18n('error_unexpected'))
    }
    this.isBusy = false
    hideLoader()
  },
}
</script>
