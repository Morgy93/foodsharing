<template>
  <div>
    <b-form-group
      label="Adress-/Standort-Suche"
      label-for="addresspicker"
      class="mb-4"
    >
      <div class="lat-lon-picker">
        <input
          id="addresspicker"
          ref="addresspicker"
          placeholder="Bitte hier die Adresse suchen. Falls nötig, danach unten korrigieren."
          type="text"
          value=""
          class="input text value ui-corner-top"
          autocomplete="off"
        >
      </div>
      <LeafletMap
        ref="leafletMap"
        :zoom="mapZoom"
        :center="mapCenter"
        :map-height="mapHeight"
      >
        <l-marker
          ref="marker"
          :visible="Boolean(positionSelected)"
          :lat-lng="coordinates"
          :icon="leafletIcon"
          draggable
          @dragend="handleMarkerDragged"
        />
      </LeafletMap>
    </b-form-group>
    <b-form-group
      label="Straße und Hausnummer (ggf. korrigieren)"
      label-for="input-adress"
      class="mb-4"
      :class="{
        'd-none': !positionSelected,
      }"
    >
      <b-form-input
        v-model="address"
        trim
        @input="emitChangeEvent"
      />
      <div
        class="invalid-feedback"
      >
        {{ $i18n('events.create.name.error') }}
      </div>
    </b-form-group>
  </div>
</template>

<script>
import { locale } from '@/helper/i18n'
import $ from 'jquery'
import L from 'leaflet'
import 'leaflet.awesome-markers'
import 'typeahead-address-photon'
import { LMarker } from 'vue2-leaflet'
import LeafletMap from './LeafletMap'

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

const engine = new window.PhotonAddressEngine(
  {
    url: 'https://photon.komoot.io',
    formatResult: function ({ properties: prop }) {
      return [prop.name, prop.street, prop.housenumber, ',', prop.postcode, prop.city, `(${prop.country})`]
        .filter(Boolean).join(' ').replace(' ,', ',')
    },
    lang: locale,
  },
)

export default {
  components: { LeafletMap, LMarker },
  props: {
    zoom: { type: Number, default: undefined },
    center: { type: Array, default: undefined },
    mapHeight: { type: Number, default: undefined },
    icon: {
      type: Object,
      default: () => ({ icon: 'users', markerColor: 'green' }),
    },
  },
  data () {
    return {
      coordinates: [0, 0],
      positionSelected: null,
      mapCenter: this.center,
      mapZoom: this.zoom,
      address: '',
    }
  },
  computed: {
    leafletIcon () {
      return L.AwesomeMarkers.icon(this.icon)
    },
  },
  mounted () {
    this.bindTypeahead($('#addresspicker'))
  },
  methods: {
    bindTypeahead (element) {
      element.typeahead(
        { highlight: true, minLength: 3, hint: false },
        { displayKey: 'description', source: engine.ttAdapter() },
      )
      engine.bindDefaultTypeaheadEvent(element)
      $(engine).bind('addresspicker:selected', this.handleSelectPlace)
    },
    handleSelectPlace (event, selectedPlace) {
      document.querySelector('#addresspicker').value = selectedPlace.description
      this.positionSelected = selectedPlace
      const prop = selectedPlace.properties
      this.coordinates = [...selectedPlace.geometry.coordinates].reverse()
      this.address = [prop.street, prop.housenumber].filter(Boolean).join(' ')
      this.setMapBounds(prop.extent)
      this.emitChangeEvent()
    },
    handleMarkerDragged (evt) {
      const pos = evt.target.getLatLng()
      engine.reverseGeocode([pos.lat, pos.lng])
    },
    emitChangeEvent () {
      const prop = this.positionSelected.properties
      let city = prop.city
      if (!city && ['Wien', 'Vienna'].includes(prop.state)) {
        city = prop.state
      }
      this.$emit('address-change', {
        coordinates: this.coordinates,
        zip: prop.postcode,
        city,
        address: this.address,
      })
    },
    setMapBounds (extent) {
      // Set map view to include the whole extent of the search result.
      // This way the map zoom is dependent on the size of the selected location.
      // Searching for an address results in a small sector, searching for a county in a larger one.
      this.$refs.leafletMap.getMapObject().fitBounds([
        extent.slice(0, 2).reverse(),
        extent.slice(2, 4).reverse(),
      ])
    },
  },
}
</script>

<style>
span.twitter-typeahead .tt-menu,
span.twitter-typeahead .tt-dropdown-menu {
  cursor: pointer;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1100 !important;
  display: none;
  float: left;
  min-width: 160px;
  padding: 5px 0;
  margin: 2px 0 0;
  list-style: none;
  font-size: 14px;
  text-align: left;
  background-color: var(--fs-color-light);
  border: 1px solid var(--fs-color-gray-200);
  border: 1px solid var(--fs-color-gray-alpha-10);
  border-radius: 4px;
  -webkit-box-shadow: 0 6px 12px var(--fs-color-gray-alpha-20);
  box-shadow: 0 6px 12px var(--fs-color-gray-alpha-20);
  background-clip: padding-box;
}
span.twitter-typeahead .tt-suggestion {
  display: block;
  padding: 3px 20px;
  clear: both;
  font-weight: normal;
  line-height: 1.42857143;
  color: var(--fs-color-dark);
  white-space: nowrap;
}
span.twitter-typeahead .tt-suggestion.tt-cursor,
span.twitter-typeahead .tt-suggestion:hover,
span.twitter-typeahead .tt-suggestion:focus {
  color: var(--fs-color-light);
  text-decoration: none;
  outline: 0;
  background-color: var(--fs-color-info-500);
}
.input-group.input-group-lg span.twitter-typeahead .form-control {
  height: 46px;
  padding: 10px 16px;
  font-size: 18px;
  line-height: 1.3333333;
  border-radius: 6px;
}
.input-group.input-group-sm span.twitter-typeahead .form-control {
  height: 30px;
  padding: 5px 10px;
  font-size: 12px;
  line-height: 1.5;
  border-radius: 3px;
}
span.twitter-typeahead {
  width: 100%;
}
.input-group span.twitter-typeahead {
  display: block !important;
  height: 34px;
}
.input-group span.twitter-typeahead .tt-menu,
.input-group span.twitter-typeahead .tt-dropdown-menu {
  top: 32px !important;
}
.input-group span.twitter-typeahead:not(:first-child):not(:last-child) .form-control {
  border-radius: 0;
}
.input-group span.twitter-typeahead:first-child .form-control {
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
.input-group span.twitter-typeahead:last-child .form-control {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
}
.input-group.input-group-sm span.twitter-typeahead {
  height: 30px;
}
.input-group.input-group-sm span.twitter-typeahead .tt-menu,
.input-group.input-group-sm span.twitter-typeahead .tt-dropdown-menu {
  top: 30px !important;
}
.input-group.input-group-lg span.twitter-typeahead {
  height: 46px;
}
.input-group.input-group-lg span.twitter-typeahead .tt-menu,
.input-group.input-group-lg span.twitter-typeahead .tt-dropdown-menu {
  top: 46px !important;
}

.lat-lon-picker .twitter-typeahead {
  margin: 0 0 10px 0;
  position: relative;
}
.lat-lon-picker .twitter-typeahead input.input {
  padding: 10px;
  width: -moz-available;
  width: -webkit-fill-available;
}
.lat-lon-picker .twitter-typeahead::after {
  position: absolute;
  right: 5px;
  top: 0;
  bottom: 0;
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  display: inline;
  font-style: normal;
  font-variant: normal;
  text-rendering: auto;
  font-size: 1.5em;
  color: var(--fs-color-secondary-500);
  padding: 0 4px;
  content: '\f3c5'; /* fa-map-marker-alt */
  line-height: 2em;
}

</style>
