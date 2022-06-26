<template>
  <ul class="mainnav">
    <Link
      :href="homeHref"
    >
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
// Store
import DataUser from '@/stores/user'
import DataBells from '@/stores/bells'
import DataStores from '@/stores/stores'
import DataBaskets from '@/stores/baskets'
import DataGroups from '@/stores/groups.js'
import DataRegions from '@/stores/regions.js'
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
// Hidden Elements
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import ScrollMixin from '@/mixins/ScrollMixin'

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
  mixins: [MediaQueryMixin, ScrollMixin],
  props: {
    regions: {
      type: Array,
      default: () => [],
    },
    groups: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      navIsSmall: false,
      metaNav: [
        {
          title: 'Karte',
          url: 'map',
        },
        {
          title: 'IT\'ler? Wir brauchen dich!',
          url: 'devdocsItTasks',
        },
        {
          title: 'Kontakt',
          url: 'contact',
        },
        {
          title: 'Hilfe',
          url: 'wiki',
        },
      ],
    }
  },
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
    isFoodsaver () {
      return DataUser.getters.isFoodsaver()
    },
    hasMailbox () {
      return DataUser.getters.hasMailBox()
    },
    homeHref () {
      return (this.isLoggedIn) ? this.$url('dashboard') : this.$url('home')
    },
  },
  watch: {
    // scrollPosition: {
    //   handler (newPos) {
    //     this.navIsSmall = newPos.y > 550
    //   },
    //   deep: true,
    // },
    hasMailbox: {
      async handler (newValue) {
        if (newValue) {
          await DataUser.mutations.fetchMailUnreadCount()
        }
      },
      deep: true,
    },
    isFoodsaver: {
      async handler (newValue) {
        if (newValue) {
          await DataStores.mutations.fetch()
        }
      },
      deep: true,
    },
  },
  async created () {
    // TODO: NO APIS :(
    DataGroups.mutations.set(this.groups)
    DataRegions.mutations.set(this.regions)

    // Load data
    if (this.isLoggedIn) {
      await DataUser.mutations.fetchDetails()
      await DataBaskets.mutations.fetchOwn()
      await DataBells.mutations.fetch()
    }
  },
  methods: {
    hide (e) {
      console.log(e)
    },
  },
}
</script>
