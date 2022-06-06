<template>
  <b-navbar-nav
    id="topbar-navright"
    class="nav-row flex-md-row align-items-start align-items-md-center"
  >
    <menu-item
      v-if="viewIsSM"
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
    />
    <menu-mailbox v-if="!viewIsSM && displayMailbox" />
    <menu-messages v-if="!viewIsSM" />
    <menu-bells v-if="!viewIsSM" />
    <MenuBullhorn />
    <MenuInformation />
    <menu-user
      :may="may"
      :user-id="userId"
      :avatar="avatar"
      :show-title="true"
    />
  </b-navbar-nav>
</template>

<script>

import { VBTooltip, BNavbarNav } from 'bootstrap-vue'
import MenuBullhorn from '../Items/Bullhorn/MenuBullhorn'
import MenuInformation from '../Items/Information/MenuInformation'
import MenuItem from '../Items/MenuItem'
import MenuMessages from '../Items/Messages/MenuMessages'
import MenuMailbox from '../Items/Mailbox/MenuMailbox'
import MenuBells from '../Items/Bells/MenuBells'
import MenuUser from '../Items/User/MenuUser'
import MediaQueryMixin from '../../../utils/MediaQueryMixin'

export default {
  components: { MenuBullhorn, MenuInformation, MenuMailbox, BNavbarNav, MenuItem, MenuMessages, MenuBells, MenuUser },
  directives: { VBTooltip },
  mixins: [MediaQueryMixin],
  props: {
    hasFsRole: {
      type: Boolean,
      default: true,
    },
    regions: {
      type: Array,
      default: () => [],
    },
    workingGroups: {
      type: Array,
      default: () => [],
    },
    displayMailbox: {
      type: Boolean,
      default: false,
    },
    userId: {
      type: Number,
      default: null,
    },
    avatar: {
      type: String,
      default: '',
    },
    may: {
      type: Object,
      default: () => ({}),
    },
  },
}
</script>
