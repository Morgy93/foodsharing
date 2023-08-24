<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <Container :title="$i18n('pickup.history.title')" :container-is-expanded="isContainerExpanded" tag="pickup_history">
    <div class="corner-bottom margin-bottom bootstrap pickup-history">
      <b-form id="pickup-date-form" class="p-1" inline>
        <label class="sr-only" for="datepicker-from">From date:</label>
        <b-form-datepicker
          id="datepicker-from"
          v-model="fromDate"
          v-b-tooltip.hover
          v-bind="calendarLabels"
          :value-as-date="true"
          :date-format-options="dateFormatOptions"
          selected-variant="secondary"
          :max="maxDateFrom"
          :min="minDateFrom"
          :required="true"
          form="pickup-date-form"
          :hide-header="true"
          :start-weekday="1"
          :locale="$i18n('calendar.locale')"
          :title="$i18n('date.from')"
          no-highlight-today
        />

        <hr class="w-auto date-separator">

        <label class="sr-only" for="datepicker-from">To date:</label>
        <b-form-datepicker
          id="datepicker-to"
          v-model="toDate"
          v-b-tooltip.hover
          v-bind="calendarLabels"
          :value-as-date="true"
          :date-format-options="dateFormatOptions"
          selected-variant="secondary"
          :max="maxDateTo"
          :min="minDateTo"
          :hide-header="true"
          :start-weekday="1"
          :locale="$i18n('calendar.locale')"
          :title="$i18n('date.to')"
          no-highlight-today
        />
      </b-form>
      <div class="p-1 pickup-search-button">
        <b-button
          variant="secondary"
          size="sm"
          class="d-block mx-auto"
          :class="{'disabled': !searchable}"
          @click.prevent="searchHistory"
        >
          <i class="fas fa-fw fa-search" />
          {{ $i18n('pickup.history.search') }}
        </b-button>
      </div>

      <div class="p-1 pickup-table">
        <SignoutHistory :store-id="storeId" />

        <Pickup
          v-for="pickupDate in pickupList"
          :key="`${pickupDate[0].storeId}-${pickupDate[0].date_ts}`"
          v-bind="pickupDate"
          :date="pickupDate[0].date"
          :store-id="pickupDate[0].storeId"
          :store-title="pickupDate[0].storeTitle"
          :occupied-slots="pickupDate"
          class="pickup-block"
        />
      </div>
    </div>
  </Container>
</template>

<script>
import { listPastPickupsForUser, listPickupHistory } from '@/api/pickups'
import i18n from '@/helper/i18n'
import { pulseError } from '@/script'
import Pickup from '@/components/Stores/Pickup/Pickup.vue'
import SignoutHistory from '@/components/Stores/SignoutHistory/SignoutHistory.vue'
import Container from '@/components/Container/Container.vue'

const calendarLabels = {
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
}

export default {
  components: { SignoutHistory, Pickup, Container },
  props: {
    collapsedAtFirst: { type: Boolean, default: true },
    fsId: { type: Number, default: null },
    storeId: { type: Number, default: null },
    coopStart: { type: String, default: '' },
  },
  data () {
    let fromDate = null
    if (this.fsId) {
      fromDate = new Date()
      fromDate.setDate(fromDate.getDate() - 2 * 7) // subtract 2 weeks
    }
    const maxDate = new Date()
    let minDate = new Date()
    if (this.fsId) {
      minDate = new Date()
      minDate.setDate(minDate.getDate() - 4 * 7) // subtract 4 weeks
    } else {
      minDate.setDate(minDate.getDate() - (54 * 7) * 10) // subtract 54 * 10 weeks, around 10 years
    }

    const dateFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: 'numeric',
      weekday: 'short',
    }

    return {
      isContainerExpanded: false,
      isLoading: false,
      fromDate,
      toDate: maxDate,
      dateFormatOptions,
      maxDateTo: maxDate,
      minDateFrom: this.coopStart ? new Date(Math.max(...[minDate, new Date(Date.parse(this.coopStart))].map(date => date.getTime()))) : minDate,
      pickupList: [],
      calendarLabels,
    }
  },
  computed: {
    minDateTo () {
      return this.fromDate || this.minDateFrom
    },
    maxDateFrom () {
      return this.toDate || this.maxDateTo
    },
    searchable () {
      return !this.isLoading && this.fromDate && this.toDate
    },
  },
  methods: {
    toggleDisplay () {
      this.display = !this.display
    },
    async searchHistory () {
      if (!this.searchable) {
        return
      }
      if (this.storeId === null && !this.fsId) {
        return
      }
      this.isLoading = true

      try {
        const startDate = new Date(this.fromDate)
        startDate.setHours(0, 0, 0, 0)
        const toDate = new Date(Math.min(...[new Date(), this.toDate].map(date => date.getTime())))

        if (this.fsId) {
          this.pickupList = await listPastPickupsForUser(
            this.fsId,
            startDate,
            toDate,
          )
        } else {
          this.pickupList = await listPickupHistory(
            this.storeId,
            startDate,
            toDate,
          )
        }
      } catch (e) {
        pulseError(i18n('error_unexpected') + e)
      }
      this.isLoading = false
    },
    when (dt) {
      return new Date(Date.parse(dt))
    },
  },
}
</script>

<style lang="scss" scoped>
.pickup-history {
  background: var(--fs-color-light);

  ::v-deep .form-inline .form-control.b-calendar-grid {
    width: 100%;
  }

  .date-separator {
    border-top-color: var(--fs-border-default);
  }
  .date-separator::after {
    content: '->';
    visibility: hidden;
  }
}
</style>
