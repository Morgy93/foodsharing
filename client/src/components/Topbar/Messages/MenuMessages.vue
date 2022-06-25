<template>
  <fs-dropdown-menu
    id="dropdown-messages"
    title="menu.entry.messages"
    icon="fa-comments"
    class="topbar-messages"
    :badge="unread"
    :show-title="showTitle"
    scrollbar
  >
    <template
      v-if="conversations.length > 0"
      #content
    >
      <menu-messages-entry
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
  </fs-dropdown-menu>
</template>
<script>
import MenuMessagesEntry from './MenuMessagesEntry'
import conversationStore from '@/stores/conversations'
import FsDropdownMenu from '../FsDropdownMenu'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuMessagesEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  computed: {
    conversations () {
      /* let res = Array.from(conversationStore.conversations) // .filter(c => c.lastMessage || c.messages)
      return res */
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
