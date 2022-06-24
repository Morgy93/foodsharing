<template>
  <fs-dropdown-menu
    id="dropdown-groups"
    title="menu.entry.your_groups"
    icon="fa-users"
    scrollbar
  >
    <template
      v-if="groups.length > 0"
      #content
    >
      <div
        v-for="group in groups"
        :key="group.id"
        class="group d-flex flex-column align-items-baseline"
      >
        <button
          v-if="groups.length !== 1"
          v-b-toggle="toggleId(group.id)"
          role="menuitem"
          target="_self"
          class="dropdown-item dropdown-header text-truncate"
        >
          <span
            class="text-truncate"
            v-html="group.name"
          />
        </button>
        <b-collapse
          :id="toggleId(group.id)"
          class="dropdown-submenu"
          :accordion="$options.name"
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
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('groups.empty')"
      />
    </template>
    <template #actions>
      <a
        :href="$url('workingGroups')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-users" />
        {{ $i18n('menu.entry.groups') }}
      </a>
    </template>
  </fs-dropdown-menu>
</template>
<script>
// Store
import { getters } from '@/stores/groups'

// Components
import FsDropdownMenu from '../FsDropdownMenu'

// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import TopBarMixin from '@/mixins/TopBarMixin'
import TruncateMixin from '@/mixins/TruncateMixin'

export default {
  name: 'MenuGroups',
  components: { FsDropdownMenu },
  mixins: [ConferenceOpener, MediaQueryMixin, TopBarMixin, TruncateMixin],
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
