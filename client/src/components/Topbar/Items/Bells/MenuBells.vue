<template>
  <fs-dropdown-menu
    id="dropdown-bells"
    menu-title="menu.entry.notifications"
    icon="fa-bell"
    :badge="unread"
    class="topbar-bells"
    :show-title="showTitle"
    right
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
        @bell-read="onBellRead"
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
      <a
        href="#"
        role="menuitem"
        class="dropdown-item dropdown-action"
        :class="{ 'disabled': !unread }"
        @click="markNewBellsAsRead(); hide();"
      >
        <small>
          <i class="fas fa-check-double" />
          {{ $i18n('menu.entry.mark_as_read') }}
        </small>
      </a>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import FsDropdownMenu from '../FsDropdownMenu'
import MenuBellsEntry from './MenuBellsEntry'
import bellStore from '@/stores/bells'
import i18n from '@/i18n'
import { pulseError } from '@/script'
import dateFnsParseISO from 'date-fns/parseISO'

export default {
  components: { MenuBellsEntry, FsDropdownMenu },
  props: {
    showTitle: { type: Boolean, default: false },
  },
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
        pulseError(i18n('error_unexpected'))
      }
    },
    async onBellRead (bell) {
      if (!bell.isRead) {
        try {
          await bellStore.markAsRead(bell)
        } catch (err) {
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    markNewBellsAsRead () {
      try {
        bellStore.markNewBellsAsRead()
      } catch {
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>
