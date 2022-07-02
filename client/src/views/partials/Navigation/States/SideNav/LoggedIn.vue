<template>
  <div class="navbar-navside">
    <MetaNavLoggedIn v-if="viewIsMobile" />
    <ul class="sidenav">
      <Link
        v-if="isFoodsaver"
        icon="fa-search"
        title="Suche"
        modal="searchBarModal"
      />
      <Link
        v-if="viewIsXXS"
        :href="$url('workingGroups')"
        icon="fa-users"
        :title="$i18n('menu.entry.groups')"
      />
      <NavConversations v-if="!viewIsMobile" />
      <NavNotifications v-if="!viewIsMobile" />
      <NavUser />
    </ul>
  </div>
</template>

<script>
// Store
import DataUser from '@/stores/user'
import MetaNavData from '../../Data/MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import NavConversations from '@/components/Navigation/Conversations/NavConversations'
import NavNotifications from '@/components/Navigation/Notifications/NavNotifications'
import NavUser from '@/components/Navigation/User/NavUser'
// State
import MetaNavLoggedIn from '../MetaNav/LoggedIn.vue'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Link,
    NavConversations,
    NavNotifications,
    NavUser,
    MetaNavLoggedIn,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      metaNav: MetaNavData,
    }
  },
  computed: {
    isFoodsaver () {
      return DataUser.getters.isFoodsaver()
    },
  },
}
</script>
