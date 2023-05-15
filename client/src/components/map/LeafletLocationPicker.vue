<!-- Extension of the LeafletMap that contains a single marker for marking or choosing a location. The marker can new made draggable.
  In this case, the chosen coordinates are emitted in a "coordinates-changed" event when dropping the marker. -->
<template>
  <leaflet-map
    ref="leafletMap"
    :zoom="zoom"
    :center="coordinates"
  >
    <l-marker
      ref="marker"
      :lat-lng="coordinates"
      :icon="icon"
      :draggable="markerDraggable"
      @dragend="onMarkerDragEnd"
    />
  </leaflet-map>
</template>

<script>
import LeafletMap from './LeafletMap'
import { LMarker } from 'vue2-leaflet'

export default {
  name: 'LeafletLocationPicker',
  components: { LeafletMap, LMarker },
  props: {
    zoom: { type: Number, required: true },
    coordinates: { type: Object, required: true },
    icon: { type: Object, required: true },
    markerDraggable: { type: Boolean, default: false },
  },
  methods: {
    /**
     * Sets the marker to new coordinates and centers the map on it.
     */
    centerMapAndMarker (coordinates) {
      this.$refs.leafletMap.centerMap(coordinates)
      this.$refs.marker.setLatLng(coordinates)
    },
    /**
     * Sets the map's boundaries to the rectangular spanned by the two coordinates.
     */
    setBounds (coordUpperLeft, coordLowerRight) {
      this.$refs.leafletMap.setBounds(coordUpperLeft, coordLowerRight)
    },
    /**
     * Sets the map's zoom to a new value.
     */
    setZoom (zoom) {
      this.$refs.leafletMap.setZoom(zoom)
    },
    onMarkerDragEnd (event) {
      const coords = event.target.getLatLng()
      this.$emit('coordinates-changed', { lat: coords.lat, lon: coords.lng })
    },
  },
}
</script>

<style scoped>

</style>
