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
      <div class="col col-8">
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
        <LeafletLocationSearchVForm
          :coordinates="getLocations"
          :zoom="zoom"
          :postal-code="getUserDetails.postalCode"
          :street="getUserDetails.street"
          :city="getUserDetails.city"
        />
      </div>
    </div>
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
    getLocations: () => DataUser.getters.getLocations(),
    getUserDetails: () => DataUser.getters.getUserDetails(),

  },
}
</script>

<style scoped>

</style>
