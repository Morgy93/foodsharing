<!-- Leaflet map component that can display vector or tile maps.
  The slot allows adding child components like markers. -->
<template>
  <div>
    <l-map
      ref="map"
      :style="`height: ${mapHeight}px`"
      :zoom="zoom"
      :center="center"
      @click="clickHandler"
    >
      <l-tile-layer
        :url="tileUrl"
        :attribution="attribution"
      />
      <slot />
    </l-map>
  </div>
</template>

<script>
import { LMap, LTileLayer } from 'vue2-leaflet'
import 'leaflet/dist/leaflet.css'
import { MAP_ATTRIBUTION, MAP_RASTER_TILES_URL } from '@/consts'

export default {
  name: 'LeafletMap',
  components: { LMap, LTileLayer },
  props: {
    zoom: { type: Number, default: 5 },
    center: { type: Array, default: () => [51, 11] },
    mapHeight: { type: Number, default: 300 },
  },
  data () {
    return {
      attribution: MAP_ATTRIBUTION,
      tileUrl: MAP_RASTER_TILES_URL,
    }
  },
  mounted () {
    window.setTimeout(this.reloadMap, 400)
  },
  methods: {
    /**
     * Returns leaflet's internal map object.
     */
    getMapObject () {
      return this.$refs.map.mapObject
    },
    reloadMap () {
      console.log('test')
      this.getMapObject().invalidateSize()
    },
    clickHandler (e) {
      console.log(e)
    },
  },
}
</script>

<style scoped>

</style>
