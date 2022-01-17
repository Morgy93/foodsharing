<template>
  <div id="map">
    <leaflet-map
      ref="leafletMap"
      :zoom="6"
      :center="[lat, lon]"
    />
    <MapControl />
  </div>
</template>

<script>
import LeafletMap from './LeafletMap'
import MapControl from '../../../../src/Modules/Map/components/MapControl'

export default {
  components: { LeafletMap, MapControl },
  props: {
    lat: { type: String, required: true },
    lon: { type: String, required: true },
  },
}
</script>

<style scoped>

</style>
