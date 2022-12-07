<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        {{ $i18n('events.create.title') }}
      </div>
      <div class="card-body">
        <b-form
          @submit="submit"
        >
          <b-form-group
            :label="$i18n('group.name')"
            label-for="input-name"
            class="mb-4"
          />
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
// import { required, minLength } from 'vuelidate/lib/validators'
// import i18n from '@/helper/i18n'
// import { hideLoader, pulseError, pulseSuccess, showLoader } from '@/script'

export default {
  props: {
    new: { type: Boolean, required: true },
  },
  computed: {
    title () {
      return this.i18n(`events.${this.new ? 'create' : 'edit'}.title`)
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
