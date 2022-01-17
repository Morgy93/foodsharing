<template>
  <div id="map">
    <leaflet-map
      ref="leafletMap"
      :style="height"
      :zoom="6"
      :center="[lat, lon]"
    />
  </div>
</template>

<script>
import LeafletMap from './LeafletMap'

export default {
  components: { LeafletMap },
  props: {
    lat: { type: String, required: true },
    lon: { type: String, required: true },
  },
  data: function () {
    return {
      height: 'height: 100%',
    }
  },
}
</script>

<style scoped>

</style>
