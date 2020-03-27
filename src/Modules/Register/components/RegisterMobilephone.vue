<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label>{{ $i18n('register.login_mobile_phone') }}</label>
    </div>
    <div class="col-sm-auto">
      <vue-tel-input
        :value="mobile"
        v-bind="telInputProps"
        :class="{ 'is-invalid': !isValid }"
        @input="update"
        @validate="validate"
      />
    </div>
    <div class="mt-3 col-sm-auto">
      <div class="msg-inside info">
        <i class="fas fa-info-circle" /> {{ $i18n('register.login_phone_info') }}
      </div>
    </div>
    <div class="col-sm-auto">
      <button
        class="btn btn-secondary mt-3"
        type="button"
        @click="$emit('prev')"
      >
        {{ $i18n('register.prev') }}
      </button>
      <button
        class="btn btn-secondary mt-3"
        type="submit"
        @click.prevent="redirect()"
      >
        {{ $i18n('register.next') }}
      </button>
    </div>
  </form>
</template>
<script>
import { VueTelInput } from 'vue-tel-input'
import i18n from '@/i18n'

export default {
  components: {
    VueTelInput
  },
  data () {
    return {
      isValid: false,
      telInputProps: {
        mode: 'international',
        defaultCountry: 'DE',
        disabledFetchingCountry: true,
        placeholder: i18n('register.phone_example'),
        preferredCountries: ['DE', 'AT', 'CH'],
        name: 'mobilephone',
        maxLen: 18,
        validCharactersOnly: true
      }
    }
  },
  methods: {
    update (phoneNumber, phoneObject) {
      this.isValid = phoneObject.isValid
      this.$emit('update:mobile', phoneNumber)
    },
    validate (phoneObject) {
      this.isValid = phoneObject.isValid
    },
    redirect () {
      if (this.isValid || this.mobile === null || this.mobile === '') {
        this.$emit('next')
      }
    }
  }
}
</script>
<style lang="scss">
.is-invalid {
    border-color: red;
}
</style>
