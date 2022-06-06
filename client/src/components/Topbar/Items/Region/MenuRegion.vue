<template>
  <fs-dropdown-menu
    id="dropdown-region"
    ref="dropdown"
    menu-title="terminology.regions"
    class="regionMenu"
    icon="fa-globe"
  >
    <template #heading-text>
      <span
        class="regionName d-none d-sm-inline-block"
        style="font-family: 'Alfa Slab One',serif;"
      >
        {{ activeRegion ? truncate(activeRegion.name, !viewIsSM ? 15 : 30) : $i18n('terminology.regions') }}
      </span>
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
        <a
          v-if="region.id !== activeRegionId || regions.length !== 1"
          v-b-toggle="`topbarregion_${region.id}`"
          role="menuitem"
          href="#"
          target="_self"
          class="dropdown-item dropdown-header"
        >
          <span
            v-html="truncate(region.name)"
          />
        </a>
        <b-collapse
          :id="`topbarregion_${region.id}`"
          :visible="region.id === activeRegionId"
          accordion="regions"
        >
          <a
            v-for="(entry,key) in generateMenu(region)"
            :key="key"
            :href="entry.href ? $url(entry.href, region.id, entry.special) : '#'"
            role="menuitem"
            class="dropdown-item sub"
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
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('region.none')"
      />
    </template>
    <template #actions>
      <a
        href="#"
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click="joinRegionDialog"
      >
        <small>
          <i class="fas fa-plus" />
          {{ $i18n('menu.entry.joinregion') }}
        </small>
      </a>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import { BCollapse, VBToggle } from 'bootstrap-vue'
import FsDropdownMenu from '../FsDropdownMenu'
import { becomeBezirk } from '@/script'
import ConferenceOpener from '@/utils/ConferenceOpener'
import RegionUpdater from '@/utils/RegionUpdater'
import Truncate from '@/utils/Truncate'
import MediaQueryMixin from '@/utils/MediaQueryMixin'

export default {
  components: { BCollapse, FsDropdownMenu },
  directives: { VBToggle },
  mixins: [ConferenceOpener, RegionUpdater, Truncate, MediaQueryMixin],
  props: {
    regions: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
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
          href: 'polls', icon: 'fa-poll-h', text: 'terminology.polls',
        },
        {
          href: 'statistic', icon: 'fa-chart-bar', text: 'terminology.statistic',
        },
      ]

      if (region.conference) {
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
