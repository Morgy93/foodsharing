<template>
  <div>
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('chain.listheader') }}
        <span v-if="chains !== null && chains.length !== chainsFiltered.length">
          {{ $i18n('filterlist.some_in_all', { some: chainsFiltered.length, all: chains.length }) }}
        </span>
      </div>
      <div
        v-if="chains !== null"
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
                :placeholder="$i18n('chain.filterplaceholder')"
              >
            </label>
          </div>
          <div class="col-3">
            <b-form-select
              v-model="filterStatus"
              :options="statusFilterOptions"
            />
          </div>
          <div class="col">
            <button
              v-b-tooltip.hover
              type="button"
              class="btn"
              :title="$i18n('storelist.emptyfilters')"
              @click="clearFilter"
            >
              <i class="fas fa-times" />
            </button>
          </div>
          <div
            v-if="adminPermissions"
            class="col"
          >
            <b-button
              size="sm"
              variant="primary"
              @click="createChainModal"
            >
              {{ $i18n('chain.new') }}
            </b-button>
          </div>
        </div>
        <b-table
          id="chain-list"
          :fields="fields"
          :current-page="currentPage"
          :per-page="perPage"
          :items="chainsFiltered"
          tbody-tr-class="chain-row"
          sort-icon-left
          small
          hover
          responsive
        >
          <template #cell(status)="row">
            <i
              v-b-tooltip.hover.window="statusOptions[row.value].description"
              class="fas fa-circle"
              :style="{ color: statusOptions[row.value].color }"
            />
          </template>

          <template #cell(headquarters_city)="row">
            {{ row.item.headquarters_zip }}
            {{ row.value }}
          </template>

          <template #cell(kams)="row">
            <PickupEntries
              :registered-users="row.value"
              :max-width="100"
              :min-width="60"
            />
          </template>

          <template #cell(name)="row">
            <a
              v-if="row.item.forum_thread"
              class="thread-link"
              :href="$url('forumThread', row.item.forum_thread)"
            >
              {{ row.value }}
            </a>
            <span v-else>
              {{ row.value }}
            </span>
          </template>

          <template #cell(notes)="row">
            <span class="clamped-3">
              <span v-if="row.item.allow_press">
                {{ $i18n('chain.allowpress') }}
              </span>
              {{ row.value }}
              <small
                v-b-tooltip.hover.window="$i18n('chain.tooltips.modification_date')"
                class="text-muted change-date"
              >
                {{ $dateFormatter.date(new Date(row.item.modification_date), { short: true }) }}
              </small>
            </span>
          </template>

          <template #cell(actions)="row">
            <b-dropdown
              v-if="adminPermissions || row.item.kams.some(kam => kam.id === ownId)"
              v-b-tooltip.hover.noninteractive.window="$i18n('chain.tooltips.options')"
              size="sm"
              no-caret
              variant="primary"
            >
              <template #button-content>
                <i class="fas fa-cog" />
              </template>
              <b-dropdown-item
                href="#"
                @click="detailsChainModal(row)"
              >
                {{ $i18n('chain.options.showstores') }}
              </b-dropdown-item>
              <b-dropdown-item
                href="#"
                @click="editChainModal(row)"
              >
                {{ $i18n('chain.options.edit') }}
              </b-dropdown-item>
            </b-dropdown>
          </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            v-model="currentPage"
            :total-rows="chainsFiltered.length"
            :per-page="perPage"
            aria-controls="chain-list"
            class="my-0"
          />
        </div>
      </div>
      <div
        v-else
        class="card-body d-flex justify-content-center"
      >
        <i class="fas fa-spinner fa-spin" />
      </div>
    </div>

    <InputModal
      ref="input-modal"
      :status-filter-options="statusFilterOptions"
      :admin-permissions="adminPermissions"
      @ok="finishEditing"
    />

    <StoreDetailsModal
      ref="details-modal"
      :store-list="storeList"
    />
  </div>
</template>

<script>

import PickupEntries from '../../../../../src/Modules/Profile/components/PickupEntries.vue'
import InputModal from '@/components/Modals/ChainList/InputModal.vue'
import StoreDetailsModal from '@/components/Modals/ChainList/StoreDetailsModal.vue'
import { getters, mutations } from '@/stores/chains'
import { hideLoader, pulseError, showLoader } from '@/script'

export default {
  components: { PickupEntries, InputModal, StoreDetailsModal },
  props: {
    adminPermissions: {
      type: Boolean,
      default: false,
    },
    ownId: {
      type: Number,
      default: -1,
    },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      filterText: '',
      filterStatus: null,
      fields: [
        {
          key: 'status',
          label: this.$i18n('chain.columns.status'),
          tdClass: 'status',
          sortable: true,
        },
        {
          key: 'name',
          label: this.$i18n('chain.columns.name'),
          sortable: true,
        },
        {
          key: 'store_count',
          label: this.$i18n('chain.columns.stores'),
          sortable: true,
          tdClass: 'text-center',
        },
        {
          key: 'headquarters_city',
          label: this.$i18n('chain.columns.headquarters'),
          sortable: true,
        },
        {
          key: 'kams',
          label: this.$i18n('chain.columns.kams'),
        },
        {
          key: 'notes',
          label: this.$i18n('chain.columns.notes'),
        },
        {
          key: 'actions',
          label: '',
        },
      ],
      statusOptions: [
        {
          description: this.$i18n('chain.status.cooperating'),
          color: 'var(--fs-color-chain-cooperating)',
        },
        {
          description: this.$i18n('chain.status.negotiating'),
          color: 'var(--fs-color-chain-negotiating)',
        },
        {
          description: this.$i18n('chain.status.notcooperating'),
          color: 'var(--fs-color-chain-not-cooperating)',
        },
      ],
    }
  },
  computed: {
    chains: () => getters.getChains(),
    storeList: () => getters.getStores(),
    chainsFiltered: function () {
      if (this.chains === null) return []
      let chains = this.chains
      const filterText = this.filterText.trim().toLowerCase()
      if (filterText) {
        const searchKeys = ['name', 'headquarters_city']
        chains = chains.filter(
          chain => (
            searchKeys.some(key => chain[key]?.toLowerCase().includes(filterText))) ||
            chain.kams.find(kam => kam.id === parseInt(filterText)),
        )
      }
      if (this.filterStatus !== null) {
        chains = chains.filter(chain => chain.status === this.filterStatus)
      }
      return chains
    },
    statusFilterOptions: function () {
      return [{ value: null, text: this.$i18n('chain.status.filterplaceholder') }].concat(
        this.statusOptions.map((status, index) => ({
          value: index,
          text: status.description,
        })),
      )
    },
  },
  async mounted () {
    await mutations.fetchChains()
  },
  methods: {
    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    editChainModal (row) {
      const chain = row.item
      this.$refs['input-modal'].show(chain.id, {
        name: chain.name,
        headquarters_zip: chain.headquarters_zip,
        headquarters_city: chain.headquarters_city,
        status: chain.status,
        forum_thread: chain.forum_thread,
        notes: chain.notes,
        common_store_information: chain.common_store_information,
        allow_press: !!chain.allow_press,
        kamIds: chain.kams.map(x => x.id).join(', '),
      })
    },
    createChainModal () {
      this.$refs['input-modal'].show(-1, {
        name: '',
        headquarters_zip: '',
        headquarters_city: '',
        status: 2,
        forum_thread: '',
        allow_press: false,
        notes: '',
        common_store_information: '',
        kamIds: '',
      })
    },
    async detailsChainModal (row) {
      const selectedChain = row.item
      this.$refs['details-modal'].show(selectedChain)
      await mutations.fetchChainStores(selectedChain.id)
    },
    async finishEditing (chainId, data) {
      showLoader()
      console.log(data)
      if (chainId < 0) {
        try {
          await mutations.createChain(data)
        } catch (e) {
          pulseError(this.$i18n('chain.error.create'))
        }
      } else {
        try {
          await mutations.editChain(chainId, data)
        } catch {
          pulseError(this.$i18n('chain.error.edit'))
        }
      }
      hideLoader()
    },
  },
}
</script>

<style lang="scss" scoped>

.status {
  width: 0;
  text-align: center;
}

::v-deep .chain-row td {
  vertical-align: middle;
}

.clamped-3 {
  display: -webkit-inline-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
