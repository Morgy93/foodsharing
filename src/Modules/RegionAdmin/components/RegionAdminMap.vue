<template>
  <leaflet-map
    ref="leafletMap"
    :zoom="9"
    :center="{lat: 52, lon: 9}"
  >
    <l-feature-group ref="storeMarkersGroup">
      <l-marker
        v-for="marker in storeMarkers"
        :key="marker.id"
        :lat-lng="[marker.lat, marker.lon]"
        :icon="storeIcon"
        :draggable="false"
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
  watch: {
    async storeMarkers () {
      await new Promise(resolve => setTimeout(resolve, 1000))

      // set the map's bounds to show all markers
      const bounds = this.$refs.storeMarkersGroup.mapObject.getBounds()
      this.$refs.leafletMap.setBounds([bounds.getNorthWest(), bounds.getSouthEast()])
    },
  },
}
</script>
