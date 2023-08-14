<template>
  <b-dropdown
    :id="`slot-${uniqueId}`"
    no-caret
    toggle-class="btn p-0 filled"
  >
    <b-tooltip
      :target="`slot-${uniqueId}`"
      triggers="hover blur"
    >
      <div>
        {{ profile.name }}
      </div>
      <div v-if="!confirmed">
        ({{ $i18n('pickup.to_be_confirmed') }})
      </div>
    </b-tooltip>
    <template #button-content>
      <Avatar
        :url="profile.avatar"
        :size="50"
        :class="{'pending': !confirmed, 'confirmed': confirmed}"
      />
      <div :class="{'slotstatus': true, 'pending': !confirmed, 'confirmed': confirmed}">
        <i :class="{'slotstatus-icon fas': true, 'fa-clock': !confirmed, 'fa-check-circle': confirmed}" />
      </div>
    </template>
    <b-dropdown-item :href="`/profile/${profile.id}`">
      <i class="fas fa-fw fa-user" /> {{ $i18n('pickup.open_profile') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowChat && !isMe"
      @click="openChat"
    >
      <i class="fas fa-fw fa-comment" /> {{ $i18n('chat.open_chat') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="phoneNumber && !isMe"
      :href="$url('phone_number', phoneNumber)"
    >
      <i class="fas fa-fw fa-phone" /> {{ $i18n('pickup.call') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="phoneNumber && !isMe"
      @click="copyIntoClipboard(phoneNumber)"
    >
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <i class="fas fa-fw" :class="[canCopy ? 'fa-clone' : 'fa-phone-slash']" />
      <span v-if="canCopy">{{ $i18n('pickup.copyNumber') }}</span>
      <span v-else>{{ phoneNumber }}</span>
    </b-dropdown-item>
    <b-dropdown-item
      v-if="!confirmed && allowConfirm"
      @click="$emit('confirm', profile.id)"
    >
      <i class="fas fa-fw fa-check" /> {{ $i18n('pickup.confirm') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowLeave"
      @click="$emit('leave')"
    >
      <i class="fa fa-fw fa-times-circle" /> {{ $i18n('pickup.leave') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowKick && !allowLeave"
      @click="$emit('kick', profile.id)"
    >
      <i class="fas fa-fw fa-times-circle" /> {{ $i18n('pickup.kick') }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
import Avatar from '@/components/Avatar'
import { BDropdown, BDropdownItem } from 'bootstrap-vue'
import { pulseSuccess } from '@/script'
import PhoneNumbers from '@/helper/phone-numbers'
import conversationStore from '@/stores/conversations'
import DataUser from '@/stores/user'

import { v4 as uuidv4 } from 'uuid'

export default {
  components: { Avatar, BDropdown, BDropdownItem },
  props: {
    profile: {
      type: Object,
      default: null,
    },
    confirmed: {
      type: Boolean,
      default: false,
    },
    allowLeave: {
      type: Boolean,
      default: false,
    },
    allowKick: {
      type: Boolean,
      default: false,
    },
    allowConfirm: {
      type: Boolean,
      default: false,
    },
    allowChat: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      uniqueId: null,
    }
  },
  computed: {
    phoneNumber () {
      return PhoneNumbers.callableNumber(this.profile.mobile || this.profile.landline)
    },
    canCopy () {
      return !!navigator.clipboard
    },
    isMe () {
      return DataUser.getters.getUserId() === this.profile.id
    },
  },
  mounted () {
    this.uniqueId = uuidv4()
  },
  methods: {
    copyIntoClipboard (text) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          pulseSuccess(this.$i18n('pickup.copiedNumber', { number: text }))
        })
      }
    },
    openChat () {
      conversationStore.openChatWithUser(this.profile.id)
    },
  },
}
</script>

<style lang="scss" scoped>
.slotstatus {
  position: absolute;
  top: -2px;
  right: -2px;
  height: 1.5rem;
  width: 1.5rem;
  z-index: 3;
  border-radius: 50%;
  background-color: var(--fs-color-light);

  &.pending {
    color: var(--fs-color-danger-500);
  }
  &.confirmed {
    color: var(--fs-color-secondary-500);
  }

  // Check / Clock inside the statuspatch
  .slotstatus-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1rem;
  }
}

.avatar.pending {
  opacity: 0.45;
}
</style>
