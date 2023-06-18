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
      <ConfigureableList
        v-model="subsetOfFields"
        :fields="fields"
      >
        <template #head="{ showConfigurationDialog }">
          <h2>Some Head</h2>
          <b-button @click="showConfigurationDialog">
            Configure
          </b-button>
        </template>
        <template #default>
          <ul>
            <li
              v-for="field in fields.filter(field => subsetOfFields.includes(field.key))"
              :key="field.key"
            >
              {{ field.key }}
            </li>
          </ul>
        </template>
      </ConfigureableList>
      <div class="form-row p-1 ">
        <div class="col-2 text-center">
          <label class=" col-form-label col-form-label-sm">
            {{ $i18n('store.filter') }}
          </label>
        </div>
        <div class="col-4">
          <label>
            <input
              v-model.trim="filterText"
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
        :fields="fields"
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
          #cell(status)="row"
          :v-if="isMobile"
        >
          <div class="text-center">
            <StoreStatusIcon :cooperation-status="row.value" />
          </div>
        </template>
        <template
          v-if="isManagingEnabled"
          #cell(memberState)="row"
        >
          <span
            v-if="isManaging(row.item)"
          >
            {{ $i18n('store.managing') }}
          </span>
          <span
            v-if="isMember(row.item)"
          >
            {{ $i18n('store.member') }}
          </span>
          <span
            v-if="isJumping(row.item)"
          >
            {{ $i18n('store.jumping') }}
          </span>
          <span
            v-if="isAppliedForTeam(row.item)"
          >
            {{ $i18n('store.isAppliedForTeam') }}
          </span>
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
import ConfigureableList from '@/components/ConfigureableList.vue'

export default {
  components: { BCard, BTable, BButton, BPagination, BFormSelect, StoreStatusIcon, ConfigureableList },
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
      availableFields: [
        'status',
        'name',
        'address',
        'zipcode',
        'city',
        'added',
        'region',
        'memberState',
        'actions',
      ],
      subsetOfFields: [
        'status',
        'city',
        'name',
      ],
      // actions not sortable, has no label
      FieldsNotSortable: [
        'actions',
      ],
      FieldsWithoutLabel: [
        'actions',
      ],
      FieldsHasStatusClass: [
        'status',
        'added',
        'memberState',
      ],
    }
  },
  computed: {
    fields () {
      return this.availableFields.map(field => {
        const fieldOpt = {
          key: field,
          label: this.FieldsWithoutLabel.includes(field) ? '' : i18n(`storelist.${field}`),
          sortable: !this.FieldsNotSortable.includes(field),
        }
        if (this.FieldsHasStatusClass.includes(field)) {
          fieldOpt.tdClass = 'status'
        }
        return fieldOpt
      })
    },
    // configreableFields () {
    //   return this.fields.map(field => {
    //     return { text: field.label, value: field.key }
    //   })
    // },
    storesFiltered () {
      let stores = this.stores
      if (this.filterStatus) {
        stores = stores.filter(store => store.status === this.filterStatus)
      }
      if (this.filterText) {
        // match filterText an all store properties
        stores = stores.filter(store => {
          for (const prop in store) {
            const propValue = store[prop]
            if (typeof propValue === 'string' && propValue.toLocaleLowerCase().indexOf(this.filterTextLower) !== -1) {
              return true
            }
          }
          return false
        })
      }
      return stores
    },
    filterTextLower () {
      return this.filterText.toLowerCase()
    },
  },
  methods: {
    isManaging (value) {
      const isManaging = this.storeMemberStatus.some(obj => obj.list.some(item => item.id === value.id && item.isManaging === true))
      return Boolean(isManaging)
    },
    isMember (value) {
      const isMember = this.storeMemberStatus.some(obj => obj.list.some(item => item.id === value.id && item.membershipStatus === 1 && item.isManaging === false))
      return Boolean(isMember)
    },
    isJumping (value) {
      const isJumping = this.storeMemberStatus.some(obj => obj.list.some(item => item.id === value.id && item.membershipStatus === 2))
      return Boolean(isJumping)
    },
    isAppliedForTeam (value) {
      const AppliedForTeam = this.storeMemberStatus.some(obj => obj.list.some(item => item.id === value.id && item.membershipStatus === 0))
      return Boolean(AppliedForTeam)
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
