<template>
  <div>
    <PickupModal
      modal-id="AddPickupModal"
      :store-id="storeId"
      :title="$i18n('store.enter_date')"
      :description="$i18n('store.enter_date_description')"
    />
  </div>
</template>

<script>
import PickupModal from './PickupModal.vue'

export default {
  components: { PickupModal },
  props: {
    storeId: { type: Number, required: true },
  },
}
</script>
