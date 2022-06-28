<template>
  <Dropdown
    :title="$i18n('navigation.conversations')"
    icon="fa-comments"
    :badge="unread"
    direction="right"
    is-fixed-size
    is-scrollable
  >
    <template
      v-if="conversations.length > 0"
      #content
    >
      <ConversationsEntry
        v-for="conversation in conversations"
        :key="conversation.id"
        :conversation="conversation"
      />
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('chat.empty')"
      />
    </template>
    <template #actions="{ hide }">
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        :class="{ 'disabled': !unread }"
        @click="markUnreadMessagesAsRead(); hide();"
      >
        <i class="icon-subnav fas fa-check-double" />
        {{ $i18n('menu.entry.mark_as_read') }}
      </button>
      <a
        :href="$url('conversations')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-comments" />
        {{ $i18n('menu.entry.all_messages') }}
      </a>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import DataConversations from '@/stores/conversations'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import ConversationsEntry from './NavConversationsEntry'
// Mixins

export default {
  components: { ConversationsEntry, Dropdown },
  computed: {
    conversations () {
      return DataConversations.getters.getSorted()
    },
    unread () {
      return DataConversations.getters.getUnreadCount()
    },
  },
  methods: {
    markUnreadMessagesAsRead () {
      DataConversations.mutations.markUnreadMessagesAsRead()
    },
  },
}
</script>
