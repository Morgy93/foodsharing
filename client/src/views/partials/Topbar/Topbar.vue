<template>
  <!-- eslint-disable -->
  <div class="bootstrap">
  <nav class="sticky nav navbar navbar-expand-md">
    <ul class="metanav-container container">
      <ul
        v-if="!viewIsMobile"
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
    </ul>
    <ul class="container nav-container">

      <ul class="mainnav">
        <Link
          :href="homeHref"
        >
          <template #text>
            <Logo small/>
          </template>
        </Link>
        <Dropdown v-if="!viewIsMobile" icon="fa-globe" title="Regions"/>
        <NavGroups v-if="!viewIsMobile" />
        <NavStores />
        <NavBaskets />

        <NavConversations v-if="viewIsMobile" />
        <NavBells v-if="viewIsMobile" />

        <Link
          v-if="viewIsMobile"
          data-toggle="collapse"
          data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <template #text>
            <i class="fas fa-bars" />
          </template>
        </Link>
      </ul>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
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
            <Link icon="fa-search" title="Suche" data-toggle="modal" data-target="#searchBarModal"/>
            <Link v-if="viewIsMobile" icon="fa-globe" title="Bezirke"/>
            <Link v-if="viewIsMobile" :href="$url('workingGroups')" icon="fa-users" :title="$i18n('menu.entry.groups')"/>
            <NavConversations v-if="!viewIsMobile"/>
            <NavBells v-if="!viewIsMobile"/>
            <NavUser />
          </ul>
        </ul>
      </div>
    </ul>
  </nav>
  <LanguageChooser />
  <SearchBarModal />
  </div>
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
import Dropdown from '@/components/Topbar/_NavItems/NavDropdown'
import Link from '@/components/Topbar/_NavItems/NavLink'
import Logo from '@/components/Topbar/Logo'
//
import NavAdmin from '@/components/Topbar/Admin/NavAdmin'
import NavUser from '@/components/Topbar/User/NavUser'
import NavBells from '@/components/Topbar/Bells/NavBells'
import NavConversations from '@/components/Topbar/Conversations/NavConversations'
import NavBaskets from '@/components/Topbar/Baskets/NavBaskets'
import NavStores from '@/components/Topbar/Stores/NavStores'
import NavGroups from '@/components/Topbar/Groups/NavGroups'
// Hidden Elements
import LanguageChooser from '@/components/Topbar/LanguageChooser'
import SearchBarModal from '@/components/SearchBar/SearchBarModal'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import ScrollMixin from '@/mixins/ScrollMixin'

export default {
  components: {
    Logo,
    LanguageChooser,
    SearchBarModal,
    Dropdown,
    Link,
    NavAdmin,
    NavUser,
    NavBells,
    NavConversations,
    NavBaskets,
    NavStores,
    NavGroups,
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
<style lang="scss" scoped>
.sticky {
  position: sticky;
  top: 0;
  z-index: 1020;
}
.nav {
  box-shadow:
    0px 1.9px 1px -10px rgba(0, 0, 0, 0.022),
    0px 4.8px 2.6px -10px rgba(0, 0, 0, 0.031),
    0px 9.7px 5.3px -10px rgba(0, 0, 0, 0.039),
    0px 20.1px 11px -10px rgba(0, 0, 0, 0.048),
    0px 55px 30px -10px rgba(0, 0, 0, 0.07);
  display: block;
  color: var(--fs-color-primary-500);
  background-color: var(--fs-color-primary-100);
}

.metanav,
.mainnav,
.sidenav {
  display: flex;
  align-items: center;
  width: 100%;
  margin: 0;
  padding: 0;
  color: var(--fs-color-primary-600);
}

.metanav,
.sidenav {
  justify-content: end;
}

::v-deep .metanav {
  font-size: 0.7rem;
  margin-top: .25rem;
  margin-bottom: .25rem;
  color: var(--fs-color-primary-500);

  & .nav-link {
    padding: 0.25rem 1rem;
  }
}

::v-deep .mainnav {
  & .nav-link {
    font-weight: 600;
  }
  @media(max-width: 768px) {
    justify-content: space-between;
  }
}

.nav .metanav-container,
.nav .nav-container {
  align-items: flex-end;
  padding-bottom: 0;
  margin-bottom: 0.25rem;

  padding-left: 0;
  padding-right: 0;

  @media(max-width: 768px ) {
    align-items: center;
  }
}

.metanav-container {
  border-bottom: 1px solid var(--fs-color-gray-200)
}

::v-deep .navbar-collapse {
  @media (max-width: 768px) {
    display: flex;
    flex-direction: column;

    & .navbar-navside {
      width: 100%;
      display: flex;
      flex-direction: column-reverse;
      margin: 1rem 0;
    }

    & .metanav,
    & .sidenav {
      flex-direction: column;
    }

    & .metanav,
    & .sidenav {
      font-size: 1rem;
      margin-bottom: unset;

      & .nav-link {
        padding: 0.5rem 1rem;

        & .badge {
          @media(max-width: 768px) {
            right: 0;

            &.overNinetyNine {
              right: 0;
            }
          }
        }
      }
    }

    & .metanav {
      margin-top: 1.5rem;
      padding-top: 1rem;
      border-top: 1px solid var(--fs-color-primary-200);
    }

    & .nav-item {
      width: 100%;

      & .dropdown-menu {
        position: unset;
        display: block;
      }

      & .dropdown-toggle {
        width: 100%;
        text-align: left;
        pointer-events: none;
        color: var(--lt-color-gray-500)
      }

      & .nav-text {
        display: unset;
      }
    }
  }
}
</style>
