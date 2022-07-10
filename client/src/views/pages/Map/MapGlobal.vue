<template>
  <div class="map-container">
    <div class="toolbar">
      <div
        v-for="(filter, index) in filteredFilters"
        :key="index"
      >
        <input
          :id="filter.name"
          v-model="selectedFilters"
          class="toolbar-item-input"
          type="radio"
          :value=" filter.name"
        >
        <label
          :for="filter.name"
          class="toolbar-item"
          :class="filter.name"
        >
          <i
            class="toolbar-item-icon fas"
            :class="filter.icon"
          />
          <span
            class="toolbar-item-text"
            v-html="$i18n(`globals.type.${filter.name}`)"
          />
        </label>
      </div>
    </div>
    <div
      v-if="loading"
      class="progress"
    >
      <h3
        v-if="progress > 10"
        v-html="progress"
      />
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
// Store
import DataStore from '@/stores/user'
import DataMap from '@/stores/map'

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'
// DEFAULTS
export default {
  name: 'Map',
  data () {
    return {
      map: null,
      loading: false,
      progress: 0,
      bounds: null,
      cluster: null,
      markers: [],
      filters: [
        {
          name: 'baskets',
          icon: 'fa-shopping-basket',
          color: 'green',
        },
        {
          name: 'communities',
          icon: 'fa-users',
          color: 'blue',
        },
        {
          name: 'foodshare_points',
          icon: 'fa-recycle',
          color: 'orange',
        },
        {
          name: 'stores',
          icon: 'fa-shopping-cart',
          color: 'red',
          isForFoodsaver: true,
        },
      ],
      selectedFilters: null,
      options: {
        center: [51, 10],
        zoom: 12,
        minZoom: 6,
        zoomSnap: 0.5,
        zoomControl: false,
      },
      tiles: {
        // http://leaflet-extras.github.io/leaflet-providers/preview/index.html
        // url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
        // attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community',
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      },
    }
  },
  computed: {
    filteredFilters () {
      return this.filters.filter(filter => {
        if (filter?.isForFoodsaver) {
          return DataStore.getters.isFoodsaver()
        }
        return true
      })
    },
  },
  watch: {
    async selectedFilters (val) {
      await this.fetchMarkers(val)
    },
    bounds: {
      handler (val) {
        this.renderMarkers()
      },
    },
  },
  async mounted () {
    Promise.all([await this.setInitialMap(), await this.fetchMarkers()])
    await this.setInitialView()
    setTimeout(() => this.map.invalidateSize(true), 100)

    this.map.on('moveend', (e) => {
      this.bounds = this.map.getBounds()
    })
  },
  methods: {
    async setInitialView () {
      const userPosition = DataStore.getters.getLocations()
      if (userPosition) {
        this.moveViewToPosition(latLng(userPosition))
      } else {
        try {
          if (navigator.geolocation) {
            this.moveViewToPosition(latLng(await DataStore.getters.getBrowserLocations()))
          } else {
            this.moveViewToPosition(latLng(this.options.center), 5)
          }
        } catch (_) {
          this.moveViewToPosition(latLng(this.options.center), 5)
        }
      }
      this.bounds = this.map.getBounds()
    },
    async moveViewToPosition (val, zoom = 12) {
      this.map.setView(val, zoom, { animation: true })
    },
    async setInitialMap () {
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
    async renderMarkers () {
      let markers = []
      try {
        markers = this.markers || []
        markers = markers.filter(marker => this.bounds.contains(latLng(marker.lat, marker.lon)))
        markers = markers.map((marker) => L.marker(latLng(marker.lat, marker.lon), {
          icon: this.getMarkerIcon(marker.type),
        }))

        if (!this.cluster) {
          this.cluster = L.markerClusterGroup({
            chunkedLoading: true,
            chunkProgress: this.updateProgressBar,
            chunkInterval: 100,
            maxClusterRadius: 100,
          })
          this.cluster.addLayers(markers)
          this.map.addLayer(this.cluster)
        }
      } catch (e) {
        console.error(e)
      } finally {
        if (markers.length > 0 && this.cluster) {
          this.cluster.clearLayers()
        }

        if (markers.length > 0) {
          this.cluster.addLayers(markers)
        }

        if (markers.length === 0) {
          this.loading = false
        }
      }
    },
    async fetchMarkers (type, states = []) {
      try {
        this.progress = 0
        this.loading = true
        await DataMap.mutations.fetchByType(type, states)
        this.markers = await DataMap.getters.getMarkers(type, states)
        this.renderMarkers()
      } catch (e) {
        console.error(e)
      }
    },
    updateProgressBar (processed, total, elapsed) {
      this.progress = Math.round(processed / total * 100)
      if (elapsed > 1) {
        this.loading = true
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
  display: block;
  position: absolute;
  top: 10rem;
  right: 1rem;
  z-index: 1020;
  padding: 0.85rem 0.8rem;
  background: var(--fs-color-background);
  border-radius: var(--border-radius);
  box-shadow: var(--fs-shadow);
}

.toolbar-item-input {
  display: none;
}

  .toolbar-item-icon {
    color: currentColor;
    font-size: 1rem;
    margin-left: .5rem;
    margin-right: 1rem;
    position: relative;

    &::before {
      position: relative;
      z-index: 2;
    }

    &::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: var(--size);
      height: var(--size);
      background-color: var(--fs-color-white);
      border-radius: 50%;
      z-index: 1;
      transform: translate(-50%, -50%);
  }
}

.toolbar-item  {
  display: flex;
  padding: 0.75rem 0.5rem;
  margin: 0.25rem;
  align-items: center;
  min-width: 12rem;
  font-weight: 600;
  font-size: 1rem;
  background-color: var(--fs-color-light);
  border-radius: var(--border-radius);
  border: 2px solid var(--fs-border-default);

  &:hover {
      &.baskets {
        border-color: var(--fs-color-secondary-400);
        background-color: var(--fs-color-secondary-200);
        color: var(--fs-color-type-baskets);
      }
      &.stores {
        border-color: var(--fs-color-danger-400);
        background-color: var(--fs-color-danger-200);
        color: var(--fs-color-type-stores);
      }
      &.foodshare_points {
        border-color: var(--fs-color-warning-400);
        background-color: var(--fs-color-warning-200);
        color: var(--fs-color-type-foodshare-points);
      }
      &.communities {
        border-color: var(--fs-color-info-400);
        background-color: var(--fs-color-info-200);
        color: var(--fs-color-type-communities);
      }
  }

  .toolbar-item-input:checked + &.baskets {
    background-color: var(--fs-color-type-baskets);
    border-color: var(--fs-color-secondary-600);
    color: var(--fs-color-light);
    &:hover {
      border-color: var(--fs-color-secondary-400);
      background-color: var(--fs-color-secondary-200);
      color: var(--fs-color-type-baskets);
    }
  }
  .toolbar-item-input:checked + &.stores {
    background-color: var(--fs-color-type-stores);
    border-color: var(--fs-color-danger-600);
    color: var(--fs-color-light);
    &:hover {
      border-color: var(--fs-color-danger-400);
      background-color: var(--fs-color-danger-200);
      color: var(--fs-color-type-stores);
    }
  }
  .toolbar-item-input:checked + &.foodshare_points {
    background-color: var(--fs-color-type-foodshare-points);
    border-color: var(--fs-color-warning-600);
    color: var(--fs-color-light);
    &:hover {
      border-color: var(--fs-color-warning-400);
      background-color: var(--fs-color-warning-200);
      color: var(--fs-color-type-foodshare-points);
    }
  }
  .toolbar-item-input:checked + &.communities {
    background-color: var(--fs-color-type-communities);
    border-color: var(--fs-color-info-600);
    color: var(--fs-color-light);
    &:hover {
      border-color: var(--fs-color-info-400);
      background-color: var(--fs-color-info-200);
      color: var(--fs-color-type-communities);
    }
  }
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
