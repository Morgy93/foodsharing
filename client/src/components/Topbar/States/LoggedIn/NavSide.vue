<template>
  <b-navbar-nav>
    <menu-item
      :url="$url('map')"
      icon="fa-map-marker-alt"
      :title="$i18n('storelist.map')"
      :show-title="!isFoodsaver"
      class="mobile-second"
    />
    <div
      v-if="isFoodsaver"
      class="spacer"
    />
    <SearchBar
      v-if="isFoodsaver"
      class="mobile-frist"
    />
    <div
      v-if="isFoodsaver"
      class="spacer"
    />
    <MenuUser
      v-if="viewIsLG"
      :show-title="!viewIsLG"
    />
    <MenuSideUser
      v-if="!viewIsLG"
      class="mobile-third"
    />
  </b-navbar-nav>
</template>

<script>
import MenuItem from '@/components/Topbar/Items/MenuItem'
import MenuUser from '@/components/Topbar/Items/User/MenuUser'
import MenuSideUser from '@/components/Topbar/Items/User/MenuSideUser'
import SearchBar from '@/components/Topbar/Items/Search/SearchBar'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: {
    MenuItem,
    MenuUser,
    MenuSideUser,
    SearchBar,
  },
  mixins: [TopBarMixin],
}
</script>

<style lang="scss" scoped>
.mobile-first {
  @media (max-width: 991px) {
    order: 1;
  }
}
.mobile-second {
  @media (max-width: 991px) {
    order: 2;
  }
}

.mobile-third {
  @media (max-width: 991px) {
    order: 3;
  }
}
</style>
