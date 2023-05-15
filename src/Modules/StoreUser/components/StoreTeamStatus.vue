<template>
  <div class="store-desc bootstrap rounded list-group mb-2">
    <div
      class="list-group-item py-2 text-white font-weight-bold bg-primary d-flex justify-content-between"
      v-html="$i18n('storeview.status')"
    />
    <div
      class="list-group-item list-group-item-action"
    >
      <b-form-select
        v-model="teamStatusSelected"
        :disabled="teamStatusSelected==null || isLoading"
        :options="options"
        size="sm"
        class="mt-3"
        @change="trySetStoreTeamStatus"
      />
    </div>
  </div>
</template>

<script>
import { setStoreTeamStatus, getStoreDetails } from '@/api/stores'
import { hideLoader, pulseError, showLoader } from '@/script'

export default {
  props: {
    storeId: { type: Number, required: true },
  },
  data () {
    return {
      teamStatusSelected: null,
      isLoading: false,
      options: [
        { value: 0, text: this.$i18n('store.team.isfull') },
        { value: 1, text: this.$i18n('menu.entry.helpwanted') },
        { value: 2, text: this.$i18n('menu.entry.helpneeded') },
      ],
    }
  },
  async mounted () {
    let value
    try {
      this.isLoading = true
      value = await getStoreDetails(this.storeId)
    } catch (e) {
      pulseError(this.$i18n('error_unexpected'))
    }
    this.isLoading = false
    this.teamStatusSelected = value.store.teamStatus
  },
  methods: {
    async trySetStoreTeamStatus () {
      showLoader()
      try {
        await setStoreTeamStatus(this.storeId, this.teamStatusSelected)
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }
      hideLoader()
    },
  },
}
</script>
