<template>
  <leaflet-map
    ref="leafletMap"
    :zoom="4"
    :center="{lat: 52, lon: 9}"
    :height="400"
  >
    <l-feature-group
      ref="storeMarkersGroup"
    >
      <l-marker
        v-for="marker in storeMarkers"
        :key="marker.id"
        :lat-lng="[marker.lat, marker.lon]"
        :icon="storeIcon"
        :draggable="false"
        @ready="updateBounds"
      />
    </l-feature-group>
  </leaflet-map>
</template>

<script>
import LeafletMap from '@/components/map/LeafletMap'
import { LMarker, LFeatureGroup } from 'vue2-leaflet'
import L from 'leaflet'
import 'leaflet.awesome-markers'
L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

export default {
  components: { LeafletMap, LMarker, LFeatureGroup },
  props: {
    storeMarkers: { type: Array, default: () => [] },
  },
  data () {
    return {
      storeIcon: L.AwesomeMarkers.icon({ icon: 'shopping-cart', markerColor: 'darkred' }),
    }
  },
  methods: {
    updateBounds () {
      // set the map's bounds to show all markers
      const markerGroup = this.$refs.storeMarkersGroup.mapObject
      if (Object.keys(markerGroup.getLayers()).length > 0) {
        const bounds = markerGroup.getBounds()
        this.$refs.leafletMap.setBounds([bounds.getNorthWest(), bounds.getSouthEast()])
      }
    },
  },
}
</script>
