<template>
  <div>
    <form @submit.prevent>
      <label
        for="nameInput"
        class="my-2"
        v-text="$i18n('name')"
      />
      <input
        id="nameInput"
        ref="name-input"
        v-model="$v.name.$model"
        type="text"
        class="my-2"
        :class="{ 'is-invalid': $v.name.$error }"
      >
      <div
        v-if="$v.name.$error"
        class="invalid-feedback"
        v-text="$i18n('region.form.invalid_name')"
      />

      <label
        for="mailboxInput"
        class="my-2"
      >
        {{ $i18n('mailbox_name') }}<br>
        {{ $i18n('region.mail.name-info') }}
      </label>
      <input
        id="mailboxInput"
        ref="mailbox-input"
        v-model="$v.mailbox.$model"
        type="text"
        class="my-2"
        :class="{ 'is-invalid': $v.mailbox.$error }"
      >
      <div
        v-if="$v.mailbox.$error"
        class="invalid-feedback"
        v-text="$i18n('region.form.invalid_name')"
      />

      <label
        for="mailboxNameInput"
        class="my-2"
        v-text="$i18n('region.mail.sender')"
      />
      <input
        id="mailboxNameInput"
        ref="mailbox-name-input"
        v-model="mailboxName"
        type="text"
        class="my-2"
      >

      <label
        for="typeInput"
        class="my-2"
        v-text="$i18n('region.type.title')"
      />
      <select
        id="typeInput"
        v-model="type"
        class="my-2"
      >
        <option
          v-for="(value, key) in regionTypes"
          :key="key"
          :value="value.id"
          class="m-2"
          v-html="$i18n('region.type.' + value.name)"
        />
      </select>

      <label
        for="workingGroupFunctionInput"
        class="my-2"
        v-text="$i18n('group.function.title')"
      />
      <select
        id="workingGroupFunctionInput"
        v-model="workingGroupFunction"
        class="my-2"
        :disabled="type !== 7"
      >
        <option
          v-for="(value, key) in workingGroupFunctions"
          :key="key"
          :value="value.id"
          class="m-2"
          v-html="$i18n('group.function.' + value.name)"
        />
      </select>

      <div class="mt-3">
        <button
          class="btn btn-primary"
          :disabled="$v.$invalid"
          @click="submitForm"
          v-text="$i18n('button.save')"
        />
      </div>
    </form>
  </div>
</template>

<script>
import { updateRegion } from '@/api/regions'
import { pulseSuccess, pulseError } from '@/script'
import { required, minLength } from 'vuelidate/lib/validators'

export default {
  props: {
    regionDetails: { type: Object, default: null },
  },
  data () {
    return {
      regionId: null,
      name: null,
      mailbox: null,
      mailboxName: null,
      type: null,
      workingGroupFunction: null,
      regionTypes: [
        { id: 6, name: 'country' },
        { id: 5, name: 'state' },
        { id: 3, name: 'region' },
        { id: 8, name: 'bigcity' },
        { id: 1, name: 'city' },
        { id: 2, name: 'district' },
        { id: 9, name: 'townpart' },
        { id: 7, name: 'workgroup' },
      ],
      workingGroupFunctions: [
        { id: 1, name: 'welcome' },
        { id: 2, name: 'voting' },
        { id: 3, name: 'fsp' },
        { id: 4, name: 'stores' },
        { id: 5, name: 'report' },
        { id: 6, name: 'mediation' },
        { id: 7, name: 'arbitration' },
        { id: 8, name: 'fsmanagement' },
        { id: 9, name: 'pr' },
        { id: 10, name: 'moderation' },
        { id: 11, name: 'board' },
      ],
    }
  },
  validations: {
    name: { required, minLength: minLength(1) },
    mailbox: { required, minLength: minLength(1) },
  },
  watch: {
    regionDetails () {
      Object.assign(this, this.regionDetails)
    },
  },
  methods: {
    async submitForm () {
      try {
        const groupFunction = this.type === 7 ? this.workingGroupFunction : null
        await updateRegion(this.regionDetails.id, this.name, this.mailbox, this.mailboxName, this.type, groupFunction)

        // refresh the parent region's children to refresh the updated region
        this.$emit('region-updated', this.regionDetails.id, this.regionDetails.parentId)

        pulseSuccess(this.$i18n('region.edit_success'))
      } catch (e) {
        console.error(e)
        pulseError(this.$i18n('error_unexpected'))
      }
    },
  },
}
</script>

<style type="text/scss" scoped>
</style>
