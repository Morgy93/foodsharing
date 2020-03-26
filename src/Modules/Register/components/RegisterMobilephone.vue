<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label for="mobile">{{ $i18n('register.login_mobile_phone') }}</label>
    </div>
    <div class="col-sm-auto">
      <vue-tel-input
        :v-model="mobile"
        v-bind="telInputProps"
        @update="$emit('input:mobile', $event.number)"
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
        @click.prevent="$emit('next')"
      >
        {{ $i18n('register.next') }}
      </button>
    </div>
  </form>
</template>
<script>
import { VueTelInput } from 'vue-tel-input'

export default {
  components: {
    VueTelInput
  },
  props: { mobile: { type: String, default: null } },
  data () {
    return {
      telInputProps: {
        mode: 'international',
        defaultCountry: 'DE',
        disabledFetchingCountry: true,
        placeholder: 'Beispiel: 17912345678',
        preferredCountries: ['DE', 'AT', 'CH'],
        name: 'mobilephone',
        maxLen: 15,
        validCharactersOnly: true
      }
    }
  }
}
</script>
