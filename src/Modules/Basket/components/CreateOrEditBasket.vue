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
            :name="contactType.key"
            :value="contactType.key"
          >
            {{ contactType.label }}
          </b-form-radio>
        </b-form-group>

        <div v-if="selectedContactType === 'phone'">
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
          type="text"
          class="form-control-sm"
        >
      </div>
    </div>
    <b-modal
      ref="LocationPickerModal"
      title="LocationPickerModal"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      cancel-variant="primary"
      ok-variant="outline-danger"
      @ok="deleteThread"
    >
      <LeafletLocationSearchVForm
        :coordinates="getLocations"
        :zoom="zoom"
        :postal-code="getUserDetails.postalCode"
        :street="getUserDetails.street"
        :city="getUserDetails.city"
      />
    </b-modal>
  </div>
</template>

<script>
import LeafletLocationSearchVForm from '@/components/map/LeafletLocationSearchVForm'
import DataUser from '@/stores/user'
import { addBasket } from '@/api/baskets'
import { VueTelInput } from 'vue-tel-input'
import 'vue-tel-input/dist/vue-tel-input.css'

export default {
  components: { LeafletLocationSearchVForm, VueTelInput },
  data: function () {
    return {
      preferredCountries: ['DE', 'AT', 'CH'],
      validCharactersOnly: true,
      defaultCountry: 'DE',
      zoom: 17,
      contactTypes: [
        { key: 'phone', label: 'Telefon' },
        { key: 'message', label: 'Nachricht' },
      ],
      durationOptions: [
        { days: 1, label: 'Ein Tag', value: '1 day' },
        { days: 2, label: 'Zwei Tage', value: '2 days' },
        { days: 3, label: 'Drei Tage', value: '3 days' },
        { days: 7, label: 'Eine Woche', value: '1 week' },
        { days: 14, label: 'Zwei Wochen', value: '2 weeks' },
        { days: 21, label: 'Drei Wochen', value: '3 weeks' },
      ],
      selectedDuration: null,
      selectedContactType: null,
      title: null,
      description: null,
      landlinePhoneNumber: null,
      mobilePhoneNumber: null,
    }
  },
  computed: {
    getAddress () {
      return `${this.getUserDetails.address} ${this.getUserDetails.postcode} ${this.getUserDetails.city}`
    },
    getLocations: () => DataUser.getters.getLocations(),
    getUserDetails: () => DataUser.getters.getUserDetails(),
  },
  async mounted () {
    await DataUser.mutations.fetchDetails()
  },
  methods: {
    tryAddBasket () {
      try {
        addBasket(this.title, this.description, this.selectedContactType)
      } catch (e) {

      }
    },
  },
}
</script>

<style scoped>

</style>
