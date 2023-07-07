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
        :fields.sync="fields"
        :selection.sync="fieldSelection"
        store
      >
        <template #head="{ showConfigurationDialog }">
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
          <b-button @click="showConfigurationDialog">
            Configure
          </b-button>
        </template>
        <template #default>
          <b-table
            id="store-list"
            :fields="selectedFields"
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
            <template #cell(memberState)="row">
              {{ getUserRole(row.item.id) }}
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
        </template>
      </ConfigureableList>
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
import ConfigureableList from '@/components/ConfigureableList.vue'
import { useStoreStore } from '@/stores/store'

const storeStore = useStoreStore()

export default {
  components: { BCard, BTable, BButton, BPagination, BFormSelect, StoreStatusIcon, ConfigureableList },
  directives: { VBTooltip },
  props: {
    stores: { type: Array, default: () => [] },
    isManagingEnabled: { type: Boolean, default: false },
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
        { value: 1, text: this.$i18n('storestatus.1') }, // CooperationStatus::NO_CONTACT
        { value: 2, text: this.$i18n('storestatus.2') }, // CooperationStatus::IN_NEGOTIATION
        { value: 3, text: this.$i18n('storestatus.3') }, // CooperationStatus::COOPERATION_STARTING
        { value: 4, text: this.$i18n('storestatus.4') }, // CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US
        { value: 5, text: this.$i18n('storestatus.5') }, // CooperationStatus::COOPERATION_ESTABLISHED
        { value: 6, text: this.$i18n('storestatus.6') }, // CooperationStatus::GIVES_TO_OTHER_CHARITY
        { value: 7, text: this.$i18n('storestatus.7') }, // CooperationStatus::PERMANENTLY_CLOSED
      ],
      fieldsDefinition: [
        {
          key: 'cooperationStatus',
          label: this.$i18n('storelist.status'),
          tdClass: 'status',
          sortable: true,
        },
        {
          key: 'name',
          label: this.$i18n('storelist.name'),
          sortable: true,
        },
        {
          key: 'street',
          label: this.$i18n('storelist.address'),
          sortable: true,
        },
        {
          key: 'zipCode',
          label: this.$i18n('storelist.zipcode'),
          sortable: true,
        },
        {
          key: 'city',
          label: this.$i18n('storelist.city'),
          sortable: true,
        },

        {
          key: 'createdAt',
          label: this.$i18n('storelist.added'),
          tdClass: 'status',
          sortable: true,
        },
        {
          key: 'region',
          label: this.$i18n('storelist.region'),
          sortable: true,
        },
        {
          key: 'memberState',
          label: this.$i18n('storelist.memberState'),
          tdClass: 'status',
          sortable: true,
        },
        {
          key: 'actions',
          label: '',
          sortable: false,
        },
      ],
      availableFields: [],
      fieldSelection: [],
    }
  },
  computed: {
    fields: {
      get () {
        return this.availableFields.map(fieldKey => this.fieldsDefinition.find(field => field.key === fieldKey))
      },
      set (fields) {
        this.availableFields = fields
      },
    },
    selectedFields () {
      // console.log('eval selectedFields', this.fieldSelection.concat(), this.fields.filter(field => this.fieldSelection.includes(field.key)))
      return this.fields.filter(field => this.fieldSelection.includes(field.key))
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
    storeMemberStatus () {
      return
    }
  },
  created () {
    this.availableFields = this.fieldsDefinition.map(field => field.key)
    this.fieldSelection = this.availableFields
  },
  methods: {
    getUserRole (storeId) {
      if (storeStore.userRelations === null) {
        storeStore.fetchUserStoreRelations()
        return '...loading'
      } else {
        const relation = storeStore.userRelations.find(relation => relation.id === storeId)
        if (relation.isManaging) {
          return this.$i18n('store.managing')
        }
        switch (relation.membershipStatus) {
          case 0: return this.$i18n('store.isAppliedForTeam')
          case 1: return this.$i18n('store.member')
          case 2: return this.$i18n('store.jumping')
        }
      }
      // not a member
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
