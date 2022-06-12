<template>
  <div class="store-desc bootstrap rounded list-group mb-2">
    <div
      class="list-group-item py-2 text-white font-weight-bold bg-primary d-flex justify-content-between"
      v-html="$i18n('store.actions')"
    />
    <button
      v-if="teamConversionId != null"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(teamConversionId)"
      v-html="$i18n('store.chat.team')"
    />
    <button
      v-if="springerConversationId != null"
      type="button"
      class="list-group-item list-group-item-action"
      @click="openChat(springerConversationId)"
      v-html="$i18n('store.chat.jumper')"
    />
    <a
      v-if="mayEditStore"
      type="button"
      class="list-group-item list-group-item-action"
      :href="$url('storeEdit',storeId)"
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
      v-if="mayLeaveStoreTeam"
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
import { removeFromTeam } from '../../Store/Store'

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
  },
  methods: {
    openChat (fsId) {
      conv.chat(fsId)
    },
    loadEditRecurringPickupModal () {
      $('#bid').val(this.storeId)
      $('#editpickups').dialog('open')
    },
    removeFromTeam,
  },
}
</script>
<style lang="scss" scoped>
.list-group-item:not(:last-child) {
  border-bottom: 0;
}
</style>
