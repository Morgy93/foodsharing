<template>
  <div class="store-desc bootstrap rounded list-group mb-2">
    <div
      class="list-group-item py-2 text-white font-weight-bold bg-primary d-flex justify-content-between"
      v-html="$i18n('store.actions')"
    />
    <button
      v-if="teamConversionId != null && userIsInStore"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(teamConversionId)"
      v-html="$i18n('store.chat.team')"
    />
    <button
      v-if="springerConversationId != null && userIsInStore || isJumper"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(springerConversationId)"
      v-html="$i18n('store.chat.jumper')"
    />
    <button
      v-if="mayEditStore"
      type="button"
      class="list-group-item list-group-item-action"
      @click="goToStoreEdit"
      v-html="$i18n('storeedit.bread')"
    />
    <button
      v-if="mayEditStore"
      type="button"
      class="list-group-item list-group-item-action"
      href="#"
      @click="loadEditRecurringPickupModal"
      v-html="$i18n('pickup.edit.bread')"
    />
    <button
      v-if="mayLeaveStoreTeam && userIsInStore || isJumper"
      type="button"
      class="list-group-item list-group-item-action"
      href="#"
      @click="removeFromTeam(fsId, $i18n('storeedit.team.leave_myself'))"
      v-html="$i18n('storeedit.team.leave')"
    />
  </div>
</template>

<script>
import conv from '@/conv'
import $ from 'jquery'
import { pulseError } from '@/script'
import DataUser from '@/stores/user'
import { removeStoreMember } from '@/api/stores'

export default {
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
    openChat (fsId) {
      conv.chat(fsId)
    },
    loadEditRecurringPickupModal () {
      $('#bid').val(this.storeId)
      $('#editpickups').dialog('open')
    },
    goToStoreEdit () {
      window.location.href = this.$url('storeEdit', this.storeId)
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
