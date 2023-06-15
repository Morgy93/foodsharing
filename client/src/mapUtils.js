import L from 'leaflet'
// import 'mapbox-gl-leaflet'

import { MAP_RASTER_TILES_URL, MAP_ATTRIBUTION } from '@/consts'
import { isWebGLSupported } from '@/utils'

/**
 * @deprecated use the Vue component @/components/map/LeafletMap instead
 */
export function initMap (element, center, zoom, maxZoom = 20) {
  const map = L.map(element, {}).setView(center, zoom)

  if (isWebGLSupported()) {
    // L.mapboxGL({
    //   style: MAP_TILES_URL,
    // }).addTo(map)
  } else {
    // WebGL is not supported, fallback to raster tiles
  }
  L.tileLayer(MAP_RASTER_TILES_URL, { maxZoom: maxZoom }).addTo(map)
  map.attributionControl.setPrefix(MAP_ATTRIBUTION)

  return map
}
