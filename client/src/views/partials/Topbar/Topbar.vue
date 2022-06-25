<template>
  <!-- eslint-disable -->
  <div class="bootstrap">
  <nav class="nav navbar navbar-expand-md">
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
        <Dropdown title="Admin" direction="right"/>
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
        <Dropdown v-if="!viewIsMobile" icon="fa-users" title="Gruppen"/>
        <Dropdown icon="fa-shopping-cart" title="Stores"/>
        <Dropdown icon="fa-shopping-basket" title="Essenskörbe"/>

        <Dropdown v-if="viewIsMobile" icon="fa-comments" title="Nachrichten"/>
        <Dropdown v-if="viewIsMobile" icon="fa-bell" title="Benachrichtigungen"/>

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
            <Dropdown title="Admin" direction="right"/>
          </ul>
          <ul class="sidenav">
            <Link icon="fa-search" title="Suche" data-toggle="modal" data-target="#searchBarModal"/>
            <Link v-if="viewIsMobile" icon="fa-globe" title="Bezirke"/>
            <Link v-if="viewIsMobile" icon="fa-comments" title="Gruppen"/>
            <Dropdown v-if="!viewIsMobile" icon="fa-comments" title="Nachrichten" direction="right"/>
            <Dropdown v-if="!viewIsMobile" badge="3" icon="fa-bell" title="Benachrichtigungen" direction="right"/>
            <Dropdown icon="fa-user-circle" title="Hallo Stefan" direction="right"/>
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
import DataStores from '@/stores/stores'
import DataBaskets from '@/stores/baskets'
import DataGroups from '@/stores/groups.js'
import DataRegions from '@/stores/regions.js'
//
import Dropdown from '@/components/Topbar/_NavItems/NavDropdown'
import Link from '@/components/Topbar/_NavItems/NavLink'
import Logo from '@/components/Topbar/Logo'
// Hidden Elements
import LanguageChooser from '@/components/Topbar/LanguageChooser'
import SearchBarModal from '@/components/SearchBar/SearchBarModal'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    LanguageChooser,
    SearchBarModal,
    Dropdown,
    Link,
  },
  mixins: [MediaQueryMixin],
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
    homeHref () {
      return (this.isLoggedIn) ? this.$url('dashboard') : this.$url('home')
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
      if (this.isFoodsaver) {
        await DataStores.mutations.fetch()
        if (this.hasMailBox) {
          await DataUser.mutations.fetchMailUnreadCount()
        }
      }
    }
  },
}
</script>
<style lang="scss" scoped>
.nav {
  box-shadow:
    0px 1.9px 1px -10px rgba(0, 0, 0, 0.022),
    0px 4.8px 2.6px -10px rgba(0, 0, 0, 0.031),
    0px 9.7px 5.3px -10px rgba(0, 0, 0, 0.039),
    0px 20.1px 11px -10px rgba(0, 0, 0, 0.048),
    0px 55px 30px -10px rgba(0, 0, 0, 0.07);
  display: block;
  position: sticky;
  top: 0;
  z-index: 1020;
  color: var(--fs-color-primary-500);
  background-color: var(--fs-color-primary-100);
  border-bottom: 1px solid var(--fs-color-primary-200);
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
  border-bottom: 1px solid var(--fs-color-primary-200)
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

      & .nav-text {
        display: unset;
      }
    }
  }
}
</style>
