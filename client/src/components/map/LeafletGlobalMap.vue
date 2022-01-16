<template>
  <div id="map">
    {{ lat }} {{ lon }}
    <leaflet-map
      ref="leafletMap"
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
}
</script>

<style scoped>

</style>
