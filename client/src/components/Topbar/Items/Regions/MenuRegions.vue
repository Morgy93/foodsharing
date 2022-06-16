<template>
  <fs-dropdown-menu
    id="dropdown-region"
    ref="dropdown"
    title="terminology.regions"
    class="regionMenu"
    icon="fa-globe"
    :show-title="showTitle"
    scrollbar
  >
    {{ regions }}
    <template #heading-text>
      <span
        class="regionName d-none d-md-inline-block"
      >
        {{ activeRegion ? truncate(activeRegion.name, isVisibleOnMobile ? 15 : 30) : $i18n('terminology.regions') }}
      </span>
      <span
        class="hide-for-users"
        v-html="activeRegion ? activeRegion.name : $i18n('terminology.regions')"
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
          v-if="region.id !== activeRegionId || regions.length !== 1"
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
          :visible="region.id === activeRegionId"
          :accordion="$options.name"
        >
          <a
            v-for="(entry,key) in generateMenu(region)"
            :key="key"
            :href="entry.href ? $url(entry.href, region.id, entry.special) : '#'"
            role="menuitem"
            class="dropdown-item dropdown-action"
            @click="setActiveRegion(region.id), entry.func ? entry.func() : null"
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
import { getters } from '@/stores/regions'
// Components
import FsDropdownMenu from '../FsDropdownMenu'
import { becomeBezirk } from '@/script'
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import RegionUpdater from '@/mixins/RegionUpdaterMixin'
import Truncate from '@/mixins/TruncateMixin'
import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  name: 'MenuGroups',
  components: { FsDropdownMenu },
  mixins: [ConferenceOpener, RegionUpdater, Truncate, TopBarMixin],
  computed: {
    regions () {
      return getters.get()
    },
    activeRegion () {
      return this.regions.find(r => r.id === this.activeRegionId)
    },
    regionsSorted () {
      return this.regions.slice().sort((a, b) => {
        if (this.activeRegionId && a.id === this.activeRegionId) return -1
        if (this.activeRegionId && b.id === this.activeRegionId) return 1
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
