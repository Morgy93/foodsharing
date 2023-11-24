<template>
  <b-form
    class="p-1 date-range-picker"
    inline
  >
    <b-form-datepicker
      v-model="fromDate"
      v-b-tooltip.hover
      class="date-picker-from"
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
    />

    <hr class="date-separator">

    <b-form-datepicker
      v-model="toDate"
      v-b-tooltip.hover
      class="date-picker-to"
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
    />
  </b-form>
</template>

<script>
export default {
  props: {
    cooperationStart: { type: String, default: null },
    maxAgeInMonths: { type: Number, default: 0 },
  },
  data () {
    const now = new Date()
    const toDate = new Date(now)

    let minDateFrom = new Date(Date.parse(this.cooperationStart))
    if (isNaN(minDateFrom)) {
      minDateFrom = new Date()
      minDateFrom.setFullYear(now.getFullYear() - 10) // 10 years back
    }
    if (this.maxAgeInMonths) {
      minDateFrom = new Date()
      minDateFrom.setMonth(minDateFrom.getMonth() - this.maxAgeInMonths)
    }

    let fromDate = new Date()
    fromDate.setDate(now.getDate() - 7)
    if (fromDate < minDateFrom) {
      fromDate = new Date(minDateFrom)
    }

    const dateFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: 'numeric',
      weekday: 'short',
    }

    const calendarLabels = {
      labelPrevYear: this.$i18n('calendar.labelPrevYear'),
      labelPrevMonth: this.$i18n('calendar.labelPrevMonth'),
      labelCurrentMonth: this.$i18n('calendar.labelCurrentMonth'),
      labelNextMonth: this.$i18n('calendar.labelNextMonth'),
      labelNextYear: this.$i18n('calendar.labelNextYear'),
      labelToday: this.$i18n('calendar.labelToday'),
      labelSelected: this.$i18n('calendar.labelSelected'),
      labelNoDateSelected: this.$i18n('calendar.labelNoDateSelected'),
      labelCalendar: this.$i18n('calendar.labelCalendar'),
      labelNav: this.$i18n('calendar.labelNav'),
      labelHelp: this.$i18n('calendar.labelHelp'),
    }

    return {
      fromDate,
      toDate,
      maxDateTo: now,
      minDateFrom,
      dateFormatOptions,
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
  },
  methods: {
    getDateRange () {
      return [this.fromDate, this.toDate]
    },
  },
}
</script>

<style lang="scss" scoped>
.date-range-picker{
  justify-content: center;
}
.date-separator {
  width: 1rem;
  border-top-color: var(--fs-border-default);
}
</style>
