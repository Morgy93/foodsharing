<template>
  <ul class="navbar-navside">
    <ul
      v-if="viewIsMobile"
      class="metanav"
    >
      <Link
        v-for="(link, idx) of metaNav"
        :key="idx"
        :title="link.title"
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
        icon="fa-globe"
        title="Bezirke"
      />
      <Link
        v-if="viewIsMobile"
        :href="$url('workingGroups')"
        icon="fa-users"
        :title="$i18n('menu.entry.groups')"
      />
      <NavConversations v-if="!viewIsMobile" />
      <NavBells v-if="!viewIsMobile" />
      <NavUser />
    </ul>
  </ul>
</template>

<script>
// Store
import MetaNavData from '../../MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import NavConversations from '@/components/Navigation/Conversations/NavConversations'
import NavBells from '@/components/Navigation/Bells/NavBells'
import NavUser from '@/components/Navigation/User/NavUser'
import NavAdmin from '@/components/Navigation/Admin/NavAdmin'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Link,
    NavConversations,
    NavBells,
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
