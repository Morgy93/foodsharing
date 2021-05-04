<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="bootstrap">
    <div class="container">
      <b-form>
        <b-col cols="8" align-v="stretch">
          <b-form-select v-model="selectedRole" class="row my-2" :options="roleChoices" />
        </b-col>
        <b-form-group class="my-2">
          <b-form-checkbox v-model="includeAddress">
            {{ $i18n('bcard.includeAddress') }}
          </b-form-checkbox>
          <b-form-checkbox v-model="includePhone">
            {{ $i18n('bcard.includePhone') }}
          </b-form-checkbox>
          <b-form-checkbox v-model="createQRCode">
            {{ $i18n('bcard.createQRCode') }}
          </b-form-checkbox>
        </b-form-group>

        <b-link class="btn btn-sm btn-secondary mb-3" :disabled="!selectedRole" @click="createBusinessCard">
          {{ $i18n('bcard.generate') }}
        </b-link>
      </b-form>
    </div>
  </div>
</template>

<script>
import { BForm, BFormGroup, BFormSelect, BLink, BFormCheckbox, BCol } from 'bootstrap-vue'
import { goTo } from '@/script'

export default {
  components: { BForm, BFormGroup, BFormSelect, BLink, BFormCheckbox, BCol },
  props: {
    roles: { type: Array, default: () => { return [] } },
  },
  data () {
    return {
      roleChoices: [],
      selectedRole: null,
      includeAddress: true,
      includePhone: true,
      createQRCode: false,
    }
  },
  mounted () {
    this.roleChoices = this.roles
    this.roleChoices.unshift({ value: null, text: this.$i18n('select') })
  },
  methods: {
    createBusinessCard () {
      goTo(this.$url('businessCard', this.selectedRole, this.includeAddress ? 1 : 0, this.includePhone ? 1 : 0, this.createQRCode ? 1 : 0))
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
