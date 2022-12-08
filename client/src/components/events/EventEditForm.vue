<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ title }}
      </div>
      <div class="card-body">
        {{ event }}
        <b-form
          @submit="submit"
        >
          <b-form-group
            :label="$i18n('events.create.name.label')"
            label-for="input-name"
            class="mb-4"
          >
            <b-form-input
              id="input-name"
              v-model="$v.event.name.$model"
              trim
              :state="$v.event.name.$error ? false : null"
            />
            <div
              v-if="$v.event.name.$error"
              class="invalid-feedback"
            >
              {{ $i18n('events.create.name.error') }}
            </div>
          </b-form-group>

          <b-form-group
            :label="$i18n('events.create.description.label')"
            label-for="description-name"
            class="mb-4"
          >
            <b-form-textarea
              id="input-description"
              v-model="$v.event.description.$model"
              trim
              rows="2"
              max-rows="15"
              :state="$v.event.description.$error ? false : null"
            />
            <div
              v-if="$v.event.description.$error"
              class="invalid-feedback"
            >
              {{ $i18n('events.create.description.error') }}
            </div>
          </b-form-group>

          <b-form-group
            :label="$i18n('events.create.region.label')"
            label-for="input-region"
            class="mb-4"
          >
            <b-form-select
              id="input-region"
              v-model="$v.event.region.$model"
              :state="$v.event.region.$error ? false : null"
            >
              <b-form-select-option
                :value="null"
              >
                Bezirk oder Arbeitsgruppe auswählen
              </b-form-select-option>
              <b-form-select-option-group label="Bezirke">
                <b-form-select-option
                  v-for="r in actualRegions"
                  :key="r.id"
                  :value="r.id"
                >
                  {{ r.name }}
                </b-form-select-option>
              </b-form-select-option-group>
              <b-form-select-option-group label="Arbeitsgruppen">
                <b-form-select-option
                  v-for="r in workingGroups"
                  :key="r.id"
                  :value="r.id"
                >
                  {{ r.name }}
                </b-form-select-option>
              </b-form-select-option-group>
            </b-form-select>
            <div
              v-if="$v.event.region.$error"
              class="invalid-feedback"
            >
              {{ $i18n('events.create.region.error') }}
            </div>
          </b-form-group>
          <b-form-group
            :label="$i18n('events.create.region.label')"
            label-for="input-region"
            class="mb-4"
          >
            <b-form-select
              id="input-region"
              v-model="$v.event.meetingType.$model"
              :options="[
                {value: 1, text: $i18n('events.create.meeting_type.offline')},
                {value: 2, text: $i18n('events.create.meeting_type.online')},
                {value: 0, text: $i18n('events.create.meeting_type.mumble')},
              ]"
            />
          </b-form-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
// import { required, minLength } from 'vuelidate/lib/validators'
// import i18n from '@/helper/i18n'
// import { hideLoader, pulseError, pulseSuccess, showLoader } from '@/script'
import {
  BForm,
  BFormGroup,
  BFormInput,
} from 'bootstrap-vue'
import { required, minLength } from 'vuelidate/lib/validators'
// import Datepicker from '@vuepic/vue-datepicker'
// import '@vuepic/vue-datepicker/dist/main.css'

const WORKING_GROUP_TYPE = 7

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
    // Datepicker,
  },
  props: {
    new: { type: Boolean, required: true },
    event: {
      type: Object,
      default: () => ({
        name: '',
        description: '',
        region: null,
        meetingType: 1,
      }),
    },
    regions: { type: Object, required: true },
  },
  validations: {
    event: {
      name: { required, minLength: minLength(1) },
      description: { required, minLength: minLength(1) },
      region: { required },
      meetingType: { },
    },
  },
  computed: {
    title () {
      return this.$i18n(`events.${this.new ? 'create' : 'edit'}.title`)
    },
    actualRegions () {
      return Object.values(this.regions).filter(r => r.type !== WORKING_GROUP_TYPE)
    },
    workingGroups () {
      return Object.values(this.regions).filter(r => r.type === WORKING_GROUP_TYPE)
    },
  },
  methods: {
    async submit (e) {
      e.preventDefault()
      // showLoader()
      // try {
      //   await updateGroup(this.group.id, this.name, this.description, this.photo, this.apply_type, this.required_bananas,
      //     this.required_pickups, this.required_weeks)
      //   pulseSuccess(i18n('group.saved'))
      // } catch (e) {
      //   pulseError(i18n('error_unexpected'))
      // }
      // hideLoader()
    },
  },
}
</script>
<style>

</style>
