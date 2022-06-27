<template>
  <ul class="mainnav">
    <Link
      title="Foodsharing"
      :href="$url('dashboard')"
    >
      <template #text>
        <Logo small />
      </template>
    </Link>
    <Link
      v-if="!isFoodsaver"
      :title="$i18n('foodsaver.upgrade.to_fs')"
      icon="fa-hands-helping"
      :href="$url('quiz_foodsaver')"
    />
    <NavRegions v-if="isFoodsaver" />
    <NavGroups v-if="isFoodsaver && !viewIsMobile" />
    <NavStores v-if="isFoodsaver" />
    <NavBaskets />
    <NavConversations v-if="viewIsMobile" />
    <NavNotifications v-if="viewIsMobile" />
    <Link
      v-if="viewIsMobile"
      data-toggle="collapse"
      data-target="#sidebar"
      aria-controls="sidebar"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <template #text>
        <i class="fas fa-bars" />
      </template>
    </Link>
  </ul>
</template>

<script>
// Store
import DataUser from '@/stores/user'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import Logo from '@/components/Navigation/Logo'
//
import NavNotifications from '@/components/Navigation/Notifications/NavNotifications'
import NavConversations from '@/components/Navigation/Conversations/NavConversations'
import NavBaskets from '@/components/Navigation/Baskets/NavBaskets'
import NavStores from '@/components/Navigation/Stores/NavStores'
import NavGroups from '@/components/Navigation/Groups/NavGroups'
import NavRegions from '@/components/Navigation/Regions/NavRegions'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    Link,
    NavNotifications,
    NavConversations,
    NavBaskets,
    NavStores,
    NavGroups,
    NavRegions,
  },
  mixins: [MediaQueryMixin],
  computed: {
    isFoodsaver () {
      return DataUser.getters.isFoodsaver()
    },
  },
}
</script>
