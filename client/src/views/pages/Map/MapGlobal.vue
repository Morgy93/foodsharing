<!-- Leaflet map component that can display vector or tile maps.
  The slot allows adding child components like markers. -->
<template>
  <div class="map-container">
    <div class="toolbar">
      <span>Center: {{ center }}</span>
      <span>Zoom: {{ zoom }}</span>
      <span>Bounds: {{ bounds }}</span>
    </div>
    <LMap
      ref="map"
      style="height: 100vh; width: 100%"
      :zoom="zoom"
      :min-zoom="5"
      :marker-zoom-animation="true"
      :fade-animation="true"
      :world-copy-jump="true"
      :center="center"
      @update:zoom="zoomUpdated"
      @update:center="centerUpdated"
      @update:bounds="boundsUpdated"
    >
      <LTileLayer
        :url="tiles.url"
        :attribution="tiles.attribution"
      />
      <LMarkerCluster>
        <LMarker
          v-for="(marker, index) in markersInBounds"
          :key="index"
          :lat-lng="getMarkerPosition(marker)"
          :icon="getMarkerIcon(marker.type)"
          :draggable="false"
        />
      </LMarkerCluster>
    </LMap>
  </div>
</template>

<script>
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet.awesome-markers'
import { MAP_ATTRIBUTION, MAP_RASTER_TILES_URL } from '@/consts'
import { getMapMarkers } from '@/api/map'

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

export default {
  props: {
    options: {
      type: Object,
      default: () => ({
        zoom: 10,
        center: [52.519325, 13.392709],
        bounds: null,
      }),
    },
  },
  data () {
    return {
      items: [],
      zoom: this.options.zoom,
      center: this.options.center,
      bounds: this.options.bounds,
      tiles: {
        attribution: MAP_ATTRIBUTION,
        url: MAP_RASTER_TILES_URL,
      },
      mapOptions: {
        zoomSnap: 0.5,
        preferCanvas: true,
      },
    }
  },
  computed: {
    markersInBounds () {
      return this.items?.filter(item => this.bounds?.contains(this.getMarkerPosition(item)))
    },
  },
  async mounted () {
    const map = this.getMapObject()
    setTimeout(() => {
      map.invalidateSize(true)
    }, 100)

    await this.fetchObjects()
  },
  methods: {
    /**
     * https://github.com/lennardv2/Leaflet.awesome-markers
     */
    getMarkerIcon (type) {
      switch (type) {
        case 'baskets':
          return L.AwesomeMarkers.icon({ icon: 'shopping-basket', markerColor: 'green' })
        case 'communities':
          return L.AwesomeMarkers.icon({ icon: 'users', markerColor: 'blue' })
        case 'fairteiler':
          return L.AwesomeMarkers.icon({ icon: 'recycle', markerColor: 'orange' })
        case 'betriebe':
          return L.AwesomeMarkers.icon({ icon: 'shopping-cart', markerColor: 'red' })
        default:
          return L.AwesomeMarkers.icon({ icon: 'question', markerColor: 'cadetblue' })
      }
    },
    async fetchObjects (filters = ['baskets', 'communities', 'fairteiler', 'betriebe'], states = ['allebetriebe']) {
      const markers = await getMapMarkers(filters, states)
      Object.keys(markers).forEach(key => {
        this.items.push(...markers[key].map(item => ({
          ...item,
          type: key,
        })))
      })
    },
    getMarkerPosition (marker) {
      return [marker?.lat, marker?.lon]
    },
    getMapObject () {
      return this.$refs.map.mapObject
    },
    zoomUpdated (zoom) {
      this.zoom = zoom
    },
    centerUpdated (center) {
      this.center = center
    },
    boundsUpdated (bounds) {
      this.bounds = bounds
    },
  },
}
</script>

<style>
.page-map {
  height: 100vh;
}
.map-container {
  position: absolute;
  top: 0;
  height: 100%;
  width: 100%;
}

.toolbar {
  flex: 0;
  position: absolute;
  top: 150px;
  z-index: 1030;
}

.mapwrapper {
  flex: 1;
  height: 100%;
}
</style>
