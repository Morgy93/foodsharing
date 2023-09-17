<template>
  <b-button-group>
    <b-button
      v-if="teamConversationId != null && isUserInStore"
      @click="openChat(teamConversationId)"
    >
      {{ $i18n('store.chat.team') }}
    </b-button>
    <b-button
      v-if="jumperConversationId != null && isUserInStore || isJumper"
      @click="openChat(jumperConversationId)"
    >
      {{ $i18n('store.chat.jumper') }}
    </b-button>
    <b-button
      v-if="mayLeaveStoreTeam && isUserInStore || isJumper"
      @click="removeFromTeam(fsId, $i18n('storeedit.team.leave_myself'))"
    >
      {{ $i18n('storeedit.team.leave') }}
    </b-button>
  </b-button-group>
</template>

<script>
import conversationStore from '@/stores/conversations'
import { pulseError } from '@/script'
import DataUser from '@/stores/user'
import { removeStoreMember } from '@/api/stores'

export default {
  props: {
    storeName: { type: String, required: true },
    fsId: { type: Number, required: true },
    mayLeaveStoreTeam: { type: Boolean, default: false },
    teamConversationId: {
      type: Number,
      default: null,
    },
    jumperConversationId: {
      type: Number,
      default: null,
    },
    mayEditStore: {
      type: Boolean,
      default: null,
    },
    isCoordinator: {
      type: Boolean,
      default: null,
    },
    storeId: {
      type: Number,
      default: null,
    },
    isUserInStore: { type: Boolean, default: false },
    isJumper: { type: Boolean, default: false },
    mayDoPickup: { type: Boolean, default: false },
    isVerified: { type: Boolean, default: false },
  },
  methods: {
    openChat (conversationId) {
      conversationStore.openChat(conversationId)
    },
    async removeFromTeam (fsId, fsName) {
      if (!fsId) {
        return
      }
      if (!confirm(this.$i18n('store.sm.reallyRemove', { name: fsName }))) {
        return
      }
      this.isBusy = true
      try {
        await removeStoreMember(this.storeId, DataUser.getters.getUserId())
        window.location.href = this.$url('dashboard')
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      this.isBusy = false
    },
  },
}
</script>
<style lang="scss" scoped>
.list-group-item:not(:last-child) {
  border-bottom: 0;
}
</style>
