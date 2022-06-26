<template>
  <ul class="">
    <li
      v-for="(item, idx) of menu(entry)"
      :key="idx"
    >
      <a
        :href="item.href ? $url(item.href, entry.id, item.special) : '#'"
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click="item.func ? item.func() : null"
      >
        <i
          class="fas"
          :class="item.icon"
        />
        {{ $i18n(item.text) }}
      </a>
    </li>
  </ul>
</template>
<script>
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'

export default {
  name: 'MenuRegion',
  mixins: [ConferenceOpener],
  props: {
    entry: {
      type: Object,
      default: () => ({}),
    },
  },
  methods: {
    menu (region) {
      const menu = [
        {
          href: 'forum', icon: 'fa-comments', text: 'menu.entry.forum',
        },
        {
          href: 'stores', icon: 'fa-cart-plus', text: 'menu.entry.stores',
        },
        {
          href: 'workingGroups', icon: 'fa-users', text: 'terminology.groups',
        },
        {
          href: 'events', icon: 'fa-calendar-alt', text: 'menu.entry.events',
        },
        {
          href: 'foodsharepoints', icon: 'fa-recycle', text: 'terminology.fsp',
        },
        {
          href: 'polls', icon: 'fa-poll-h', text: 'terminology.polls',
        },
        {
          href: 'members', icon: 'fa-user', text: 'menu.entry.members',
        },
        {
          href: 'statistic', icon: 'fa-chart-bar', text: 'terminology.statistic',
        },
      ]

      if (region.hasConference) {
        menu.push({
          icon: 'fa-users', text: 'menu.entry.conference', func: () => this.showConferencePopup(region.id),
        })
      }

      if (region.mayHandleFoodsaverRegionMenu) {
        menu.push({
          href: 'foodsaverList', icon: 'fa-user', text: 'menu.entry.fs',
        })
      }

      if (region.maySetRegionOptions) {
        menu.push({
          href: 'options', icon: 'fa-tools', text: 'menu.entry.options',
        })
      }
      if (region.maySetRegionPin) {
        menu.push({
          href: 'pin', icon: 'fa-users', text: 'menu.entry.pin',
        })
      }
      if (region.mayAccessReportGroupReports) {
        menu.push({
          href: 'reports', icon: 'fa-poo', text: 'terminology.reports',
        })
      }
      if (region.mayAccessArbitrationGroupReports) {
        menu.push({
          href: 'reports', icon: 'fa-poo', text: 'terminology.arbitration',
        })
      }

      if (region.isAdmin) {
        menu.push({
          href: 'forum', special: 1, icon: 'fa-comment-dots', text: 'menu.entry.BOTforum',
        })
        menu.push({
          href: 'passports', icon: 'fa-address-card', text: 'menu.entry.ids',
        })
      }

      return menu
    },
  },
}
</script>
