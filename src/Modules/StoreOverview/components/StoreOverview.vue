<template>
  <div class="container bootstrap">
    <div class="card my-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ 'Terminübersicht der Betriebe' }}
      </div>
      <div class="card-body p-0 mt-1">
        <div class="form-row p-1 ">
          <div class="col-2 text-center">
            <label class=" col-form-label col-form-label-sm">
              Filtern nach...
            </label>
          </div>
          <div class="col-auto">
            <label>
              <input
                v-model="filterText"
                type="text"
                class="form-control form-control-sm"
                placeholder="Name/Adresse"
              >
            </label>
          </div>
          <div class="col-1">
            <button
              v-b-tooltip.hover
              type="button"
              class="btn btn-sm"
              :title="$i18n('storelist.emptyfilters')"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
          <div class="col-auto text-right">
            <b-form-checkbox
              v-model="status"
            >
              Nur meine verantwortlichen Betriebe anzeigen.
            </b-form-checkbox>
          </div>
        </div>
        <b-table
          striped
          hover
          :items="items"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      items: [
        { betrieb: 'Bäckerei Kraus', datum_1: 'X', datum_2: ' ', datum_3: 'O', datum_4: 'O' },
        { betrieb: 'Edeka', datum_1: 'O', datum_2: 'O', datum_3: ' ', datum_4: 'X' },
        { betrieb: 'CAP', datum_1: ' ', datum_2: 'X', datum_3: 'O', datum_4: ' ' },
        { betrieb: 'Rewe', datum_1: 'X', datum_2: ' ', datum_3: 'X', datum_4: 'X' }
      ]
    }
  }
}
</script>
