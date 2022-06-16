<template>
  <b-navbar-nav
    class="nav_mainbar"
    is-nav
  >
    <MenuRegions
      v-if="isFoodsaver"
      :show-title="true"
    />
    <div
      v-if="isFoodsaver"
      class="spacer"
    />
    <MenuGroups
      v-if="isFoodsaver"
      :show-title="true"
    />
    <MenuStores
      v-if="isFoodsaver"
      :user="user"
    />
    <MenuItem
      v-if="!isFoodsaver"
      :title="$i18n('foodsaver.upgrade.to_fs')"
      icon="fa-hands-helping"
      :show-title="viewIsMD"
      :url="$url('quizFs')"
    />
    <div
      v-if="!isFoodsaver"
      class="spacer"
    />
    <MenuBaskets
      :show-title="!isFoodsaver && viewIsLG"
    />
    <MenuMessages
      :show-title="!isFoodsaver && viewIsLG"
    />
    <MenuBells
      :show-title="!isFoodsaver && viewIsLG"
    />
  </b-navbar-nav>
</template>

<script>
import MenuItem from '@/components/Topbar/Items/MenuItem'

import MenuMessages from '@/components/Topbar/Items/Messages/MenuMessages'
import MenuBells from '@/components/Topbar/Items/Bells/MenuBells'
import MenuGroups from '@/components/Topbar/Items/Groups/MenuGroups'
import MenuRegions from '@/components/Topbar/Items/Regions/MenuRegions'
import MenuBaskets from '@/components/Topbar/Items/Baskets/MenuBaskets'
import MenuStores from '@/components/Topbar/Items/Stores/MenuStores'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: {
    MenuItem,
    MenuMessages,
    MenuBells,
    MenuGroups,
    MenuRegions,
    MenuBaskets,
    MenuStores,
  },
  mixins: [TopBarMixin],
}

</script>
