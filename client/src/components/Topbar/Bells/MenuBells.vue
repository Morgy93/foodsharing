<template>
  <fs-dropdown-menu
    id="dropdown-bells"
    class="topbar-bells"
    title="menu.entry.notifications"
    icon="fa-bell"
    :badge="unread"
    :show-title="showTitle"
    scrollbar
  >
    <template
      v-if="bells.length > 0"
      #content
    >
      <menu-bells-entry
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
    <template #actions="{ hide }">
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        :class="{ 'disabled': !unread }"
        @click="markNewBellsAsRead(); hide();"
      >
        <i class="fas fa-check-double" />
        {{ $i18n('menu.entry.mark_as_read') }}
      </button>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import FsDropdownMenu from '../FsDropdownMenu'
import MenuBellsEntry from './MenuBellsEntry'

import bellStore from '@/stores/bells'
import { pulseError } from '@/script'
import dateFnsParseISO from 'date-fns/parseISO'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuBellsEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  computed: {
    bells () {
      return bellStore.bells.map(bell => {
        const newBell = Object.assign({}, bell)
        newBell.createdAt = dateFnsParseISO(bell.createdAt)
        return newBell
      })
    },
    unread () {
      if (bellStore.unreadCount) {
        return bellStore.unreadCount < 99 ? bellStore.unreadCount : '99+'
      }
      return null
    },
  },
  created () {
    bellStore.loadBells()
  },
  methods: {
    async onBellDelete (id) {
      try {
        await bellStore.delete(id)
      } catch (err) {
        pulseError(this.$i18n('error_unexpected'))
      }
    },
    async onBellRead (bell) {
      if (!bell.isRead) {
        try {
          await bellStore.markAsRead(bell)
        } catch (err) {
          pulseError(this.$i18n('error_unexpected'))
        }
      }
    },
    markNewBellsAsRead () {
      try {
        bellStore.markNewBellsAsRead()
      } catch {
        pulseError(this.$i18n('error_unexpected'))
      }
    },
  },
}
</script>
