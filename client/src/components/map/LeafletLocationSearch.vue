<!-- Combines the LeafletLocationPicker with a text field that supports searching for addresses via geocoding. -->
<!-- eslint-disable vue/max-attributes-per-line -->
<!-- eslint-disable vue/singleline-html-element-content-newline -->
<template>
  <div class="bootstrap">
    <div class="alert alert-info">
      <i class="fas fa-info-circle" />
      {{ $i18n('addresspicker.infobox') }}
      <div v-if="additionalInfoText">
        <hr>
        <span v-html="additionalInfoText" />
      </div>
    </div>

    <b-form-input
      id="searchinput"
      v-model="searchInput"
      :placeholder="$i18n('addresspicker.placeholder')"
      class="mb-2"
    />
    <LeafletLocationPicker
      ref="locationPicker"
      :icon="icon"
      :coordinates.sync="coords"
      :zoom="zoom"
      :marker-draggable="true"
      @coordinates-changed="updateCoordinates"
    />

    <div v-if="showAddressFields">
      <b-form-group
        :label="$i18n('anschrift')"
        label-for="input-street"
        class="my-2"
      >
        <b-form-input
          id="input-street"
          ref="inputStreet"
          v-model="currentStreet"
          :disabled="false"
        />
      </b-form-group>
      <b-form-group
        :label="$i18n('plz')"
        label-for="input-postal"
        class="my-2"
      >
        <b-form-input
          id="input-postal"
          ref="inputPostal"
          v-model="currentPostal"
          class="my-2"
          :disabled="true"
        />
      </b-form-group>
      <b-form-group
        :label="$i18n('ort')"
        label-for="input-city"
        class="my-2"
      >
        <b-form-input
          id="input-city"
          ref="inputCity"
          v-model="currentCity"
          class="my-2"
          :disabled="true"
        />
      </b-form-group>
    </div>
  </div>
</template>

<script>
import { BFormGroup, BFormInput } from 'bootstrap-vue'
import { locale } from '@/helper/i18n'
import { isTest } from '@/helper/server-data'

import L from 'leaflet'
import LeafletLocationPicker from '@/components/map/LeafletLocationPicker'
import 'leaflet.awesome-markers'

import $ from 'jquery'
import 'corejs-typeahead'
import 'typeahead-address-photon'

L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

export default {
  name: 'LeafletLocationSearch',
  components: { BFormGroup, BFormInput, LeafletLocationPicker },
  props: {
    zoom: { type: Number, required: true },
    coordinates: { type: Object, required: true },
    postalCode: { type: String, default: '' },
    street: { type: String, default: '' },
    city: { type: String, default: '' },
    iconName: { type: String, default: 'smile' },
    iconColor: { type: String, default: 'orange' },
    showAddressFields: { type: Boolean, default: true },
    additionalInfoText: { type: String, default: null },
    doReverseGeocoding: { type: Boolean, default: true },
  },
  data () {
    return {
      icon: L.AwesomeMarkers.icon({ icon: this.iconName, markerColor: this.iconColor }),
      coords: this.coordinates,
      currentPostal: this.postalCode,
      currentStreet: this.street,
      currentCity: this.city,
      searchInput: null,
      geolocationSearchEngine: null,
    }
  },
  mounted () {
    // create the geocoding search engine
    this.geolocationSearchEngine = new window.PhotonAddressEngine({
      url: isTest ? '/mock/photon' : 'https://photon.komoot.io',
      formatResult: function (feature) {
        const prop = feature.properties
        return [prop.name || '', prop.street, prop.housenumber || '', prop.postcode, prop.city, prop.country].filter(Boolean).join(' ')
      },
      lang: locale,
    })

    // bind the search engine to the text field
    const searchpanel = $('#searchinput')
    searchpanel.typeahead({
      highlight: true,
      minLength: 3,
      hint: true,
    }, {
      displayKey: 'description',
      source: this.geolocationSearchEngine.ttAdapter(),
    })
    this.geolocationSearchEngine.bindDefaultTypeaheadEvent(searchpanel)

    // update the map when an address suggestion was selected
    $(this.geolocationSearchEngine).on('addresspicker:selected', this.updateMap)
  },
  methods: {
    updateCoordinates (coords) {
      // if the marker was dragged, we need to do reverse geocoding to find the address
      if (this.doReverseGeocoding) {
        this.geolocationSearchEngine.reverseGeocode([coords.lat, coords.lon])
      }
    },
    updateMap (event, searchResult) {
      this.searchInput = searchResult.description
      this.coords = { lat: searchResult.geometry.coordinates[1], lon: searchResult.geometry.coordinates[0] }

      // set the map to the new coordinates
      this.$refs.locationPicker.centerMapAndMarker(this.coords)
      if (searchResult.properties.extent) {
        const bounds = searchResult.properties.extent
        this.$refs.locationPicker.setBounds([bounds[1], bounds[0]], [bounds[3], bounds[2]])
      } else {
        this.$refs.locationPicker.setZoom(17)
      }

      // update the address data
      const prop = searchResult.properties
      if (prop.postcode) {
        this.currentPostal = prop.postcode
      }
      if (prop.city) {
        this.currentCity = prop.city
      }
      if (prop.street) {
        this.currentStreet = prop.street + (prop.housenumber ? ' ' + prop.housenumber : '')
      }

      this.$emit('address-change', this.coords, this.currentStreet, this.currentPostal, this.currentCity)
    },
  },
}
</script>

<style scoped>

</style>
