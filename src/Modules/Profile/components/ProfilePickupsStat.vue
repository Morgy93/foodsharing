<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('profile.pickups_stat.title') }}
      </div>
      <div>
        <p />
      </div>
      <div>
        <b-pagination
          v-model="currentPage"
          :total-rows="pickupsStatData.length"
          :per-page="perPage"
          aria-controls="pickupStat-table"
        />
        <b-table
          id="pickupStat-table"
          :current-page="currentPage"
          :per-page="perPage"
          :fields="fields"
          :items="pickupsStatData"
          :sort-by="sortBy"
          :sort-desc="sortDesc"
          striped
          hover
          small
          bordered
          responsive
        />
      </div>
    </div>
  </div>
</template>

<script>

import { BPagination, BTable } from 'bootstrap-vue'

export default {
  components: { BTable, BPagination },
  props: {
    pickupsStatData: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      sortBy: 'time',
      sortDesc: true,
      currentPage: 1,
      perPage: 14,
      fields: [
        {
          key: 'calenderWeek',
          label: this.$i18n('profile.pickups_stat.calenderWeek'),
          sortable: true,
        },
        {
          key: 'districtName',
          label: this.$i18n('profile.pickups_stat.districtName'),
          sortable: true,
        },
        {
          key: 'categorieName',
          label: this.$i18n('profile.pickups_stat.categorieName'),
          sortable: true,
        },
        {
          key: 'pickupAmount',
          label: this.$i18n('profile.pickups_stat.pickupAmount'),
          sortable: true,
        },
        {
          key: 'pickupCount',
          label: this.$i18n('profile.pickups_stat.pickupCount'),
          sortable: true,
        },
      ],
    }
  },
}
</script>
