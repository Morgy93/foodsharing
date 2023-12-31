<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('poll.new_poll.title') }} in {{ region.name }}
      </div>
      <b-form
        :class="{disabledLoading: isLoading, 'card-body': true}"
        @submit="showConfirmDialog"
      >
        <b-alert
          show
          variant="dark"
        >
          {{ $i18n('polls.hint_2') }}: <a :href="$url('wiki_voting')">{{ $url('wiki_voting') }}</a>
        </b-alert>
        <b-form-group
          :label="$i18n('poll.new_poll.name')"
          label-for="input-name"
          class="mb-4"
        >
          <b-form-input
            id="input-name"
            v-model="$v.name.$model"
            trim
            :state="$v.name.$error ? false : null"
          />
          <div
            v-if="$v.name.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.name_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.scope')"
          class="mb-3"
        >
          <b-form-radio
            v-for="index in possibleScopes"
            :key="index"
            v-model="scope"
            :value="index - 1"
          >
            {{ $i18n('poll.scope_description_' + (index - 1)) }}
          </b-form-radio>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.type')"
          class="mb-4"
        >
          <b-form-radio
            v-for="index in 4"
            :key="index"
            v-model="type"
            :value="index - 1"
          >
            {{ $i18n('poll.type_description_' + (index - 1)) }}
          </b-form-radio>
        </b-form-group>

        <b-form-group class="mb-3 datepicker">
          <b-form-row>
            <b-col>
              <label for="input-startdate">{{ $i18n('poll.new_poll.start_date') }}</label>
            </b-col>
            <b-col class="text-center">
              <label for="input-startdatetime">{{ $i18n('poll.new_poll.time') }}</label>
            </b-col>
          </b-form-row>
          <b-form-row class="ml-1">
            <b-col>
              <b-form-datepicker
                id="input-startdate"
                v-model="startDate"
                today-button
                class="mb-2"
                v-bind="labelsCalendar || {}"
                :locale="locale"
                :min="new Date()"
                :state="$v.startDateTime.$error ? false : null"
                @input="updateDateStartTimes"
              />
            </b-col>
            <b-col>
              <b-form-timepicker
                id="input-startdatetime"
                v-model="startTime"
                :locale="locale"
                v-bind="labelsTimepicker || {}"
                :state="$v.startDateTime.$error ? false : null"
                @input="updateDateStartTimes"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.startDateTime.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.start_date_required') }}
          </div>
        </b-form-group>
        <b-form-group
          :label="$i18n('poll.new_poll.end_date')"
          class="mb-3 datepicker"
        >
          <b-form-row class="ml-2">
            <b-col>
              <b-form-datepicker
                id="input-enddate"
                v-model="endDate"
                class="mb-2"
                v-bind="labelsCalendar || {}"
                :locale="locale"
                :min="startDate"
                :state="$v.endDateTime.$error ? false : null"
                @input="updateDateEndTimes"
              />
            </b-col>
            <b-col>
              <b-form-timepicker
                id="input-enddatetime"
                v-model="endTime"
                :locale="locale"
                v-bind="labelsTimepicker || {}"
                :state="$v.endDateTime.$error ? false : null"
                @input="updateDateEndTimes"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.endDateTime.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.end_date_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.description')"
          class="mb-4"
        >
          <div
            class="mb-2 ml-2"
            v-html="$i18n('forum.markdown_description')"
          />
          <b-form-textarea
            id="input-description"
            v-model="$v.description.$model"
            :placeholder="$i18n('poll.new_poll.description_placeholder')"
            trim
            :state="$v.description.$error ? false : null"
            rows="5"
            class="ml-1"
          />
          <div
            v-if="$v.description.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.description_required') }}
          </div>
        </b-form-group>

        <b-form-group
          :label="$i18n('poll.new_poll.options')"
          label-for="input-name"
          class="mb-4"
        >
          <b-form-row>
            <b-form-spinbutton
              id="input-num-options"
              v-model="numOptions"
              min="2"
              max="200"
              class="m-1 mb-3 mr-5"
              style="width:120px"
              size="sm"
              @input="updateNumOptions"
            />
            <b-form-checkbox
              id="shuffle-options-checkbox"
              v-model="shuffleOptions"
              class="mt-2 mb-3 ml-2"
            >
              {{ $i18n('poll.new_poll.shuffle_options') }}
            </b-form-checkbox>
          </b-form-row>

          <b-form-row
            v-for="index in numOptions"
            :key="index"
            class="row"
          >
            <b-col
              cols="3"
              align-v="stretch"
            >
              {{ $i18n('poll.new_poll.option') }} {{ index }}:
            </b-col>
            <b-col>
              <b-form-input
                id="input-option-0"
                v-model="$v.options.$model[index-1]"
                trim
                :state="$v.options.$error ? false : null"
                class="mr-3 mb-1"
              />
            </b-col>
          </b-form-row>
          <div
            v-if="$v.options.$error"
            class="invalid-feedback"
          >
            {{ $i18n('poll.new_poll.option_texts_required') }}
          </div>
        </b-form-group>

        <b-button
          type="submit"
          variant="primary"
          :disabled="$v.$invalid"
        >
          {{ $i18n('poll.new_poll.submit') }}
        </b-button>
        <div
          v-if="$v.$invalid"
          class="invalid-feedback"
        >
          {{ $i18n('poll.new_poll.missing_fields') }}
        </div>
      </b-form>
    </div>

    <b-modal
      v-if="!isLoading"
      ref="newPollConfirmModal"
      :title="$i18n('poll.new_poll.submit')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="submitPoll"
    >
      {{ $i18n('poll.new_poll.submit_question', { 'date': formattedEditTime }) }}
    </b-modal>
  </div>
</template>

<script>

import {
  BForm,
  BFormGroup,
  BFormInput,
  BFormRadio,
  BFormDatepicker,
  BFormTimepicker,
  BFormTextarea,
  BFormSpinbutton,
  BFormCheckbox,
  BButton,
  BFormRow,
  BCol,
  BModal,
  BAlert,
} from 'bootstrap-vue'
import { createPoll } from '@/api/voting'
import { pulseError } from '@/script'
import dataFormatter from '@/helper/date-formatter'
import i18n, { locale } from '@/helper/i18n'
import { required, minLength } from 'vuelidate/lib/validators'

const EDIT_TIME_HOURS = 1
const DEFAULT_START_TIME_HOURS = 2

function isAfterStart (dateTime) {
  return dateTime > this.startDateTime
}

function isAfterEditTime (dateTime) {
  return dateTime > new Date(new Date().getTime() + EDIT_TIME_HOURS * 60 * 60 * 1000)
}

// returns if the array does not contain duplicate entries
function areEntriesUnique (array) {
  const unique = [...new Set(array)]
  return unique.length === array.length
}

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
    BFormRadio,
    BFormDatepicker,
    BFormTimepicker,
    BFormTextarea,
    BFormSpinbutton,
    BFormCheckbox,
    BButton,
    BFormRow,
    BCol,
    BModal,
    BAlert,
  },
  props: {
    region: {
      type: Object,
      required: true,
    },
    isWorkGroup: {
      type: Boolean,
      required: true,
    },
  },
  data () {
    return {
      isLoading: false,
      name: '',
      scope: 0,
      type: 0,
      startDate: null,
      startTime: null,
      endDate: null,
      endTime: null,
      description: '',
      numOptions: 3,
      shuffleOptions: true,
      options: Array(3).fill(''),
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
    }
  },
  validations: {
    name: { required, minLength: minLength(1) },
    description: { required, minLength: minLength(1) },
    options: {
      required,
      $each: {
        required,
        minLength: minLength(1),
      },
      areEntriesUnique,
    },
    startDateTime: { required, isAfterEditTime },
    endDateTime: { required, isAfterStart },
  },
  computed: {
    startDateTime () {
      return new Date(Date.parse(this.startDate + ' ' + this.startTime))
    },
    endDateTime () {
      return new Date(Date.parse(this.endDate + ' ' + this.endTime))
    },
    possibleScopes () {
      if (this.isWorkGroup) {
        // 'store managers' and 'users with home region' does not make sense in work groups
        return [1, 2]
      } else {
        return [1, 2, 3, 4, 5]
      }
    },
    formattedEditTime () {
      const editDate = new Date(new Date().getTime() + EDIT_TIME_HOURS * 60 * 60 * 1000)
      return dataFormatter.time(editDate)
    },
  },
  mounted () {
    const defaultStart = new Date(new Date().getTime() + DEFAULT_START_TIME_HOURS * 60 * 60 * 1000)
    this.startDate = defaultStart.toISOString().split('T')[0]
    this.startTime = dataFormatter.time(defaultStart)
  },
  methods: {
    updateDateStartTimes () {
      this.$v.startDateTime.$touch()
    },
    updateDateEndTimes () {
      this.$v.endDateTime.$touch()
    },
    updateNumOptions () {
      // the options array must be assigned with a new object for the validation to work
      const newOptions = Array(this.numOptions).fill('')
      for (let i = 0; i < Math.min(this.options.length, this.numOptions); i++) {
        newOptions[i] = this.options[i]
      }
      this.options = newOptions
      this.$v.options.$touch()
    },
    showConfirmDialog (e) {
      e.preventDefault()
      this.$refs.newPollConfirmModal.show()
    },
    async submitPoll (e) {
      e.preventDefault()
      this.isLoading = true
      try {
        const poll = await createPoll(this.region.id, this.name, this.description, this.startDateTime, this.endDateTime, this.scope, this.type, this.options, this.shuffleOptions, true)
        window.location = this.$url('poll', poll.id)
      } catch (e) {
        pulseError(i18n('error_unexpected') + ': ' + e.message)
      }

      this.isLoading = false
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
</style>
