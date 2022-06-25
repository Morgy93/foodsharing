<template>
  <Dropdown
    title="menu.entry.messages"
    icon="fa-comments"
    :badge="unread"
    direction="right"
    scrollable
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
        <i class="fas fa-check-double" />
        {{ $i18n('menu.entry.mark_as_read') }}
      </button>
      <a
        :href="$url('conversations')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-comments" />
        {{ $i18n('menu.entry.all_messages') }}
      </a>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import conversationStore from '@/stores/conversations'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import ConversationsEntry from './NavConversationsEntry'
// Mixins

export default {
  components: { ConversationsEntry, Dropdown },
  computed: {
    conversations () {
      return Object.values(conversationStore.conversations).filter((a) => (a.lastMessage != null)).sort(
        (a, b) => (a.hasUnreadMessages === b.hasUnreadMessages) ? ((a.lastMessage.sentAt < b.lastMessage.sentAt) ? 1 : -1) : (a.hasUnreadMessages ? -1 : 1),
      )
    },
    unread () {
      if (conversationStore.unreadCount) {
        return conversationStore.unreadCount < 99 ? conversationStore.unreadCount : '99+'
      }
      return null
    },
  },
  created () {
    return conversationStore.loadConversations()
  },
  methods: {
    markUnreadMessagesAsRead () {
      conversationStore.markUnreadMessagesAsRead()
    },
  },
}
</script>
