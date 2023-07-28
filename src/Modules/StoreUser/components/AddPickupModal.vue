<template>
  <div>
    <b-modal
      id="AddPickupModal"
      :title="$i18n('store.enterdate')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      @ok="trySetPickupSlots"
    >
      <b-row>
        <b-col cols="3">
          {{ $i18n('day') }}
        </b-col>
        <b-col>
          <b-form-datepicker
            v-model="selectedSlotDate"
            v-bind="labelsCalendar || {}"
            :min="minSlotDate"
            :locale="$i18n('calendar.locale')"
            menu-class="w-100"
            calendar-width="100%"
            class="mb-2"
            start-weekday="1"
          />
        </b-col>
      </b-row>
      <b-row>
        <b-col cols="3">
          {{ $i18n('time') }}
        </b-col>
        <b-col>
          <b-form-timepicker
            v-model="selectedSlotTime"
            v-bind="labelsTimepicker || {}"
            :locale="locale"
          />
        </b-col>
      </b-row>
      <b-row
        v-if="disableAutoPickupSlot === false"
        class="pt-2"
      >
        <b-col cols="3">
          {{ $i18n('pickup.edit.description_titel') }}
        </b-col>
        <b-col>
          <b-form-input
            v-model="slotDescription"
            :placeholder="$i18n('pickup.description_optional')"
            :maxlength="100"
          />
          <small v-if="slotDescription?.length === 100">
            <i class="fas fa-info-circle" />
            {{ $i18n('pickup.description_max_length_info') }}
          </small>
        </b-col>
      </b-row>
      <b-row
        v-if="disableAutoPickupSlot === false"
        class="pt-2"
      >
        <b-col cols="3">
          {{ $i18n('pickup.edit.slot_titel') }}
        </b-col>
        <b-col>
          <b-form-spinbutton
            v-model="selectedSlotCount"
            :min="minSlotCount"
            :max="maxCountPickupSlot"
          />
        </b-col>
      </b-row>
      <hr>
      <b-row class="pt-2">
        <b-col cols="3">
          {{ $i18n('pickup.edit.disable_auto_slot_titel') }}
        </b-col>
        <b-col>
          <b-form-checkbox
            v-model="disableAutoPickupSlot"
            class="pt-2"
          >
            {{ $i18n('pickup.edit.disable_auto_slot') }}
          </b-form-checkbox>
        </b-col>
      </b-row>
    </b-modal>
  </div>
</template>

<script>
import { setPickupSlots } from '@/api/pickups'
import { getters } from '@/stores/stores'
import i18n, { locale } from '@/helper/i18n'
import { pulseError } from '@/script'

export default {
  props: {
    storeId: { type: Number, required: true },
  },
  data () {
    return {
      disableAutoPickupSlot: false,
      locale: locale,
      labelsTimepicker: {
        labelHours: i18n('timepicker.labelHours'),
        labelMinutes: i18n('timepicker.labelMinutes'),
        labelSeconds: i18n('timepicker.labelSeconds'),
        labelIncrement: i18n('timepicker.labelIncrement'),
        labelDecrement: i18n('timepicker.labelDecrement'),
        labelSelected: i18n('timepicker.labelSelected'),
        labelNoTimeSelected: i18n('timepicker.labelNoTimeSelected'),
        labelCloseButton: i18n('timepicker.labelCloseButton'),
      },
      labelsCalendar: {
        labelPrevYear: i18n('calendar.labelPrevYear'),
        labelPrevMonth: i18n('calendar.labelPrevMonth'),
        labelCurrentMonth: i18n('calendar.labelCurrentMonth'),
        labelNextMonth: i18n('calendar.labelNextMonth'),
        labelNextYear: i18n('calendar.labelNextYear'),
        labelToday: i18n('calendar.labelToday'),
        labelSelected: i18n('calendar.labelSelected'),
        labelNoDateSelected: i18n('calendar.labelNoDateSelected'),
        labelCalendar: i18n('calendar.labelCalendar'),
        labelNav: i18n('calendar.labelNav'),
        labelHelp: i18n('calendar.labelHelp'),
        labelTodayButton: i18n('calendar.labelToday'),
      },
      selectedSlotDate: null,
      selectedSlotTime: null,
      minSlotDate: null,
      selectedSlotCount: 1,
      minSlotCount: 1,
      slotDescription: '',
    }
  },
  computed: {
    maxCountPickupSlot () {
      return getters.getMaxCountPickupSlot()
    },
  },
  watch: {
    disableAutoPickupSlot (newVal) {
      if (newVal) {
        this.selectedSlotCount = 0
      } else {
        this.selectedSlotCount = 1
      }
    },
  },
  async created () {
    this.minSlotDate = new Date()
  },
  methods: {
    async trySetPickupSlots () {
      try {
        if (!this.selectedSlotDate || !this.selectedSlotTime) {
          return
        }

        const combinedDateTime = new Date(this.selectedSlotDate)

        const timeParts = this.selectedSlotTime.split(':')
        const hours = parseInt(timeParts[0])
        const minutes = parseInt(timeParts[1])
        const seconds = parseInt(timeParts[2])

        combinedDateTime.setHours(hours)
        combinedDateTime.setMinutes(minutes)
        combinedDateTime.setSeconds(seconds)

        if (!this.selectedSlotCount || this.slotDescription === '') {
          this.slotDescription = null
        }

        await setPickupSlots(this.storeId, combinedDateTime, this.selectedSlotCount, this.slotDescription)
      } catch {
        pulseError(this.$i18n('storeedit.unsuccess'))
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.custom-modal-width {
  max-width: 300px;
  width: 100%;
}
</style>
