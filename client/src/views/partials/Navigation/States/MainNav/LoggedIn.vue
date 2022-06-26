<template>
  <ul class="mainnav">
    <Link :href="$url('dashboard')">
      <template #text>
        <Logo small />
      </template>
    </Link>
    <NavRegions v-if="!viewIsMobile" />
    <NavGroups v-if="!viewIsMobile" />
    <NavStores />
    <NavBaskets />
    <NavConversations v-if="viewIsMobile" />
    <NavBells v-if="viewIsMobile" />
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
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import Logo from '@/components/Navigation/Logo'
//
import NavBells from '@/components/Navigation/Bells/NavBells'
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
    NavBells,
    NavConversations,
    NavBaskets,
    NavStores,
    NavGroups,
    NavRegions,
  },
  mixins: [MediaQueryMixin],
}
</script>
