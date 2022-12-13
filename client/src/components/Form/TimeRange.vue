<template>
  <div>
    <b-form-group class="mb-3 datepicker">
      <label for="input-startdate">{{ $i18n('timerange.start_date') }}</label>
      <b-form-row class="ml-1">
        <b-col class="date-input">
          <b-form-datepicker
            id="input-startdate"
            v-model="startDate"
            today-button
            class="mb-2"
            v-bind="labelsCalendar || {}"
            :locale="locale"
            :min="new Date()"
            :state="$v.startDateTime.$error ? false : null"
            @input="moveAlongEndDate"
          />
        </b-col>
        <b-col class="time-input">
          <b-form-timepicker
            id="input-startdatetime"
            v-model="startTime"
            minutes-step="5"
            :locale="locale"
            v-bind="labelsTimepicker || {}"
            :state="$v.startDateTime.$error ? false : null"
            @input="handleStartTimeInput()"
          />
        </b-col>
      </b-form-row>
      <div
        v-if="$v.startDateTime.$error"
        class="invalid-feedback"
      >
        {{ $i18n('timerange.start_date_error') }}
      </div>
    </b-form-group>
    <b-form-group
      :label="$i18n('timerange.end_date')"
      class="mb-3 datepicker"
    >
      <b-form-row class="ml-2">
        <b-col class="date-input">
          <b-form-datepicker
            id="input-enddate"
            v-model="endDate"
            class="mb-2"
            v-bind="labelsCalendar || {}"
            :locale="locale"
            :min="new Date()"
            :state="$v.endDateTime.$error ? false : null"
            @input="moveAlongStartDate"
          />
        </b-col>
        <b-col class="time-input">
          <b-form-timepicker
            id="input-enddatetime"
            v-model="endTime"
            minutes-step="5"
            :locale="locale"
            v-bind="labelsTimepicker || {}"
            :state="$v.endDateTime.$error ? false : null"
            @input="$v.endDateTime.$touch()"
          />
        </b-col>
      </b-form-row>
      <div
        v-if="$v.endDateTime.$error"
        class="invalid-feedback"
      >
        {{ $i18n('timerange.end_date_error') }}
      </div>
    </b-form-group>
  </div>
</template>
<script>
import {
  BFormGroup,
  BFormDatepicker,
  BFormTimepicker,
} from 'bootstrap-vue'
import { required } from 'vuelidate/lib/validators'
import i18n, { locale } from '@/helper/i18n'

const EDIT_TIME_HOURS = 1

function isAfterStart (dateTime) {
  return dateTime > this.startDateTime
}

function isAfterEditTime (dateTime) {
  return dateTime > new Date(new Date().getTime() + EDIT_TIME_HOURS * 60 * 60 * 1000)
}

export default {
  components: {
    BFormGroup,
    BFormDatepicker,
    BFormTimepicker,
  },
  validations: {
    startDateTime: { required, isAfterEditTime },
    endDateTime: { required, isAfterStart },
  },
  props: {
    value: { type: Array, default: () => [null, null] },
  },
  data () {
    return {
      locale: locale,
      startDate: null,
      startTime: null,
      endDate: null,
      endTime: null,
      labelsTimepicker: ['Hours', 'Minutes', 'Seconds', 'Increment', 'Decrement', 'Selected', 'NoTimeSelected', 'CloseButton']
        .reduce((obj, key) => ({ ...obj, ['label' + key]: i18n('timepicker.label' + key) }), {}),
      labelsCalendar: ['PrevYear', 'PrevMonth', 'CurrentMonth', 'NextMonth', 'NextYear', 'Today', 'Selected', 'NoDateSelected', 'Calendar', 'Nav', 'Help', 'TodayButton']
        .reduce((obj, key) => ({ ...obj, ['label' + key]: i18n('calendar.label' + key) }), {}),
    }
  },
  computed: {
    startDateTime () {
      return new Date(Date.parse(this.startDate + ' ' + this.startTime))
    },
    endDateTime () {
      return new Date(Date.parse(this.endDate + ' ' + this.endTime))
    },
  },
  methods: {
    moveAlongStartDate () {
      if (this.endDate < this.startDate) {
        this.startDate = this.endDate
      }
      this.handleInput()
    },
    moveAlongEndDate () {
      if (!this.endDate || this.startDate > this.endDate) {
        this.endDate = this.startDate
      }
      this.handleInput()
    },
    handleStartTimeInput () {
      this.$v.startDateTime.$touch()
      this.handleInput()
    },
    handleEndTimeInput () {
      this.$v.endDateTime.$touch()
      this.handleInput()
    },
    handleInput () {
      this.$emit('input', [this.startDateTime, this.endDateTime])
    },
  },
}
</script>

<style lang="scss" scoped>
#input-num-options {
  width: 120px;
}

// Override weird .form-control height styling from bootstrap-theme
// See https://gitlab.com/foodsharing-dev/foodsharing/-/issues/975
.datepicker ::v-deep .b-form-time-control .form-control.b-form-spinbutton.flex-column {
  height: auto;
}

.invalid-feedback {
  font-size: 100%;
  display: unset;
}

.date-input {
  flex-basis: 400px;
}
.time-input {
  flex-basis: 200px;
}

</style>
