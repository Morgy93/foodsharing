<template>
  <fs-dropdown-menu
    title="menu.entry.activities"
    icon="fa-bullhorn"
    :show-title="showTitle"
  >
    <template #content>
      <div
        v-for="(heading, idx) in headings"
        :key="idx"
        class="group"
      >
        <button
          v-b-toggle="toggleId(idx)"
          role="menuitem"
          class="dropdown-header dropdown-item text-truncate"
          target="_self"
          :class="{ 'disabled': collapsed }"
          v-html="$i18n(heading.name)"
        />
        <b-collapse
          :id="toggleId(idx)"
          :visible="collapsed ? collapsed : idx === 0"
          :accordion="collapsed ? heading.name : $options.name"
        >
          <a
            v-for="(entry, key) in heading.items"
            :key="key"
            :href="$url(entry.url)"
            :target="entry.target"
            role="menuitem"
            class="dropdown-item sub"
            v-html="$i18n(entry.title)"
          />
        </b-collapse>
      </div>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import FsDropdownMenu from '../FsDropdownMenu'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  name: 'MenuBullhorn',
  components: { FsDropdownMenu },
  mixins: [TopBarMixin],
  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      headings: [
        {
          name: 'menu.entry.fundraising',
          items: [
            { url: 'donations', title: 'menu.entry.donations' },
            { url: 'circle_of_friends', title: 'menu.entry.friendcircle' },
            { url: 'selfservice', title: 'menu.entry.selfservice' },
            { url: 'transparency', title: 'menu.entry.transparency' },
          ],
        },
        {
          name: 'menu.entry.politics',
          items: [
            { url: 'fsstaedte', title: 'menu.entry.fscities' },
            { url: 'claims', title: 'menu.entry.demands' },
            { url: 'leeretonne', title: 'menu.entry.pastcampaigns' },
          ],
        },
        {
          name: 'menu.entry.education',
          items: [
            { url: 'academy', title: 'menu.entry.academy' },
            { url: 'workshops', title: 'menu.entry.talksandworkshops' },
            { url: 'festival', title: 'menu.entry.fsfestival' },
          ],
        },
      ],
    }
  },
  methods: {
    toggleId (id) {
      return this.$options.name + '_' + id
    },
  },
}
</script>
