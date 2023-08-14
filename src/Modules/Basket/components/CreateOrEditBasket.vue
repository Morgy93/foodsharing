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
            type="text"
            class="form-control-sm"
          >
        </div>
        <label>Beschreibung</label>
        <b-form-textarea
          id="forum-create-thread-form-body"
          v-model="body"
          rows="6"
        />
        <button
          class="btn btn-primary"
        >
          {{ $i18n('upload.image') }}
        </button>
        <b-form-group
          v-slot="{ ariaDescribedby }"
          label="Individual radios"
        >
          <b-form-radio
            v-model="selected"
            :aria-describedby="ariaDescribedby"
            name="some-radios"
            value="A"
          >
            Option A
          </b-form-radio>
          <b-form-radio
            v-model="selected"
            :aria-describedby="ariaDescribedby"
            name="some-radios"
            value="B"
          >
            Option B
          </b-form-radio>
        </b-form-group>
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

export default {
  components: { LeafletLocationSearchVForm },
  data: function () {
    return {
      zoom: 17,
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
}
</script>

<style scoped>

</style>
