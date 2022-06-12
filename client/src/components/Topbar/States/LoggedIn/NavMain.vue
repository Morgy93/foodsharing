<template>
  <b-navbar-nav
    class="nav_mainbar"
    is-nav
  >
    <MenuRegions
      v-if="isFoodsaver"
      class="spacer"
      :show-title="true"
      :regions="regions"
    />
    <MenuGroups
      v-if="isFoodsaver"
      :show-title="true"
      :groups="groups"
    />
    <MenuStores
      v-if="isFoodsaver"
    />
    <MenuItem
      v-if="!isFoodsaver"
      :title="$i18n('foodsaver.upgrade.to_fs')"
      icon="fa-hands-helping"
      :show-title="viewIsMD"
      :url="$url('quizFs')"
    />
    <MenuBaskets
      :show-title="!isFoodsaver && viewIsMD"
    />
    <MenuMailbox
      v-if="user.mailbox_id"
    />
    <MenuMessages />
    <MenuBells />
  </b-navbar-nav>
</template>

<script>
import MenuItem from '@/components/Topbar/Items/MenuItem'

import MenuMailbox from '@/components/Topbar/Items/Mailbox/MenuMailbox'
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
    MenuMailbox,
    MenuBells,
    MenuGroups,
    MenuRegions,
    MenuBaskets,
    MenuStores,
  },
  mixins: [TopBarMixin],
}

</script>
