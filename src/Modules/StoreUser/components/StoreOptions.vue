<template>
  <div class="store-desc bootstrap rounded list-group mb-2">
    <StoreInformationModal :store-id="storeId" />
    <div
      class="list-group-item py-2 text-white font-weight-bold bg-primary d-flex justify-content-between"
      v-html="$i18n('store.actions')"
    />
    <button
      v-if="teamConversionId != null && userIsInStore"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(teamConversionId)"
      v-text="$i18n('store.chat.team')"
    />
    <button
      v-if="springerConversationId != null && userIsInStore || isJumper"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(springerConversationId)"
      v-html="$i18n('store.chat.jumper')"
    />
    <button
      type="button"
      class="list-group-item list-group-item-action"
      @click="$bvModal.show('storeInformationModal')"
      v-text="$i18n('storeview.show_information')"
    />
    <button
      v-if="mayEditStore"
      type="button"
      class="list-group-item list-group-item-action"
      href="#"
      @click="loadEditRecurringPickupModal"
      v-text="$i18n('pickup.edit.bread')"
    />
    <button
      v-if="mayLeaveStoreTeam && userIsInStore || isJumper"
      type="button"
      class="list-group-item list-group-item-action"
      href="#"
      @click="removeFromTeam(fsId, $i18n('storeedit.team.leave_myself'))"
      v-text="$i18n('storeedit.team.leave')"
    />
  </div>
</template>

<script>
import conversationStore from '@/stores/conversations'
import $ from 'jquery'
import { pulseError } from '@/script'
import DataUser from '@/stores/user'
import { removeStoreMember } from '@/api/stores'
import StoreInformationModal from './StoreInformationModal.vue'

export default {
  components: {
    StoreInformationModal,
  },
  props: {
    fsId: { type: Number, required: true },
    mayLeaveStoreTeam: { type: Boolean, default: false },
    teamConversionId: {
      type: Number,
      default: null,
    },
    springerConversationId: {
      type: Number,
      default: null,
    },
    mayEditStore: {
      type: Boolean,
      default: false,
    },
    storeId: {
      type: Number,
      default: null,
    },
    userIsInStore: { type: Boolean, default: false },
    isJumper: { type: Boolean, default: false },
  },
  methods: {
    openChat (conversationId) {
      conversationStore.openChat(conversationId)
    },
    loadEditRecurringPickupModal () {
      $('#bid').val(this.storeId)
      $('#editpickups').dialog('open')
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
