<template>
  <div>
    <PickupModal
      modal-id="DeletePickupModal"
      :store-id="storeId"
      :title="$i18n('store.delete_date')"
      :delete-pickup-mode="true"
      :description="$i18n('store.delete_date_description')"
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
