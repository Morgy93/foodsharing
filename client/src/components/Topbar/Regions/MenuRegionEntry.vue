<template>
  <!-- eslint-disable -->
  <div @click.prevent>
    <div class="card">
      <div class="card-header" :id="entry.id">
        <h5 class="mb-0">
          <button class="btn btn-link" data-toggle="collapse" :data-target="'#'+entry.id" aria-expanded="true" :aria-controls="entry.id">
            {{ entry.name }}
          </button>
        </h5>
      </div>

      <div :id="entry.id" class="collapse" :aria-labelledby="entry.id" data-parent="#regions">
        <div class="card-body">
          Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
        </div>
      </div>
    </div>
    <!-- <ul class="">
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
    </ul> -->
  </div>
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
