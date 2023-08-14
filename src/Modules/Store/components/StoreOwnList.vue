<template>
  <div>
    <StoreListComponent
      :is-managing-enabled="isManagingEnabled"
      :stores="stores"
      :store-member-status="storeMemberStatus"
    />
  </div>
</template>

<script>
import StoreListComponent from './StoreListComponent.vue'
import { listStoresDetailsForCurrentUser } from '@/api/stores'
import DataStores from '@/stores/stores'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/helper/i18n'

export default {
  components: { StoreListComponent },
  data () {
    return {
      isManagingEnabled: true,
      stores: [],
    }
  },
  computed: {
    storeMemberStatus () {
      return [
        {
          list: DataStores.getters.getAll(),
        },
      ].filter(e => e.list.length > 0)
    },
  },
  async mounted () {
    console.log('mounted')
    showLoader()
    this.isBusy = true
    try {
      const values = await listStoresDetailsForCurrentUser()
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
