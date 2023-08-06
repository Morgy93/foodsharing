<template>
  <div>
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
  </div>
</template>

<script>
export default {
  name: 'NavGroupsLinkEntry',
  props: {
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

      if (this.entry.hasSubgroups) {
        menu.push({
          href: 'subGroups', icon: 'fa-user-friends', text: 'terminology.subgroups',
        })
      }

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

      if (this.entry.isChainGroup) {
        menu.push({
          href: 'chains', icon: 'fa-link', text: 'menu.entry.chainList',
        })
      }

      return menu
    },
  },
}
</script>
