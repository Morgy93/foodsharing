<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <h4 class="m-1 text-truncate">
          {{ $i18n('regionOptions.header_page', { bezirk: regionName }) }}
        </h4>
      </div>
      <div class="rounded p-3">
        <b-form-checkbox
          id="enableReportButton"
          v-model="reportButtonEnabled"
          :disabled="!maySetReport"
        >
          {{ $i18n('regionOptions.enableReportButton') }}
        </b-form-checkbox>
        <b-form-checkbox
          id="enableMediationButton"
          v-model="mediationButtonEnabled"
          :disabled="!maySetReport"
          class="mt-1"
        >
          {{ $i18n('regionOptions.enableMediationButton') }}
        </b-form-checkbox>
        <b-form-checkbox
          id="activeRegionPickupRule"
          v-model="regionPickupRuleActive"
          class="mt-1"
          :disabled="!maySetRule"
        >
          {{ $i18n('regionOptions.regionPickupRuleActive') }}
        </b-form-checkbox>
        <b-container fluid>
          <b-row class="my-1">
            <b-col>
              <label>{{ $i18n('regionOptions.regionPickupTimespan') }}: {{ pickupRuleTimespan }}</label>
              <b-form-input
                v-model="pickupRuleTimespan"
                type="range"
                min="1"
                max="31"
                :disabled="!maySetRule"
              />
            </b-col>
          </b-row>
          <b-row class="my-1">
            <b-col>
              <label>{{ $i18n('regionOptions.regionPickupLimitNumber') }}: {{ pickupRuleLimit }} </label>
              <b-form-input
                v-model="pickupRuleLimit"
                type="range"
                min="1"
                max="14"
                :disabled="!maySetRule"
                @change="onChangeMax()"
              />
            </b-col>
          </b-row>
          <b-row class="my-1">
            <b-col>
              <label>{{ $i18n('regionOptions.regionPickupLimitDayNumber') }}: {{ pickupRuleLimitDay }}</label>
              <b-form-input
                v-model="pickupRuleLimitDay"
                type="range"
                min="1"
                :max="rangeDayLimit"
                :disabled="!maySetRule"
              />
            </b-col>
          </b-row>
          <b-row class="my-1">
            <b-col>
              <label>{{ $i18n('regionOptions.regionPickupInactiveHours') }}:</label>
            </b-col>
            <b-col>
              <b-form-select
                v-model="pickupRuleInactive"
                :options="optionsIgnoreRuleHours"
                :disabled="!maySetRule"
              />
            </b-col>
          </b-row>
          <b-table
            :fields="fields"
            :items="regionPickupRuleActiveStoreList"
            :sort-by="sortBy"
            striped
            hover
            small
            caption-top
          >
            <template
              #cell(storeName)="row"
            >
              <a
                :href="$url('store', row.item.storeId)"
                class="ui-corner-all"
              >
                {{ row.value }}
              </a>
            </template>
          </b-table>
        </b-container>
        <div
          v-if="maySetReport || maySetRule"
        >
          <b-button
            class="text-right mt-2"
            variant="secondary"
            size="sm"
            @click="trySendOptions"
          >
            {{ $i18n('regionOptions.save') }}
          </b-button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { setRegionOptions } from '@/api/regions'
import { hideLoader, pulseError, pulseInfo, showLoader } from '@/script'
import i18n from '@/helper/i18n'

export default {
  props: {
    maySetRegionOptionsReportButtons: { type: Boolean, default: false },
    maySetRegionOptionsRegionPickupRule: { type: Boolean, default: false },
    isReportButtonEnabled: { type: Boolean, default: false },
    isMediationButtonEnabled: { type: Boolean, default: false },
    isRegionPickupRuleActive: { type: Boolean, default: false },
    regionPickupRuleTimespanDays: { type: Number, default: 0 },
    regionPickupRuleLimitNumber: { type: Number, default: 0 },
    regionPickupRuleLimitDayNumber: { type: Number, default: 0 },
    regionPickupRuleInactiveHours: { type: Number, default: 0 },
    rangeDayLimitNum: { type: Number, default: 4 },
    regionId: { type: Number, required: true },
    regionName: {
      type: String,
      default: '',
    },
    regionPickupRuleActiveStoreList: {
      type: Array,
      default: () => [],
    },

  },
  data () {
    return {
      maySetReport: this.maySetRegionOptionsReportButtons,
      maySetRule: this.maySetRegionOptionsRegionPickupRule,
      reportButtonEnabled: this.isReportButtonEnabled,
      mediationButtonEnabled: this.isMediationButtonEnabled,
      regionPickupRuleActive: this.isRegionPickupRuleActive,
      pickupRuleTimespan: this.regionPickupRuleTimespanDays,
      pickupRuleLimit: this.regionPickupRuleLimitNumber,
      pickupRuleLimitDay: this.regionPickupRuleLimitDayNumber,
      pickupRuleInactive: this.regionPickupRuleInactiveHours,
      rangeDayLimit: this.rangeDayLimitNum,
      sortBy: 'storeName',
      fields: [{
        key: 'storeName',
        label: this.$i18n('regionOptions.regionPickupRuleActiveStoreList'),
        sortable: true,
      },
      ],
      optionsIgnoreRuleHours: [
        { text: '4', value: 4 },
        { text: '8', value: 8 },
        { text: '12', value: 12 },
        { text: '16', value: 16 },
        { text: '24', value: 24 },
        { text: '36', value: 36 },
        { text: '48', value: 48 },
        { text: '60', value: 60 },
        { text: '72', value: 72 },
      ],
    }
  },
  methods: {
    onChangeMax () {
      if (this.pickupRuleLimitDay > this.pickupRuleLimit) {
        this.pickupRuleLimitDay = this.pickupRuleLimit
      }
      this.rangeDayLimit = this.pickupRuleLimit
    },
    async trySendOptions () {
      showLoader()
      try {
        await setRegionOptions(this.regionId, this.reportButtonEnabled, this.mediationButtonEnabled, this.regionPickupRuleActive, this.pickupRuleTimespan, this.pickupRuleLimit, this.pickupRuleLimitDay, this.pickupRuleInactive)
        pulseInfo(i18n('regionOptions.success'))
      } catch (err) {
        console.error(err)
        pulseError(i18n('error_unexpected'))
      }
      hideLoader()
    },
  },
}
</script>
