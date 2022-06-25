<template>
  <fs-dropdown-menu
    title="menu.entry.infos"
    icon="fa-info"
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
  name: 'MenuInformation',
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
          name: 'menu.entry.aboutUs',
          items: [
            { url: 'mission', title: 'menu.entry.mission' },
            { url: 'grundsaetze', title: 'menu.entry.fundamentals' },
            { url: 'blog', title: 'menu.entry.blog' },
            { url: 'team', title: 'menu.entry.team' },
            { url: 'partner', title: 'menu.entry.partners' },
          ],
        },
        {
          name: 'menu.entry.background',
          items: [
            { url: 'support', title: 'menu.entry.support', target: '_blank' },
            { url: 'wiki', title: 'menu.entry.wiki' },
            { url: 'guide', title: 'menu.entry.guide', target: '_blank' },
            { url: 'statistics', title: 'menu.entry.statistics' },
            { url: 'dataprivacy', title: 'menu.entry.dataprivacy' },
            { url: 'releaseNotes', title: 'menu.entry.release-notes' },
          ],
        },
        {
          name: 'menu.entry.regionalgroups',
          items: [
            { url: 'communitiesGermany', title: 'menu.entry.Germany' },
            { url: 'communitiesAustria', title: 'menu.entry.Austria' },
            { url: 'communitiesSwitzerland', title: 'menu.entry.Swiss' },
            { url: 'international', title: 'menu.entry.international' },
          ],
        },
        {
          name: 'menu.entry.contact',
          items: [
            { url: 'contact', title: 'menu.entry.contact' },
            { url: 'press', title: 'menu.entry.press' },
            { url: 'infosCompany', title: 'menu.entry.forcompanies' },
            { url: 'imprint', title: 'menu.entry.imprint' },
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
