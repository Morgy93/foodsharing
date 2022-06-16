import $ from 'jquery'

import L from 'leaflet'
import 'leaflet.awesome-markers'
import 'leaflet.css-awesome-markers'
import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
import 'mapbox-gl-leaflet'
import 'mapbox-gl/dist/mapbox-gl.css'

import 'corejs-typeahead'
import 'css/typeahead.css'
import 'typeahead-address-photon'

import { initMap } from '@/mapUtils'
import { locale } from '@/i18n'

export let map
export let clusterGroup
let defaultMarker
$(() => {
  $('.vmap').each((i, el) => initializeMap(el))
})

export async function initializeMap (el, cb = null) {
  const mapOptions = $(el).data('options')
  if (!mapOptions) return console.error('map is missing data-options')

  const {
    center = { lat: 50.89, lon: 10.13 },
    zoom = 13,
    searchpanel = false,
    markers = [],
    defaultMarkerOptions,
  } = mapOptions

  defaultMarker = L.AwesomeMarkers.icon({
    icon: defaultMarkerOptions.icon,
    markerColor: defaultMarkerOptions.color,
    prefix: defaultMarkerOptions.prefix,
  })

  map = initMap(el, center, zoom)

  clearCluster()

  if (searchpanel) {
    initializeSearchpanel(searchpanel, cb)
  }

  markers.forEach(addMarker)

  commitCluster()
}

function initializeSearchpanel (searchpanel, cb = null) {
  const $searchpanel = $('#' + searchpanel)

  let marker

  const icon = L.AwesomeMarkers.icon(
    {
      icon: 'smile',
      markerColor: 'orange',
      prefix: 'fa',
    })

  const engine = new window.PhotonAddressEngine(
    {
      url: 'https://photon.komoot.io',
      formatResult: function (feature) {
        const prop = feature.properties
        const formatted = [prop.name || '', prop.street, prop.housenumber || '', prop.postcode, prop.city, prop.country].filter(Boolean).join(' ')
        return formatted
      },
      lang: locale,
    },
  )

  $searchpanel.typeahead(
    {
      highlight: true,
      minLength: 3,
      hint: true,
    },
    {
      displayKey: 'description',
      source: engine.ttAdapter(),
    })
  engine.bindDefaultTypeaheadEvent($searchpanel)

  $(engine).on('addresspicker:selected', (event, result) => {
    const latLng = L.latLng(result.geometry.coordinates[1], result.geometry.coordinates[0])

    if (marker) {
      marker.setLatLng(latLng)
    } else {
      marker = L.marker(latLng, { icon }).addTo(map)
    }
    if (result.properties.extent) {
      const b = result.properties.extent
      map.fitBounds([
        [b[1], b[0]],
        [b[3], b[2]],
      ])
    } else {
      map.setView(latLng, 15)
    }

    if (cb) {
      cb(result)
    }
  })
}

export function addMarker ({ id, lat, lng, click, icon = defaultMarker }) {
  clusterGroup.addLayer(L.marker(new L.LatLng(lat, lng), { id, click, icon }))
}

export function clearCluster () {
  if (clusterGroup && map) map.removeLayer(clusterGroup)
  clusterGroup = L.markerClusterGroup()
  clusterGroup.on('click', el => {
    const { click } = el.layer.options
    if (click) click()
  })
}

export function commitCluster () {
  map.addLayer(clusterGroup)
}
