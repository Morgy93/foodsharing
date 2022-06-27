<template>
  <div class="navbar-navside">
    <ul
      v-if="viewIsMobile"
      class="metanav"
    >
      <Link
        v-for="(link, idx) of metaNav"
        :key="idx"
        :title="$i18n(link.title)"
        :href="$url(link.url)"
      />
      <NavAdmin />
    </ul>
    <ul class="sidenav">
      <Link
        icon="fa-search"
        title="Suche"
        data-toggle="modal"
        data-target="#searchBarModal"
      />
      <Link
        v-if="viewIsMobile"
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
import MetaNavData from '../../Data/MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import NavConversations from '@/components/Navigation/Conversations/NavConversations'
import NavNotifications from '@/components/Navigation/Notifications/NavNotifications'
import NavUser from '@/components/Navigation/User/NavUser'
import NavAdmin from '@/components/Navigation/Admin/NavAdmin'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Link,
    NavConversations,
    NavNotifications,
    NavUser,
    NavAdmin,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      metaNav: MetaNavData,
    }
  },
}
</script>
