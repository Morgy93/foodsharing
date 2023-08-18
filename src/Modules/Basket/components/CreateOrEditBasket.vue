<template>
  <div class="card mb-3 rounded">
    <div
      class="card-header text-white bg-primary"
    >
      {{ $i18n('basket.add') }}
    </div>
    <div
      class="card-body p-0"
    />
    <div class="row m-2">
      <div class="col col-6">
        <div>
          <label>Titel</label>
          <input
            v-model="title"
            type="text"
            class="form-control-sm"
          >
        </div>
      </div>
      <div class="col col-6">
        <label>Abholadresse</label>
        <div class="d-flex">
          <input
            v-model="getAddress"
            type="text"
            :disabled="true"
          >
          <button
            class="btn"
            @click="$refs.LocationPickerModal.show()"
          >
            <i class="fas fa-pencil-alt" />
          </button>
        </div>
      </div>
    </div>
    <div class="row m-2">
      <div class="col col-6">
        <label>Beschreibung</label>
        <b-form-textarea
          id="forum-create-thread-form-body"
          v-model="description"
          rows="3"
        />
      </div>
      <div class="col col-6">
        <button
          class="btn btn-outline-primary"
        >
          {{ $i18n('upload.image') }}
        </button>
      </div>
    </div>
    <div class="row m-2">
      <div class="col col-6">
        <b-form-group
          v-slot="{ ariaDescribedby }"
          label="Kontakt per"
        >
          <b-form-radio
            v-for="contactType in contactTypes"
            :key="contactType.key"
            v-model="selectedContactType"
            :aria-describedby="ariaDescribedby"
            :name="contactType.label"
            :value="contactType.key"
          >
            {{ contactType.label }}
          </b-form-radio>
        </b-form-group>

        <div v-if="selectedContactType === 1">
          <label>Telefon</label>
          <VueTelInput
            :value="landlinePhoneNumber"
            :class="{ 'is-invalid': !isValid }"
            :valid-characters-only="validCharactersOnly"
            :mode="mode"
            :input-options="inputOptions"
            :default-country="defaultCountry"
            :preferred-countries="preferredCountries"
            @input="update"
            @validate="validate"
          />

          <label>Handy</label>
          <VueTelInput
            :value="mobilePhoneNumber"
            :class="{ 'is-invalid': !isValid }"
            :valid-characters-only="validCharactersOnly"
            :mode="mode"
            :input-options="inputOptions"
            :default-country="defaultCountry"
            :preferred-countries="preferredCountries"
            @input="update"
            @validate="validate"
          />
        </div>

        <b-form-group label="Wie lange soll dein Essenskorb gültig sein?">
          <b-form-select v-model="selectedDuration">
            <option
              v-for="(duration, index) in durationOptions"
              :key="index"
              :value="duration.days"
            >
              {{ duration.label }}
            </option>
          </b-form-select>
        </b-form-group>
        <button
          class="btn btn-primary"
          @click="tryAddBasket"
        >
          Veröffentlichen
        </button>
      </div>
      <div class="col col-6">
        <label>Geschätztes Gewicht (kg)</label>
        <input
          v-model="weight"
          type="number"
          class="form-control-sm"
        >
      </div>
    </div>
    <b-modal
      ref="LocationPickerModal"
      title="LocationPickerModal"
      :cancel-title="$i18n('button.cancel')"
      ok-title="Adresse übernehmen"
      cancel-variant="primary"
      ok-variant="outline-danger"
      @ok="onLocationPickerModalOk"
    >
      <leaflet-location-search
        id="location"
        :zoom="zoom"
        :coordinates="coordinates"
        :street="getUserDetails.street"
        :postal-code="getUserDetails.postalCode"
        :city="getUserDetails.city"
        @address-change="onAddressChangedInternal"
      />
    </b-modal>
  </div>
</template>

<script>
import DataUser from '@/stores/user'
import { addBasket } from '@/api/baskets'
import { VueTelInput } from 'vue-tel-input'
import 'vue-tel-input/dist/vue-tel-input.css'
import LeafletLocationSearch from '@/components/map/LeafletLocationSearch.vue'

export default {
  components: { LeafletLocationSearch, VueTelInput },
  data: function () {
    return {
      preferredCountries: ['DE', 'AT', 'CH'],
      validCharactersOnly: true,
      defaultCountry: 'DE',
      zoom: 17,
      contactTypes: [
        { key: 1, label: 'Telefon' },
        { key: 2, label: 'Nachricht' },
      ],
      durationOptions: [
        { days: 1, label: 'Ein Tag', value: '1 day' },
        { days: 2, label: 'Zwei Tage', value: '2 days' },
        { days: 3, label: 'Drei Tage', value: '3 days' },
        { days: 7, label: 'Eine Woche', value: '1 week' },
        { days: 14, label: 'Zwei Wochen', value: '2 weeks' },
        { days: 21, label: 'Drei Wochen', value: '3 weeks' },
      ],
      selectedDuration: 3,
      selectedContactType: null,
      title: null,
      description: null,
      landlinePhoneNumber: null,
      mobilePhoneNumber: null,
      weight: null,
      lat: null,
      lon: null,
      coordinates: null,
      address: null,
      city: null,
      postcode: null,
      selectedCoordinates: null,
      selectedAddress: null,
      selectedPostcode: null,
      selectedCity: null,
    }
  },
  computed: {
    getAddress () {
      return `${this.address} ${this.postcode} ${this.city}`
    },
    getLocations: () => DataUser.getters.getLocations(),
    getUserDetails: () => DataUser.getters.getUserDetails(),
  },
  async mounted () {
    await DataUser.mutations.fetchDetails()
    this.lat = this.getUserDetails.lat
    this.lon = this.getUserDetails.lon
    this.coordinates = this.getLocations
    this.address = this.getUserDetails.address
    this.city = this.getUserDetails.city
    this.postcode = this.getUserDetails.postcode
  },
  methods: {
    onAddressChanged (coordinates, street, postalCode, city) {
      this.coordinates = coordinates
      this.address = street
      this.postcode = postalCode
      this.city = city
    },
    onAddressChangedInternal (coordinates, street, postalCode, city) {
      this.selectedCoordinates = coordinates
      this.selectedAddress = street
      this.selectedPostcode = postalCode
      this.selectedCity = city
    },
    onLocationPickerModalOk () {
      this.onAddressChanged(this.selectedCoordinates, this.selectedAddress, this.selectedPostcode, this.selectedCity)
    },
    tryAddBasket () {
      try {
        addBasket(
          this.title,
          this.description,
          [this.selectedContactType],
          this.landlinePhoneNumber,
          this.mobilePhoneNumber,
          this.weight,
          this.selectedDuration,
          this.lat,
          this.lon,
        )
      } catch (e) {

      }
    },
  },
}
</script>

<style scoped>

</style>
