<template>
  <div class="group text-truncate">
    <button
      v-if="!isAlone"
      v-b-toggle="toggleId(entry.id)"
      role="menuitem"
      class="dropdown-header dropdown-item text-truncate"
      target="_self"
      @click.stop
      v-html="entry.name"
    />
    <h6
      v-if="isAlone"
      role="menuitem"
      class="dropdown-header text-truncate"
      v-html="entry.name"
    />
    <b-collapse
      :id="toggleId(entry.id)"
      class="dropdown-submenu"
      accordion="groups"
    >
      <a
        v-for="(menu,key) in menuEntries"
        :key="key"
        :href="menu.href ? $url(menu.href, entry.id, menu.special) : '#'"
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click="menu.func ? menu.func() : null"
      >
        <i
          class="icon-subnav fas"
          :class="menu.icon"
        />
        {{ $i18n(menu.text) }}
      </a>
    </b-collapse>
  </div>
</template>
<script>
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'

export default {
  name: 'MenuGroupsEntry',
  mixins: [ConferenceOpener],
  props: {
    isAlone: {
      type: Boolean,
      default: false,
    },
    entry: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    menuEntries () {
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

      if (this.entry.hasConference) {
        menu.push({
          icon: 'fa-users', text: 'menu.entry.conference', func: () => this.showConferencePopup(this.entry.id),
        })
      }

      if (this.entry.isAdmin) {
        menu.push({
          href: 'workingGroupEdit', icon: 'fa-cog', text: 'menu.entry.workingGroupEdit',
        })
      }

      return menu
    },
  },
  methods: {
    toggleId (id) {
      return this.$options.name + '_' + id
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../../scss/icon-sizes.scss';
</style>
