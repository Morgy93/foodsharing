<template>
  <div class="group text-truncate">
    <button
      v-if="!isAlone"
      v-b-toggle="toggleId(entry.id)"
      role="menuitem"
      class="dropdown-header dropdown-item text-truncate"
      target="_self"
      @click.stop
    >
      <i
        v-if="isHomeRegion"
        v-b-tooltip="$i18n('dashboard.homeRegion', {region: entry.name})"
        class="icon-subnav fas fa-home"
      />
      <span v-html="entry.name" />
    </button>
    <h6
      v-if="isAlone"
      role="menuitem"
      class="dropdown-header text-truncate"
      v-html="entry.name"
    />
    <b-collapse
      :id="toggleId(entry.id)"
      class="dropdown-submenu"
      accordion="region"
      :visible="isHomeRegion"
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
// Store
import DataUser from '@/stores/user'
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
    isHomeRegion () {
      return this.entry.id === DataUser.getters.getHomeRegion()
    },
    menuEntries () {
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
          href: 'options', icon: 'fa-tools', text: 'menu.entry.options',
        },
        {
          href: 'statistic', icon: 'fa-chart-bar', text: 'terminology.statistic',
        },
      ]

      if (this.entry.hasConference) {
        menu.push({
          icon: 'fa-users', text: 'menu.entry.conference', func: () => this.showConferencePopup(this.entry.id),
        })
      }

      if (this.entry.mayHandleFoodsaverRegionMenu) {
        menu.push({
          href: 'foodsaverList', icon: 'fa-user', text: 'menu.entry.fs',
        })
      }

      if (this.entry.maySetRegionPin) {
        menu.push({
          href: 'pin', icon: 'fa-users', text: 'menu.entry.pin',
        })
      }
      if (this.entry.mayAccessReportGroupReports) {
        menu.push({
          href: 'reports', icon: 'fa-poo', text: 'terminology.reports',
        })
      }
      if (this.entry.mayAccessArbitrationGroupReports) {
        menu.push({
          href: 'reports', icon: 'fa-poo', text: 'terminology.arbitration',
        })
      }

      if (this.entry.isAdmin) {
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
