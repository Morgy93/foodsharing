<template>
  <div class="card mb-3 rounded">
    <div class="card-header text-white bg-primary">
      <span
        v-if="isManagingEnabled"
      >
        {{ $i18n('store.ownStores') }}
      </span>
      <span
        v-else
      >
        {{ $i18n('store.allStoresOfRegion') }} {{ regionName }}
      </span>
      <span>
        {{ $i18n('filterlist.some_in_all', {some: storesFiltered.length, all: stores.length}) }}
      </span>
    </div>
    <div
      v-if="stores.length"
      class="card-body p-0"
    >
      <div class="form-row p-1 ">
        <div class="col-2 text-center">
          <label class=" col-form-label col-form-label-sm">
            {{ $i18n('store.filter') }}
          </label>
        </div>
        <div class="col-4">
          <label>
            <input
              v-model="filterText"
              type="text"
              class="form-control form-control-sm"
              placeholder="Name/Adresse"
            >
          </label>
        </div>
        <div class="col-3">
          <b-form-select
            v-model="filterStatus"
            :options="statusOptions"
            size="sm"
          />
        </div>
        <div class="col">
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
        <div
          v-if="showCreateStore"
          :regionId="regionId"
          class="col"
        >
          <a
            :href="$url('storeAdd', regionId)"
            class="btn btn-sm btn-primary btn-block"
          >
            {{ $i18n('store.addNewStoresButton') }}
          </a>
        </div>
      </div>
      <b-table
        id="store-list"
        :fields="fieldsFiltered"
        :current-page="currentPage"
        :per-page="perPage"
        :sort-by.sync="sortBy"
        :sort-desc.sync="sortDesc"
        :items="storesFiltered"
        small
        hover
        responsive
      >
        <template
          #cell(cooperationStatus)="row"
          :v-if="isMobile"
        >
          <div class="text-center">
            <StoreStatusIcon :cooperation-status="row.value" />
          </div>
        </template>
        <template
          v-if="isManagingEnabled"
          #cell(isManaging)="row"
        >
          <i
            v-if="isManaging(row.item)"
            class="fas fa-users-cog"
          />
        </template>
        <template
          #cell(name)="row"
        >
          <a
            :href="$url('store', row.item.id)"
            class="ui-corner-all"
          >
            {{ row.value }}
          </a>
        </template>
        <template
          #cell(region)="row"
        >
          {{ row.value.name }}
        </template>
        <template
          #cell(actions)="row"
        >
          <b-button
            size="sm"
            @click.stop="row.toggleDetails"
          >
            {{ row.detailsShowing ? 'x' : 'Details' }}
          </b-button>
        </template>
        <template
          #row-details="row"
        >
          <b-card>
            <div class="details">
              <p>
                <strong>{{ $i18n('storelist.addressdata') }}</strong><br>
                {{ row.item.street }} <a
                  :href="mapLink(row.item)"
                  class="nav-link details-nav"
                  :title="$i18n('storelist.map')"
                >
                  <i class="fas fa-map-marker-alt" />
                </a><br> {{ row.item.zipCode }} {{ row.item.city }}
              </p>
              <p><strong>{{ $i18n('storelist.entered') }}</strong> {{ row.item.createdAt }}</p>
            </div>
          </b-card>
        </template>
      </b-table>
      <div class="float-right p-1 pr-3">
        <b-pagination
          v-model="currentPage"
          :total-rows="storesFiltered.length"
          :per-page="perPage"
          aria-controls="store-list"
          class="my-0"
        />
      </div>
    </div>
    <div
      v-else
      class="card-body d-flex justify-content-center"
    >
      {{ $i18n('store.noStores') }}
      <div
        v-if="showCreateStore"
        :regionId="regionId"
        class="col"
      >
        <a
          :href="$url('storeAdd', regionId)"
          class="btn btn-sm btn-primary btn-block"
        >
          {{ $i18n('store.addNewStoresButton') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import {
  BTable,
  BPagination,
  BFormSelect,
  VBTooltip,
  BButton,
  BCard,
} from 'bootstrap-vue'
import StoreStatusIcon from './StoreStatusIcon.vue'
import i18n from '@/helper/i18n'

export default {
  components: { BCard, BTable, BButton, BPagination, BFormSelect, StoreStatusIcon },
  directives: { VBTooltip },
  props: {
    stores: { type: Array, default: () => [] },
    isManagingEnabled: { type: Boolean, default: false },
    storeMemberStatus: { type: Array, default: () => [] },
    showCreateStore: { type: Boolean, default: false },
    regionId: { type: Number, default: 0 },
    regionName: { type: String, default: '' },
  },
  data () {
    return {
      sortBy: 'createdAt',
      sortDesc: true,
      currentPage: 1,
      perPage: 20,
      filterText: '',
      filterStatus: null,
      statusOptions: [
        { value: null, text: 'Status' },
        { value: 1, text: i18n('storestatus.1') }, // CooperationStatus::NO_CONTACT
        { value: 2, text: i18n('storestatus.2') }, // CooperationStatus::IN_NEGOTIATION
        { value: 3, text: i18n('storestatus.3') }, // CooperationStatus::COOPERATION_STARTING
        { value: 4, text: i18n('storestatus.4') }, // CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
        { value: 5, text: i18n('storestatus.5') }, // CooperationStatus::COOPERATION_ESTABLISHED
        { value: 6, text: i18n('storestatus.6') }, // CooperationStatus::GIVES_TO_OTHER_CHARITY
        { value: 7, text: i18n('storestatus.7') }, // CooperationStatus::PERMANENTLY_CLOSED
      ],
    }
  },
  computed: {
    fields () {
      const columns = [
        {
          key: 'cooperationStatus',
          label: i18n('storelist.status'),
          tdClass: 'status',
          sortable: true,
        },
      ]
      if (this.isManagingEnabled) {
        columns.push({
          key: 'isManaging',
          label: i18n('storelist.isManaging'),
          tdClass: 'status',
          sortable: true,
        })
      }
      columns.push(
        {
          key: 'name',
          label: i18n('storelist.name'),
          sortable: true,
        },
        {
          key: 'street',
          label: i18n('storelist.address'),
          sortable: true,
        },
        {
          key: 'zipCode',
          label: i18n('storelist.zipcode'),
          sortable: true,
        },
        {
          key: 'city',
          label: i18n('storelist.city'),
          sortable: true,
        },
        {
          key: 'createdAt',
          label: i18n('storelist.added'),
          sortable: true,
        },
        {
          key: 'region',
          label: i18n('storelist.region'),
          sortable: true,
        },
        {
          key: 'actions',
          label: '',
          sortable: false,
        },
      )
      return columns
    },
    storesFiltered: function () {
      if (!this.filterText.trim() && !this.filterStatus) return this.stores
      const filterText = this.filterText ? this.filterText.toLowerCase() : null
      return Array.from(this.stores.filter((store) => {
        return (
          (!this.filterStatus || store.cooperationStatus === this.filterStatus) &&
          (!filterText || (
            store.name.toLowerCase().indexOf(filterText) !== -1 ||
            store.street.toLowerCase().indexOf(filterText) !== -1 ||
            store.region.name.toLowerCase().indexOf(filterText) !== -1 ||
            store.city.toLowerCase().indexOf(filterText) !== -1 ||
            store.zipCode.toLowerCase().indexOf(filterText) !== -1
          ))
        )
      }))
    },
    fieldsFiltered: function () {
      const outputFields = []

      const regions = [...new Set(this.stores.map(function (value) {
        return value.region.name
      }))]

      const displayableFields = (window.innerWidth > 800 && window.innerHeight > 600)
        ? ['region', 'actions']
        : ['region', 'street', 'createdAt', 'zip']

      this.fields.forEach(field => {
        if ((field.key === 'region' && regions.length > 0) ||
          !displayableFields.includes(field.key)) {
          outputFields.push(field)
        }
      })

      return outputFields
    },
  },
  methods: {
    isManaging (value) {
      const isManaging = this.storeMemberStatus.some(obj => obj.list.some(item => item.id === value.id && item.isManaging === true))
      return Boolean(isManaging)
    },
    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    mapLink (store) {
      if (['iPad', 'iPhone', 'iPod'].includes(
        navigator?.userAgentData?.platform ||
        navigator?.platform ||
        'unknown')) {
        return `maps://?q=?q=${store.location.lat},${store.location.lon})`
      }

      return `geo:0,0?q=${store.location.lat},${store.location.lon}`
    },
  },
}
</script>
<style>
  .details-nav {
    float:right;
    font-size: 2em;
  }
</style>
