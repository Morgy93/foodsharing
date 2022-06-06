<template>
  <b-navbar-nav
    id="topbar-navright"
    class="nav-row flex-md-row justify-content-end align-items-start align-items-md-center"
  >
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :show-title="true"
    />
    <MenuBullhorn />
    <MenuInformation />
  </b-navbar-nav>
</template>

<script>

import { VBTooltip, BNavbarNav } from 'bootstrap-vue'
import MenuBullhorn from '../Items/Bullhorn/MenuBullhorn'
import MenuInformation from '../Items/Information/MenuInformation'
import MenuItem from '../Items/MenuItem'

export default {
  components: { MenuBullhorn, MenuInformation, BNavbarNav, MenuItem },
  directives: { VBTooltip },
}
</script>
