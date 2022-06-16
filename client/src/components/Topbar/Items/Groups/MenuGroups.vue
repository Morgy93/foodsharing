<template>
  <fs-dropdown-menu
    v-if="groups.length > 0"
    id="dropdown-groups"
    title="menu.entry.your_groups"
    icon="fa-users"
    scrollbar
  >
    <template #content>
      <div
        v-for="(group, idx) in groups"
        :key="group.id"
        class="group text-truncate"
      >
        <button
          v-if="!alwaysOpen"
          v-b-toggle="toggleId(group.id)"
          role="menuitem"
          class="dropdown-header dropdown-item text-truncate"
          target="_self"
          v-html="group.name"
        />
        <h6
          v-if="alwaysOpen"
          role="menuitem"
          class="dropdown-header text-truncate"
          v-html="group.name"
        />
        <b-collapse
          :id="toggleId(group.id)"
          class="dropdown-submenu"
          :visible="idx === 0"
          :accordion="'groups'"
        >
          <a
            v-for="(entry,key) in generateMenu(group)"
            :key="key"
            :href="entry.href ? $url(entry.href, group.id, entry.special) : '#'"
            role="menuitem"
            class="dropdown-item dropdown-action"
            @click="entry.func ? entry.func() : null"
          >
            <i
              class="fas"
              :class="entry.icon"
            />
            {{ $i18n(entry.text) }}
          </a>
        </b-collapse>
      </div>
    </template>
    <template #actions>
      <a
        :href="$url('workingGroups')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-users" />
        {{ $i18n('menu.entry.groups') }}
      </a>
    </template>
  </fs-dropdown-menu>
  <menu-item
    v-else
    :url="$url('workingGroups')"
    :title="$i18n('menu.entry.groups')"
    :show-title="false"
    icon="fa-users"
  />
</template>
<script>
// Store
import { getters } from '@/stores/groups'

// Components
import FsDropdownMenu from '../FsDropdownMenu'
import MenuItem from '../MenuItem'

// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  name: 'MenuGroups',
  components: { FsDropdownMenu, MenuItem },
  mixins: [ConferenceOpener, MediaQueryMixin, TopBarMixin],
  computed: {
    groups () {
      return getters.get()
    },
    alwaysOpen () {
      return this.groups.length <= 2
    },
  },
  methods: {
    generateMenu (group) {
      const menu = [
        {
          href: 'wall', icon: 'fa-bullhorn', text: 'menu.entry.wall',
        },
        {
          href: 'forum', icon: 'fa-comment-alt', text: 'menu.entry.forum',
        },
        {
          href: 'events', icon: 'fa-calendar-alt', text: 'menu.entry.events',
        },
        {
          href: 'polls', icon: 'fa-poll-h', text: 'terminology.polls',
        },
        {
          href: 'members', icon: 'fa-user', text: 'menu.entry.members',
        },

      ]

      if (group.hasConference) {
        menu.push({
          icon: 'fa-users', text: 'menu.entry.conference', func: () => this.showConferencePopup(group.id),
        })
      }

      if (group.isAdmin) {
        menu.push({
          href: 'workingGroupEdit', icon: 'fa-cog', text: 'menu.entry.workingGroupEdit',
        })
      }

      return menu
    },
    toggleId (id) {
      return this.$options.name + '_' + id
    },
  },
}
</script>
