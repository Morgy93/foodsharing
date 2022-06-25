<template>
  <Dropdown
    title="menu.entry.notifications"
    icon="fa-bell"
    :badge="unread"
    direction="right"
  >
    <template
      v-if="bells.length > 0"
      #content
    >
      <BellsEntry
        v-for="bell in bells"
        :key="bell.id"
        :bell="bell"
        @remove="onBellDelete"
        @read="onBellRead"
      />
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('bell.no_bells')"
      />
    </template>
    <template #actions>
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        :class="{ 'disabled': !unread }"
        @click="markNewBellsAsRead()"
      >
        <i class="fas fa-check-double" />
        {{ $i18n('menu.entry.mark_as_read') }}
      </button>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import DataBell from '@/stores/bells'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import BellsEntry from './NavBellsEntry'
// Mixins
import { pulseError } from '@/script'

export default {
  components: {
    BellsEntry,
    Dropdown,
  },
  computed: {
    bells () {
      return DataBell.getters.get()
    },
    unread () {
      const unread = DataBell.getters.getUnreadCount()
      if (unread) {
        return unread < 99 ? unread : '99+'
      }
      return null
    },
  },
  methods: {
    async onBellDelete (id) {
      try {
        await DataBell.mutations.delete(id)
      } catch (err) {
        pulseError(this.$i18n('error_unexpected'))
      }
    },
    async onBellRead (bell) {
      if (!bell.isRead) {
        try {
          await DataBell.mutations.markAsRead(bell)
        } catch (err) {
          pulseError(this.$i18n('error_unexpected'))
        }
      }
    },
    markNewBellsAsRead () {
      try {
        DataBell.mutations.markNewBellsAsRead()
      } catch {
        pulseError(this.$i18n('error_unexpected'))
      }
    },
  },
}
</script>
