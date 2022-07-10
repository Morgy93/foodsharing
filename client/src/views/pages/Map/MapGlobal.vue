<template>
  <div class="map-container">
    <div class="toolbar">
      <h2>{{ checkedFilters }}</h2>
      <label
        v-for="(filter, index) in filters"
        :key="index"
      >
        <input
          v-model="checkedFilters"
          type="checkbox"
          :value=" filter.filter || filter.name"
        >
        <i
          class="fas"
          :class="filter.icon"
        />
        {{ $i18n(`globals.type.${filter.name}`) }}
      </label>
    </div>
    <div
      v-if="loading"
      class="progress"
    >
      <i class="fas fa-spinner fa-spin" />
    </div>
    <div
      id="map-wrapper"
      class="map-wrapper"
    />
  </div>
</template>

<script>
import L, { latLng } from 'leaflet'
import 'leaflet/dist/leaflet.css'
// Custom marker icons and cluster
import 'leaflet.awesome-markers'
import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
// API
import { getMapMarkers } from '@/api/map'

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'
// DEFAULTS
export default {
  name: 'Map',
  data () {
    return {
      map: null,
      loading: false,
      progress: 0,
      cluster: null,
      markers: [],
      filters: [{
        name: 'baskets',
        icon: 'fa-shopping-basket',
        color: 'green',
      }, {
        name: 'communities',
        icon: 'fa-users',
        color: 'blue',
      }, {
        name: 'foodshare_points',
        filter: 'fairteiler',
        icon: 'fa-recycle',
        color: 'orange',
      }, {
        name: 'stores',
        filter: 'betriebe',
        icon: 'fa-shopping-cart',
        color: 'red',
      }],
      checkedFilters: [],
      options: {
        center: [52.519325, 13.392709],
        zoom: 12,
        minZoom: 6,
        zoomSnap: 0.5,
        zoomControl: false,
      },
      tiles: {
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      },
    }
  },
  watch: {
    async checkedFilters (val) {
      await this.fetchMarkers(val)
      // console.log('checkedFilters', val, this.markers.length)
    },
  },
  async mounted () {
    Promise.all([await this.initMap(), await this.fetchMarkers()])
    setTimeout(() => {
      this.map.invalidateSize(true)
    }, 100)
  },
  methods: {
    async initMap () {
      this.map = L.map('map-wrapper', this.options)
      L.tileLayer(this.tiles.url, { attribution: this.tiles.attribution }).addTo(this.map)
    },
    getMarkerIcon (type) {
      const filter = this.filters.find(filter => [filter.name, filter.filter].includes(type))
      if (filter) {
        return L.AwesomeMarkers.icon({ icon: filter.icon, markerColor: filter.color })
      }
      return L.AwesomeMarkers.icon({ icon: 'question', markerColor: 'black' })
    },
    async fetchMarkers (filters = [], states = []) {
      const temp = this.markers
      try {
        this.markers = []
        if (filters.length === 0) return this.markers
        this.loading = true
        const markers = await getMapMarkers(filters, states)
        Object.keys(markers).forEach(key => {
          this.markers.push(...markers[key].map(item => ({ ...item, type: key })))
        })
        this.markers = this.markers.map((marker) => L.marker(latLng(marker), {
          icon: this.getMarkerIcon(marker.type),
        }))
        if (!this.cluster) {
          this.cluster = L.markerClusterGroup({
            chunkedLoading: true,
            chunkProgress: this.updateProgressBar,
            chunkInterval: 100,
            maxClusterRadius: 100,
          })
          this.cluster.addLayers(this.markers)
          this.map.addLayer(this.cluster)
        }
      } catch (e) {
        console.error(e)
      } finally {
        if (this.markers.length > 0 && this.checkedFilters.length > 0) {
          this.cluster.clearLayers(this.markers)
          this.cluster.addLayers(this.markers)
        } else if (temp.length > 0) {
          this.cluster.clearLayers(temp)
        }
        this.loading = false
      }
    },
    updateProgressBar (processed, total, elapsed, layersArray) {
      console.log(processed, total, elapsed, layersArray)
      if (elapsed > 1000) {
        this.loading = true
        this.progress = Math.round(processed / total * 100)
      }
      if (processed === total) {
        this.loading = false
      }
    },
  },
}
</script>

<style lang="scss">
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
  top: 10rem;
  right: 1rem;
  z-index: 1030;
  padding: 2rem;
  background: var(--fs-color-background);
  border-radius: var(--border-radius);
  box-shadow: var(--fs-shadow);
}

.progress {
  font-size: 4rem;
  color: var(--fs-color-light);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  width: 100%;
  position: absolute;
  z-index: 1020;
  background-color: var(--fs-color-gray-alpha-50);
}

.map-wrapper {
  flex: 1;
  height: 100%;
}
</style>
