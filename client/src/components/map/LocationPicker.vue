<!-- Extension of the LeafletMap that contains a single marker for choosing a location.
  The chosen coordinates are emitted in a "coordinates-change" event. -->
<template>
  <LeafletMap
    ref="leafletMap"
    :zoom="zoom"
    :center="mapCenter"
    :map-height="mapHeight"
  >
    <l-marker
      ref="marker"
      :visible="positionSelected"
      :lat-lng="coordinates"
      :icon="leafletIcon"
    />
  </LeafletMap>
</template>

<script>
import LeafletMap from './LeafletMap'
import { LMarker } from 'vue2-leaflet'
import L from 'leaflet'
import 'leaflet.awesome-markers'
L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

export default {
  components: { LeafletMap, LMarker },
  props: {
    zoom: { type: Number, default: undefined },
    center: { type: Array, default: () => [] },
    mapHeight: { type: Number, default: undefined },
    initialCoordinates: { type: Array, default: () => [] },
    icon: {
      type: Object,
      default: () => ({ icon: 'users', markerColor: 'green' }),
    },
  },
  data () {
    const positionSelected = (this.initialCoordinates.length > 0)
    let coordinates = [0, 0]
    let mapCenter
    if (positionSelected) {
      mapCenter = coordinates = this.initialCoordinates
    }
    if (this.center.length > 0) {
      mapCenter = this.center
    }
    return {
      coordinates,
      positionSelected,
      mapCenter,
    }
  },
  computed: {
    leafletIcon () {
      return L.AwesomeMarkers.icon(this.icon)
    },
  },
  mounted () {
    // update the marker's location on click
    const map = this.$refs.leafletMap.getMapObject()
    map.on('click', (e) => {
      this.coords = [e.latlng.lat, e.latlng.lng]
      this.positionSelected = true
      this.$refs.marker.setLatLng(this.coords)
      this.$emit('coordinates-change', this.coords)
    })
  },
}
</script>
