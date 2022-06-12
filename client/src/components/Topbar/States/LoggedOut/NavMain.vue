<template>
  <b-navbar-nav class="nav_mainbar">
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :show-title="true"
    />
    <MenuBullhorn
      :collapsed="false"
      :show-title="true"
    />
    <MenuInformation
      :collapsed="false"
      :show-title="true"
    />
  </b-navbar-nav>
</template>

<script>

import MenuBullhorn from '../../Items/Bullhorn/MenuBullhorn'
import MenuInformation from '../../Items/Information/MenuInformation'
import MenuItem from '../../Items/MenuItem'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuBullhorn, MenuInformation, MenuItem },
  mixins: [TopBarMixin],
}
</script>
