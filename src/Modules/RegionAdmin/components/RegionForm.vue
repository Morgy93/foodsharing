<template>
  <div>
    <form @submit.prevent>
      <label
        for="nameInput"
        class="m-2"
        v-text="$i18n('name')"
      />
      <input
        id="nameInput"
        ref="name-input"
        v-model="name"
        type="text"
        class="m-2"
      >

      <label
        for="mailboxInput"
        class="m-2"
      >
        {{ $i18n('mailbox_name') }}<br>
        {{ $i18n('region.mail.name-info') }}
      </label>
      <input
        id="mailboxInput"
        ref="mailbox-input"
        v-model="mailbox"
        type="text"
        class="m-2"
      >

      <label
        for="mailboxNameInput"
        class="m-2"
        v-text="$i18n('region.mail.sender')"
      />
      <input
        id="mailboxNameInput"
        ref="mailbox-name-input"
        v-model="mailboxName"
        type="text"
        class="m-2"
      >

      <label
        for="typeInput"
        class="m-2"
        v-text="$i18n('region.type.title')"
      />
      <select
        id="typeInput"
        v-model="type"
        class="m-2"
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
        class="m-2"
        v-text="$i18n('group.function.title')"
      />
      <select
        id="workingGroupFunctionInput"
        v-model="workingGroupFunction"
        class="m-2"
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
          type="submit"
          class="btn btn-primary"
          @click="submit"
        >
          {{ $i18n('button.save') }}
        </button>
        <hr class="my-3">

        <span
          v-if="regionDetails.name"
          class="ml-2"
        >
          {{ $i18n('region.hull.closure', {region: regionDetails.name}) }}
        </span>
      </div>
    </form>
  </div>
</template>

<script>
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
  watch: {
    regionDetails () {
      Object.assign(this, this.regionDetails)
    },
  },
  methods: {
    submit () {
      // TODO
    },
  },
}
</script>

<style type="text/scss" scoped>
</style>
