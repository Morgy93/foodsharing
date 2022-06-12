<template>
  <b-navbar-nav>
    <div class="spacer" />
    <SearchBar
      v-if="isFoodsaver"
    />
    <div class="spacer" />
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :show-title="viewIsXXL"
    />
    <MenuBullhorn
      :collapsed="!viewIsMD"
      :show-title="viewIsXXL"
    />
    <MenuInformation
      :collapsed="!viewIsMD"
      :show-title="viewIsXXL"
    />
    <MenuUser
      :user="user"
      :show-title="!viewIsLG"
    />
  </b-navbar-nav>
</template>

<script>
import MenuItem from '@/components/Topbar/Items/MenuItem'
import MenuUser from '@/components/Topbar/Items/User/MenuUser'
import MenuBullhorn from '@/components/Topbar/Items/Bullhorn/MenuBullhorn'
import MenuInformation from '@/components/Topbar/Items/Information/MenuInformation'
import SearchBar from '@/components/Topbar/Items/Search/SearchBar'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: {
    MenuItem,
    MenuUser,
    MenuBullhorn,
    MenuInformation,
    SearchBar,
  },
  mixins: [TopBarMixin],
}

</script>
