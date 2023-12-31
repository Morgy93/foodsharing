<template>
  <b-modal
    id="storeInformationModal"
    ref="storeInformationModal"
    :cancel-title="$i18n('globals.close')"
    :ok-title="$i18n('globals.save')"
    :ok-disabled="!editMode"
    size="lg"
    @show="showModal"
    @hidden="resetModal"
    @ok="submit"
  >
    <template #modal-title>
      {{ $i18n('terminology.store') }}
      <b-form-checkbox
        v-if="hasEditPermission"
        id="editMode"
        v-model="editMode"
        switch
      >
        {{ $i18n('storeedit.bread') }}
      </b-form-checkbox>
    </template>
    <b-card no-body>
      <b-tabs
        content-class="mt-3"
        card
        fill
      >
        <b-tab
          :title="$i18n('storeview.common')"
          active
        >
          <b-card-text>
            <b-form-group
              :label="$i18n('name')"
              label-for="storeName"
              class="bootstrap input-wrapper"
            >
              <b-form-input
                id="storeName"
                v-model="store.name"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :description="$i18n('storeview.visible_for_public')"
              :label="$i18n('public_info')"
              label-for="publicInfo"
            >
              <b-form-textarea
                id="publicInfo"
                v-model="store.publicInfo"
                :placeholder="$i18n('wall.message_placeholder')"
                :state="publicInfoState"
                rows="5"
                max-rows="10"
                :disabled="!editMode"
              />
            </b-form-group>
          </b-card-text>
        </b-tab>
        <b-tab
          :title="$i18n('storeview.source_data')"
          @click="dispatchResize"
        >
          <b-card-text>
            <b-form-group
              v-if="!store.contact"
              :label="$i18n('ansprechpartner')"
              class="bootstrap input-wrapper"
            >
              <small>
                {{ $i18n('storeview.no_permission_to_view') }}
              </small>
            </b-form-group>
            <b-form-group
              v-if="store.contact"
              :label="$i18n('ansprechpartner')"
              class="bootstrap input-wrapper"
            >
              <b-form-group
                :label="$i18n('storeview.contact.name')"
                label-for="contactName"
              >
                <b-form-input
                  id="contactName"
                  v-model="store.contact.name"
                  :disabled="!editMode"
                />
              </b-form-group>
              <b-form-group
                :label="$i18n('telefon')"
                label-for="phone"
              >
                <b-form-input
                  id="phone"
                  v-model="store.contact.phone"
                  :disabled="!editMode"
                />
              </b-form-group>
              <b-form-group
                :label="$i18n('fax')"
                label-for="fax"
              >
                <b-form-input
                  id="fax"
                  v-model="store.contact.fax"
                  :disabled="!editMode"
                />
              </b-form-group>
              <b-form-group
                :label="$i18n('email')"
                label-for="email"
              >
                <b-form-input
                  id="email"
                  v-model="store.contact.email"
                  :type="'email'"
                  :disabled="!editMode"
                />
              </b-form-group>
            </b-form-group>
            <b-form-group
              :label="$i18n('kette_id')"
              label-for="chainId"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                id="chainId"
                v-model="store.chainId"
                :options="storeChains"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('address')"
              label-for="location"
            >
              <leaflet-location-search
                id="location"
                :zoom="17"
                :coordinates="store.location"
                :street="store.address.street"
                :postal-code="store.address.zipCode"
                :city="store.address.city"
                :disabled="!editMode"
                @address-change="onAddressChanged"
              />
            </b-form-group>
          </b-card-text>
        </b-tab>
        <b-tab
          :title="$i18n('storeview.management')"
        >
          <b-card-text>
            <b-form-group
              :label="$i18n('bezirk')"
              label-for="region"
            >
              <region-tree-v-form
                v-if="store.region"
                v-model="store.region"
                modal-title="storeview.select_related_region"
                input-name="regionId"
                :selectable-region-types="[1, 8, 9]"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('betrieb_kategorie_id')"
              label-for="categoryId"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                id="categoryId"
                v-model="store.categoryId"
                :options="categoryTypes"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('storeview.cooperation_start')"
              label-for="cooperationStart"
              class="bootstrap input-wrapper"
            >
              <b-form-datepicker
                id="cooperationStart"
                v-model="store.cooperationStart"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('storeview.cooperation_status')"
              label-for="cooperationStatus"
            >
              <b-form-select
                id="cooperationStatus"
                v-model="store.cooperationStatus"
                :options="storeCooperationStatusTypes"
                :disabled="!editMode"
              />
            </b-form-group>
          </b-card-text>
        </b-tab>
        <b-tab
          :title="$i18n('terminology.pickup')"
        >
          <b-card-text>
            <b-form-group
              :label="$i18n('store.average_collection_quantity')"
              label-for="weight"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                id="weight"
                v-model="store.weight"
                :options="weightTypes"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('public_time')"
              label-for="publicTime"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                id="publicTime"
                v-model="store.publicTime"
                :options="publicTimes"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('prefetchtime')"
              label-for="calendarInterval"
              class="bootstrap input-wrapper"
            >
              <b-form-spinbutton
                id="calendarInterval"
                v-model="calendarInterval"
                min="0"
                max="8"
                class="w-50"
                :disabled="!editMode"
                :formatter-fn="calendarIntervalFormatter"
                wrap
              />
            </b-form-group>
            <b-form-group
              :label="$i18n('use_region_pickup_rule')"
              label-for="useRegionPickupRule"
              class="input-wrapper"
            >
              <b-form-checkbox
                id="useRegionPickupRule"
                v-model="store.options.useRegionPickupRule"
                switch
                :disabled-field="!editMode"
                :disabled="!editMode"
              />
            </b-form-group>
            <RegularPickup
              :edit-pickups.sync="editPickups"
              :edit-mode="editMode"
              :max-count-pickup-slot="maxCountPickupSlot"
            />
          </b-card-text>
        </b-tab>
        <b-tab
          :title="$i18n('storeview.team')"
        >
          <b-card-text>
            <b-form-group
              :label="$i18n('storeedit.fetch.teamStatus')"
              label-for="teamStatus"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                id="teamStatus"
                v-model="store.teamStatus"
                :options="teamStatusOptions"
                :disabled="!editMode"
              />
            </b-form-group>
            <b-form-group
              id="fieldset-2"
              :description="$i18n('storeview.visible_for_team')"
              :label="$i18n('storeview.specials')"
              label-for="description"
            >
              <b-form-textarea
                id="description"
                v-model="store.description"
                :placeholder="$i18n('wall.message_placeholder')"
                rows="5"
                max-rows="100"
                :disabled="!editMode"
              />
              <div
                class="mb-2 ml-2"
                v-html="$i18n('forum.markdown_description')"
              />
            </b-form-group>
          </b-card-text>
        </b-tab>
        <b-tab
          :title="$i18n('storeview.public_and_statistics')"
        >
          <b-card-text>
            <b-form-group
              :label="$i18n('ueberzeugungsarbeit')"
              label-for="effort"
              class="bootstrap input-wrapper"
            >
              <b-form-select
                v-if="store.effort !== null"
                id="effort"
                v-model="store.effort"
                :options="convinceStatusTypes"
                :disabled="!editMode"
              />
              <small
                v-if="store.effort === null"
              >
                {{ $i18n('storeview.no_permission_to_view') }}
              </small>
            </b-form-group>
            <b-form-group
              :label="$i18n('sticker')"
              label-for="showsSticker"
              class="bootstrap input-wrapper"
            >
              <b-form-checkbox
                v-if="store.showsSticker !== null"
                id="showsSticker"
                v-model="store.showsSticker"
                switch
                :disabled-field="!editMode"
                :disabled="!editMode"
              />
              <small
                v-if="store.showsSticker === null"
              >
                {{ $i18n('storeview.no_permission_to_view') }}
              </small>
            </b-form-group>
            <b-form-group
              :label="$i18n('presse')"
              label-for="publicity"
              class="bootstrap input-wrapper"
            >
              <b-form-checkbox
                v-if="store.publicity !== null"
                id="publicity"
                v-model="store.publicity"
                switch
                :disabled-field="!editMode"
                :disabled="!editMode"
              />
              <small
                v-if="store.publicity === null"
              >
                {{ $i18n('storeview.no_permission_to_view') }}
              </small>
            </b-form-group>
            <b-form-group
              :label="$i18n('storeview.groceries.label')"
              label-for="tags-with-dropdown"
            >
              <small
                v-if="store.groceries === null"
              >
                {{ $i18n('storeview.no_permission_to_view') }}
              </small>
              <b-form-tags
                v-if="store.groceries !== null"
                id="tags-with-dropdown"
                v-model="storeFoodNames"
                :disabled="!editMode"
                n-outer-focus
                class="mb-2"
              >
                <template #default="{ tags, disabled, addTag, removeTag }">
                  <ul
                    v-if="tags.length > 0"
                    class="list-inline d-inline-block mb-2"
                  >
                    <li
                      v-for="tag in tags"
                      :key="tag"
                      class="list-inline-item"
                    >
                      <b-form-tag
                        :title="tag"
                        :disabled="disabled"
                        variant="info"
                        @remove="removeTag(tag)"
                      >
                        {{ tag }}
                      </b-form-tag>
                    </li>
                  </ul>

                  <b-dropdown
                    v-if="editMode"
                    size="sm"
                    variant="outline-secondary"
                    block
                    menu-class="w-100"
                  >
                    <template #button-content>
                      <i class="fas fa-cutlery" /> {{ $i18n('storeview.groceries.select_tag') }}
                    </template>
                    <b-dropdown-form @submit.stop.prevent="() => {}">
                      <b-form-group
                        :label="$i18n('storeview.groceries.search_tag')"
                        label-for="tag-search-input"
                        label-cols-md="auto"
                        class="mb-0"
                        label-size="sm"
                        :description="foodSearchCriteriaFieldDesc"
                        :disabled="disabled"
                      >
                        <b-form-input
                          id="tag-search-input"
                          v-model="foodSearchCriteriaField"
                          type="search"
                          size="sm"
                          autocomplete="off"
                        />
                      </b-form-group>
                    </b-dropdown-form>
                    <b-dropdown-divider />
                    <b-dropdown-item-button
                      v-for="option in availableFoodOptions"
                      :key="option"
                      @click="onSelectFoodClick({ option, addTag })"
                    >
                      {{ option }}
                    </b-dropdown-item-button>
                    <b-dropdown-text v-if="availableFoodOptions.length === 0">
                      {{ $i18n('storeview.groceries.no_tag_preset') }}
                    </b-dropdown-text>
                  </b-dropdown>
                </template>
              </b-form-tags>
            </b-form-group>
          </b-card-text>
        </b-tab>
      </b-tabs>
    </b-card>
  </b-modal>
</template>

<script>
// Stores
import { getters, mutations } from '@/stores/stores'
import { getters as pickupGetters, mutations as pickupMutations } from '@/stores/pickups'

// Others
import { pulseError, showLoader, hideLoader, pulseSuccess } from '@/script'
import { getStoreInformation, updateStore } from '@/api/stores'
import { editRegularPickup } from '@/api/pickups'

import LeafletLocationSearch from '@/components/map/LeafletLocationSearch.vue'
import RegionTreeVForm from '@/components/regiontree/RegionTreeVForm.vue'
import RegularPickup from './RegularPickup.vue'

import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import AutoResizeTextareaMixin from '@/mixins/AutoResizeTextareaMixin'

export default {
  name: 'StoreInformationEditModal',
  components: {
    LeafletLocationSearch,
    RegionTreeVForm,
    RegularPickup,
  },
  mixins: [MediaQueryMixin, AutoResizeTextareaMixin],
  props: {
    storeId: { type: Number, required: true },
  },
  data () {
    return {
      editPickups: {},
      previousEditPickups: null,
      selectedWeekDay: null,
      foodSearchCriteriaField: '',
      storeFoodNames: [],
      teamStatusOptions: [
        { value: 0, text: this.$i18n('store.team.isfull') },
        { value: 1, text: this.$i18n('menu.entry.helpwanted') },
        { value: 2, text: this.$i18n('menu.entry.helpneeded') },
      ],
      editMode: null,
      store: {
        name: null,
        publicInfo: null,
        description: null,
        contact: {
          name: null,
          phone: null,
          fax: null,
          email: null,
        },
        chainId: null,
        categoryId: null,
        address: {
          street: null,
          city: null,
          zipCode: null,
        },
        location: {
          lon: 0.0,
          lat: 0.0,
        },
        region: null,
        cooperationStatus: null,
        cooperationStart: null,
        weight: null,
        publicTime: null,
        calendarInterval: null,
        options: {
          useRegionPickupRule: null,
        },
        groceries: [],
        effort: null,
        showsSticker: null,
        publicity: null,
      },
    }
  },
  computed: {
    hasEditPermission () {
      return this.store.contact !== null
    },
    publicInfoState () {
      if (!this.editMode) return null
      else return this.store.publicInfo.length <= 180
    },
    calendarInterval: {
      get () {
        return this.store.calendarInterval / 3600 / 24 / 7
      },
      set (val) {
        this.store.calendarInterval = val * 3600 * 24 * 7
      },
    },
    maxCountPickupSlot () {
      return getters.getMaxCountPickupSlot()
    },
    storeChains () {
      return getters.getStoreChains()?.map(item => ({ value: item.id, text: item.name }))
    },
    storeCooperationStatusTypes () {
      return getters.getStoreCooperationStatus()?.map(item => ({ value: item.id, text: item.name }))
    },
    weightTypes () {
      return getters.getStoreWeightTypes()?.map(item => ({ value: item.id, text: item.name }))
    },
    publicTimes () {
      return getters.getPublicTimes()?.map(item => ({ value: item.id, text: item.name }))
    },
    categoryTypes () {
      return getters.getStoreCategoryTypes()?.map(item => ({ value: item.id, text: item.name }))
    },
    convinceStatusTypes () {
      return getters.getStoreConvinceStatusTypes()?.map(item => ({ value: item.id, text: item.name }))
    },
    foodSearchCriteria () {
      return this.foodSearchCriteriaField.trim().toLowerCase()
    },
    storeFoodIds () {
      const selectedValues = getters.getGrocerieTypes().filter(opt => this.storeFoodNames.indexOf(opt.name) !== -1).filter(opt => opt.name)
      return [...new Set(selectedValues.map(opt => opt.id))]
    },
    availableFoodOptions () {
      const foodSearchCriteria = this.foodSearchCriteria
      // Filter out already selected options
      const options = getters.getGrocerieTypes().filter(opt => this.storeFoodNames.indexOf(opt.name) === -1)
      if (foodSearchCriteria) {
        // Show only opitions that match foodSearchCriteria
        return options.filter(opt => opt.name.toLowerCase().indexOf(foodSearchCriteria) > -1).map(opt => opt.name)
      }
      // Show all options availab
      return [...new Set(options.map(opt => opt.name))]
    },
    foodSearchCriteriaFieldDesc () {
      if (this.foodSearchCriteria && this.availableFoodOptions.length === 0) {
        return this.$i18n('storeview.groceries.no_match')
      }
      return ''
    },
  },
  async created () {
    // Load data
    await mutations.fetch()
    await pickupMutations.fetchRegularPickup(this.storeId)
    this.editPickups = this.regularPickup()
  },
  methods: {
    regularPickup () {
      return pickupGetters.getRegularPickup()
    },
    dispatchResize () {
      window.dispatchEvent(new Event('resize'))
    },
    isUpdatedRegularPickup () {
      return JSON.stringify(this.editPickups) !== JSON.stringify(this.previousEditPickups)
    },
    async submit (bvModalEvent) {
      bvModalEvent.preventDefault()
      if (!this.publicInfoState) {
        pulseError(this.$i18n('storeview.invalid_field'))
        return
      }

      try {
        showLoader()
        const store = structuredClone(this.store)
        store.regionId = this.store.region.id
        delete store.region
        store.groceries = this.storeFoodIds
        await updateStore(store)
        if (this.isUpdatedRegularPickup()) {
          await editRegularPickup(this.storeId, this.editPickups)
        }
        pulseSuccess(this.$i18n('storeedit.edit_success'))
        this.$bvModal.hide('storeInformationModal')
      } catch (err) {
        const errorDescription = err.jsonContent ?? { message: '' }
        const errorMessage = `(${errorDescription.message ?? 'Unknown'})`
        pulseError(this.$i18n('storeedit.unsuccess', { error: errorMessage }))
      } finally {
        hideLoader()
      }
    },
    async showModal () {
      this.previousEditPickups = structuredClone(this.editPickups)
      this.store = await getStoreInformation(this.storeId)
      if (this.store.categoryId === null) {
        this.store.categoryId = 0
      }

      if (this.store.chainId === null) {
        this.store.chainId = 0
      }

      if (this.store.groceries !== null) {
        const selectedValues = getters.getGrocerieTypes().filter(opt => this.store.groceries.indexOf(opt.id) !== -1).map(opt => opt.name)
        this.storeFoodNames = [...new Set(selectedValues)]
      } else {
        this.storeFoodNames = []
      }
    },
    async resetModal () {
      this.editMode = false
      this.store = await getStoreInformation(this.storeId)
      if (this.store.groceries !== null) {
        const selectedValues = getters.getGrocerieTypes().filter(opt => this.store.groceries.indexOf(opt.id) !== -1).map(opt => opt.name)
        this.storeFoodNames = [...new Set(selectedValues)]
      } else {
        this.storeFoodNames = []
      }
    },
    calendarIntervalFormatter (value) {
      if (value === 0) {
        return this.$i18n('storeview.calendar_interval.not_set')
      } else if (value === 1) {
        return this.$i18n('storeview.calendar_interval.single')
      } else {
        return this.$i18n('storeview.calendar_interval.multiply', { weeks: value })
      }
    },
    onSelectFoodClick ({ option, addTag }) {
      addTag(option)
      this.foodSearchCriteriaField = ''
    },
    onAddressChanged (coordinates, street, postalCode, city) {
      this.store.location = coordinates
      this.store.address.street = street
      this.store.address.zipCode = postalCode
      this.store.address.city = city
    },
  },
}
</script>

<style lang="scss" scoped>
.selector select {
    margin-bottom: 0.25rem;
}

.test-modal .modal-dialog {
  max-width: 100%;
  margin: 0;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100vh;
  display: flex;
  position: fixed;
  z-index: 100000;
}
</style>

<style>
.b-form-btn-label-control.form-control > .btn {
  font-size: 0.5em;
}
</style>
