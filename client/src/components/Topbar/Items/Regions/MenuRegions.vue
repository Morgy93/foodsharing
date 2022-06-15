<template>
  <fs-dropdown-menu
    id="dropdown-region"
    ref="dropdown"
    :title="regionsSorted.length > 1 ? 'dashboard.my.regions' : 'dashboard.my.region'"
    class="regionMenu"
    icon="fa-globe"
    :show-title="false"
    scrollbar
  >
    <template #heading-text>
      <span
        class="regionName d-none d-md-inline-block"
      >
        {{ getHomeRegion ? truncate(getHomeRegion.name, isVisibleOnMobile ? 15 : 30) : $i18n('terminology.regions') }}
      </span>
      <span
        class="hide-for-users"
        v-html="getHomeRegion ? getHomeRegion.name : $i18n('terminology.regions')"
      />
    </template>
    <template
      v-if="regionsSorted.length > 0"
      #content
    >
      <div
        v-for="region in regionsSorted"
        :key="region.id"
        class="group d-flex flex-column align-items-baseline"
      >
        <button
          v-if="region.id !== getHomeRegion.id || regions.length !== 1"
          v-b-toggle="toggleId(region.id)"
          role="menuitem"
          target="_self"
          class="dropdown-item dropdown-header"
        >
          <span
            v-html="truncate(region.name)"
          />
        </button>
        <b-collapse
          :id="toggleId(region.id)"
          class="dropdown-submenu"
          :visible="region.id === getHomeRegion.id"
          :accordion="$options.name"
        >
          <a
            v-for="(entry,key) in generateMenu(region)"
            :key="key"
            :href="entry.href ? $url(entry.href, region.id, entry.special) : '#'"
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
      {{ regions }}
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('region.none')"
      />
    </template>
    <template #actions>
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click="joinRegionDialog"
      >
        <i class="fas fa-plus" />
        {{ $i18n('menu.entry.joinregion') }}
      </button>
    </template>
  </fs-dropdown-menu>
</template>
<script>
// Store
import DataRegion from '@/stores/regions'
// Components
import FsDropdownMenu from '../FsDropdownMenu'
import { becomeBezirk } from '@/script'
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import Truncate from '@/mixins/TruncateMixin'
import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  name: 'MenuRegions',
  components: { FsDropdownMenu },
  mixins: [ConferenceOpener, Truncate, TopBarMixin],
  computed: {
    regions () {
      return DataRegion.getters.get()
    },
    getHomeRegion () {
      return DataRegion.getters.getHomeRegion()
    },
    regionsSorted () {
      return this.regions.slice().sort((a, b) => {
        if (this.getHomeRegion.id && a.id === this.getHomeRegion.id) return -1
        if (this.getHomeRegion.id && b.id === this.getHomeRegion.id) return 1
        else return a.name.localeCompare(b.name)
      })
    },
  },
  methods: {
    joinRegionDialog () {
      this.$refs.dropdown.visible = false
      becomeBezirk()
    },
    generateMenu (region) {
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
    toggleId (id) {
      return this.$options.name + '_' + id
    },
  },
}
</script>

<style lang="scss" scoped>
.regionName {
  line-height: 0;
  margin-left: .5rem;
  font-family: 'Alfa Slab One',serif;
}
</style>
