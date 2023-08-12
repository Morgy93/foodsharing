/* eslint-disable camelcase,eqeqeq */

import '@/core'
import '@/globals'

import $ from 'jquery'

import { getBrowserLocation, expose } from '@/utils'
import { GET } from '@/browser'

import { showLoader, hideLoader, goTo, ajreq } from '@/script'

import storage from '@/storage'

import { initMap } from '@/mapUtils'

import L from 'leaflet'

import 'leaflet.awesome-markers'
import 'leaflet.markercluster'
// import 'mapbox-gl-leaflet'

// import 'mapbox-gl/dist/mapbox-gl.css'
import './Map.css'
import { getMapMarkers } from '@/api/map'
import { findStores, myStores } from '@/api/stores'
import { vueApply, vueRegister } from '@/vue'
import CommunityBubble from './components/CommunityBubble'

let u_map = null
let markers = null

expose({
  u_map,
  u_init_map,
  u_loadDialog,
})

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

const bkIcon = L.AwesomeMarkers.icon({
  icon: 'shopping-basket',
  markerColor: 'green',
})
const bIcon = L.AwesomeMarkers.icon({
  icon: 'shopping-cart',
  markerColor: 'darkred',
})
const fIcon = L.AwesomeMarkers.icon({
  icon: 'recycle',
  markerColor: 'beige',
})

const comIcon = L.AwesomeMarkers.icon({
  icon: 'users',
  markerColor: 'blue',
})

const map = {
  initiated: false,
  init: function () {
    const center = storage.get('map:center', [50.89, 10.13])
    const zoom = storage.get('map:zoom', 6)
    u_map = initMap('map', center, zoom)

    expose({ u_map }) // need to re-expose it as it is just a variable

    this.initiated = true

    u_map.on('dragend', function (e) {
      map.updateStorage()
    })

    u_map.on('zoomend', function (e) {
      map.updateStorage()
    })
  },
  initMarker: function (items) {
    $('#map-control .linklist a').removeClass('active')
    if (items == undefined) {
      if ($('#map-control .foodsaver').length > 0) {
        items = ['betriebe']
      } else {
        items = ['fairteiler', 'baskets', 'communities']
      }

      if (GET('load') == undefined) {
        items = storage.get('map:activeItems', items)
      }
    }
    for (let i = 0; i < items.length; i++) {
      $(`#map-control .linklist a.${items[i]}`).addClass('active')
    }

    loadMarker(items)
  },
  updateStorage: function () {
    const center = u_map.getCenter()
    const zoom = u_map.getZoom()

    const activeItems = []
    $('#map-control .linklist a.active').each(function () {
      activeItems.push($(this).attr('name'))
    })

    storage.set('map:center', [center.lat, center.lng])
    storage.set('map:zoom', zoom)
    storage.set('map:activeItems', activeItems)
  },
  setView: function (lat, lon, zoom) {
    if (!this.initiated) {
      this.init()
    }
    u_map.setView([lat, lon], zoom, { animation: true })
  },
}

expose({ map })

function u_init_map (lat, lon, zoom) {
  map.init()
  if (lat == undefined && storage.get('map:center') == undefined) {
    getBrowserLocation(pos => map.setView(pos.lat, pos.lon, 12))
  }
}

function u_loadDialog (purl) {
  $('#b_content').addClass('loading')
  $('#b_content').dialog('option', 'title', 'lade...')
  $('#b_content').dialog('open')
  const pos = $('#topbar .container').offset()
  $('#b_content').parent().css({
    left: `${pos.left}px`,
    top: '80px',
  })

  if (purl != undefined) {
    $.ajax({
      url: purl,
      dataType: 'json',
      success: function (data) {
        if (data.status === 1) {
          u_setDialogData(data)
        } else {
          $('#b_content').removeClass('loading')
        }
      },
    })
  }
}

function u_setDialogData (data) {
  $('#b_content .inner').html(data.html)
  $('#b_content').dialog('option', 'title', data.betrieb.name)
  $('#b_content').removeClass('loading')
  $('#b_content .lbutton').button()
}

function init_bDialog () {
  $('#b_content').dialog({
    autoOpen: false,
    modal: false,
    draggable: false,
    resizable: false,
  })
}

function collectStoreOptions () {
  const options = []
  $('#map-options input:checked').each(function () {
    options[options.length] = $(this).val()
  })
  return options
}

function showBubble (el) {
  const id = (el.layer.options.id)
  const type = el.layer.options.type

  if (type === 'bk') {
    ajreq('bubble', { app: 'basket', id: id })
  } else if (type === 'b') {
    ajreq('bubble', { app: 'store', id: id })
  } else if (type === 'f') {
    const bid = (el.layer.options.bid)
    goTo(`/?page=fairteiler&sub=ft&bid=${bid}&id=${id}`)
  } else if (type === 'c') {
    ajreq('bubble', { app: 'bezirk', id: id }).then(x => {
      vueApply('#community-bubble')
    })
  }
}

function prepareStoreTeamStatus (status) {
  const teamStatus = []
  if (status.includes('needhelp')) {
    teamStatus.push(1)
  }
  if (status.includes('needhelpinstant')) {
    teamStatus.push(2)
  }
  if (teamStatus.length !== 0) {
    return [`q[]=teamStatus:in:${teamStatus.join(',')}`]
  }
  return []
}

function prepareStoreCooperationStatus (status) {
  if (status.includes('nkoorp')) {
    const status = [0, 1, 2, 4, 6, 7]
    return [`q[]=cooperationStatus:in:${status.join(',')}`]
  }
  return []
}

async function loadMarker (types, loader) {
  $('#map-options').hide()

  if (loader == undefined) {
    loader = true
  }

  if (loader) {
    showLoader()
  }

  try {
    const data = await getMapMarkers(types.filter(item => item !== 'betriebe'), [])
    if (markers != null) {
      u_map.removeLayer(markers)
    }

    markers = L.markerClusterGroup({ maxClusterRadius: 50 })
    markers.on('click', showBubble)

    if (data.baskets != undefined) {
      $('#map-control li a.baskets').addClass('active')
      for (const a of data.baskets) {
        const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: bkIcon, type: 'bk' })
        markers.addLayer(marker)
      }
    }

    const containsStores = types.includes('betriebe')
    if (containsStores) {
      const storesOptions = collectStoreOptions()
      const teamQueries = prepareStoreTeamStatus(storesOptions)
      const queries = teamQueries.concat(prepareStoreCooperationStatus(storesOptions))
      const storeData = storesOptions.includes('mine') ? await myStores(queries) : await findStores(queries)
      $('#map-options').show()
      $('#map-control li a.betriebe').addClass('active')
      for (const a of storeData.stores) {
        const loc = a.location
        const marker = L.marker(new L.LatLng(loc.lat, loc.lon), { id: a.id, icon: bIcon, type: 'b' })
        markers.addLayer(marker)
      }
    }

    if (data.fairteiler != undefined) {
      $('#map-control li a.fairteiler').addClass('active')
      for (const a of data.fairteiler) {
        const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, bid: a.regionId, icon: fIcon, type: 'f' })
        markers.addLayer(marker)
      }
    }

    if (data.communities != undefined) {
      $('#map-control li a.communities').addClass('active')
      for (const a of data.communities) {
        const marker = L.marker(new L.LatLng(a.lat, a.lon), { id: a.id, icon: comIcon, type: 'c' })
        markers.addLayer(marker)
      }
    }

    u_map.addLayer(markers)
  } catch (e) {
    console.error(e)
    u_map.removeLayer(markers)
  }
  hideLoader()
}

showLoader()
$('#map-control li a').on('click', function () {
  $(this).toggleClass('active')

  const types = []
  let i = 0
  $('#map-control li a.active').each(function (el) {
    types[i] = $(this).attr('name')
    i++
  })
  loadMarker(types)
  map.updateStorage()
  return false
})

$('#map-control-colapse').on('click', function () {
  $('#map-legend').toggleClass('colapsed')
})

$('#map-options input').on('change', function () {
  if ($(this).val() === 'allebetriebe') {
    $('#map-options input').prop('checked', false)
    $('#map-options input[value=\'allebetriebe\']').prop('checked', true)
  } else {
    $('#map-options input[value=\'allebetriebe\']').prop('checked', false)
  }
  if ($('#map-options input:checked').length === 0) {
    $('#map-options input[value=\'allebetriebe\']').prop('checked', true)
  }

  const types = []
  let i = 0
  $('#map-control li a.active').each(function (el) {
    types[i] = $(this).attr('name')
    i++
  })
  setTimeout(function () {
    loadMarker(types)
  }, 100)
})

init_bDialog()

vueRegister({
  CommunityBubble,
})
