<template>
  <div id="map">
    <leaflet-map
      ref="leafletMap"
      :zoom="6"
      :center="[lat, lon]"
    >
      <l-marker
        ref="marker"
        :lat-lng="markers"
      />
    </leaflet-map>
    <MapControl />
  </div>
</template>

<script>
import LeafletMap from './LeafletMap'
import MapControl from '../../../../src/Modules/Map/components/MapControl'
import { getMapMarkers } from '@/api/map'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/i18n'
import { LMarker } from 'vue2-leaflet'

export default {
  components: { LeafletMap, MapControl, LMarker },
  props: {
    lat: { type: String, required: true },
    lon: { type: String, required: true },
    options: { type: String, default: null },
    markers: { type: Array, required: true },
  },
  data () {
    return {
      dataMarkers: this.markers,
    }
  },
  async mounted () {
    showLoader()
    this.isBusy = true
    try {
      const types = ['baskets']
      const jsonData = await getMapMarkers(types, null)
      for (const i in jsonData) { this.dataMarkers.push([i, jsonData[i]]) }
    } catch (e) {
      pulseError(i18n('error_unexpected'))
    }
    this.isBusy = false
    hideLoader()
  },
}
</script>

<style scoped>

</style>
