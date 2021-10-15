<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('utilizationList.header_for_district', {bezirk: regionName}) }}
      </div>
      <div>
        <b-tabs
          pills
          card
        >
          <b-tab
            :title="$i18n('utilizationList.today_tab')"
            active
          >
            <b-pagination
              v-model="currentPageToday"
              :total-rows="utilizationDataTodayTab.length"
              :per-page="perPage"
              aria-controls="pickupToday-table"
            />
            <b-table
              id="pickupToday-table"
              :current-page="currentPageToday"
              :per-page="perPage"
              :fields="fields"
              :items="utilizationDataTodayTab"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              caption-top
            >
              <template
                slot="StoreName"
                slot-scope="row"
              >
                <a
                  :href="$url('store', row.item.id)"
                  class="ui-corner-all"
                >
                  {{ row.value }}
                </a>
              </template>
            </b-table>
          </b-tab>
          <b-tab
            :title="$i18n('utilizationList.tomorrow_tab')"
          >
            <b-pagination
              v-model="currentPageTomorrow"
              :total-rows="utilizationDataTomorrowTab.length"
              :per-page="perPage"
              aria-controls="pickupTomorrow-table"
            />
            <b-table
              id="pickupTomorrow-table"
              :current-page="currentPageTomorrow"
              :per-page="perPage"
              :fields="fields"
              :items="utilizationDataTomorrowTab"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              caption-top
            >
              <template
                slot="StoreName"
                slot-scope="row"
              >
                <a
                  :href="$url('store', row.item.id)"
                  class="ui-corner-all"
                >
                  {{ row.value }}
                </a>
              </template>
            </b-table>
          </b-tab>
          <b-tab
            :title="$i18n('utilizationList.dayaftertomorrow_tab')"
          >
            <b-pagination
              v-model="currentPageDayAfterTomorrow"
              :total-rows="utilizationDataDayAfterTomorrowTab.length"
              :per-page="perPage"
              aria-controls="pickupDayAfterTomorrow-table"
            />
            <b-table
              id="pickupDayAfterTomorrow-table"
              :current-page="currentPageDayAfterTomorrow"
              :fields="fields"
              :items="utilizationDataDayAfterTomorrowTab"
              :per-page="perPage"
              :sort-by="sortBy"
              :sort-desc="sortDesc"
              striped
              hover
              small
              caption-top
            >
              <template
                slot="StoreName"
                slot-scope="row"
              >
                <a
                  :href="$url('store', row.item.id)"
                  class="ui-corner-all"
                >
                  {{ row.value }}
                </a>
              </template>
            </b-table>
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>

import { BPagination, BTable, BTabs, BTab } from 'bootstrap-vue'

export default {
  components: { BTable, BTabs, BTab, BPagination },
  props: {
    regionName: {
      type: String,
      default: '',
    },
    utilizationDataTodayTab: {
      type: Array,
      default: () => [],
    },
    utilizationDataTomorrowTab: {
      type: Array,
      default: () => [],
    },
    utilizationDataDayAfterTomorrowTab: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      sortBy: 'Utilization',
      sortDesc: false,
      currentPageToday: 1,
      currentPageTomorrow: 1,
      currentPageDayAfterTomorrow: 1,
      perPage: 10,
      fields: {
        StoreName: {
          label: this.$i18n('utilizationList.storeName_table_header'),
          sortable: true,
        },
        NumberOfAppointments: {
          label: this.$i18n('pickuplist.NumberOfAppointments_table_header'),
          sortable: true,
        },
        MaxFetchSlot: {
          label: this.$i18n('utilizationList.MaxFetchSlot_table_header'),
          sortable: true,
        },
        OccupiedSlots: {
          label: this.$i18n('utilizationList.OccupiedSlots_table_header'),
          sortable: true,
        },
        Freeslots: {
          label: this.$i18n('utilizationList.Freeslots_table_header'),
          sortable: true,
        },
        Utilization: {
          label: this.$i18n('utilizationList.Utilization_table_header'),
          sortable: true,
        },
        NumberOfActiveMembers: {
          label: this.$i18n('utilizationList.NumberOfActiveMembers_table_header'),
          sortable: true,
        },
        NumberOfWaitingMembers: {
          label: this.$i18n('utilizationList.NumberOfWaitingMembers_table_header'),
          sortable: true,
        },

      },
    }
  },
}
</script>
