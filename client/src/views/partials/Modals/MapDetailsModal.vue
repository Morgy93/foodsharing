<template>
  <b-modal
    id="mapDetailsModal"
    ref="mapDetailsModal"
    centered
    :title="title"
  >
    {{ body }}
  </b-modal>
</template>

<script>
import DataMap from '@/stores/map'

export default {
  computed: {
    title () {
      return JSON.stringify(DataMap.getters.getMarkerModalData().id)
    },
    body () {
      return JSON.stringify(DataMap.getters.getMarkerModalData())
    },
  },
}
</script>
