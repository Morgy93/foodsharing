<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ title }}
      </div>
      <div class="card-body">
        {{ $v }}
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
              v-model="$v.name.$model"
              trim
              :state="$v.name.$error ? false : null"
            />
            <div
              v-if="$v.name.$error"
              class="invalid-feedback"
            >
              {{ $i18n('events.create.name.error') }}
            </div>
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

export default {
  components: {
    BForm,
    BFormGroup,
    BFormInput,
  },
  props: {
    new: { type: Boolean, required: true },
    event: {
      type: Object,
      default: () => ({
        name: '',
      }),
    },
  },
  data () {
    return {
      name: this.event.name,
    }
  },
  validations: {
    name: { required, minLength: minLength(1) },
  },
  computed: {
    title () {
      return this.$i18n(`events.${this.new ? 'create' : 'edit'}.title`)
    },
  },
  methods: {
    async submit () {
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
