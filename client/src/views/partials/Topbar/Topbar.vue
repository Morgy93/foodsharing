<template>
  <!-- eslint-disable -->
  <div class="bootstrap nav_spacing">
    <b-navbar class="nav" toggleable="lg" fixed="top">
      <b-container class="p-0">
        <div class="nav_mainbar">
          <b-navbar-brand class="text-center">
            <Logo :href="homeHref"/>
          </b-navbar-brand>
          <NavMainLoggedIn
            v-if="isLoggedIn && user"
            :class="{
              'justify-content-start': isFoodsaver,
              'justify-content-around': isFoodsaver && !viewIsMD,
            }"
          />
          <NavMainLoggedOut v-else/>
        </div>
        <b-navbar-toggle
          target="nav_sidebar"
          class="ml-1 position-relative"
        >
          <template #default="{ expanded }">
          <div
            v-if="hasMailBox"
            class="badge badge-danger badge-navbar-toggler"
            v-html="getUnreadCount"
          />
          <i
            class="fa"
            style="min-width: 1.25rem;"
            :class="{ 'fa-bars': !expanded,'fa-times': expanded }"
          />
          </template>
        </b-navbar-toggle>
        <b-collapse :class="{'nav_sidebar': viewIsLG}" id="nav_sidebar" is-nav>
            <NavSideLoggedIn
              v-if="isLoggedIn && user"
            />
            <NavSideLoggedOut v-else/>
        </b-collapse>
      </b-container>
    </b-navbar>
    <language-chooser />
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
import Logo from '@/components/Topbar/Items/Logo'
// Logged Out
import NavMainLoggedOut from '@/components/Topbar/States/LoggedOut/NavMain'
import NavSideLoggedOut from '@/components/Topbar/States/LoggedOut/NavSide'
// Logged In
import NavMainLoggedIn from '@/components/Topbar/States/LoggedIn/NavMain'
import NavSideLoggedIn from '@/components/Topbar/States/LoggedIn/NavSide'

// Hidden Elements
import LanguageChooser from '@/components/Topbar/Items/LanguageChooser'

// Mixins
import TopBarMixin from '@/mixins/TopBarMixin'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'

export default {
  components: {
    Logo,
    LanguageChooser,
    NavMainLoggedOut,
    NavSideLoggedOut,
    NavMainLoggedIn,
    NavSideLoggedIn,
  },
  mixins: [TopBarMixin, StateTogglerMixin],
  props: {
    regions: {
      type: Array,
      default: () => [],
    },
    groups: {
      type: Array,
      default: () => [],
    },
    isLoggedIn: {
      type: Boolean,
      default: false,
    },
  },
  async created () {
    // TODO: NO APIS :(
    DataGroups.mutations.set(this.groups)
    DataRegions.mutations.set(this.regions)
    DataUser.mutations.setLoggedIn(this.isLoggedIn)

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
.nav,
.nav_mainbar,
.nav_spacing {
  min-height: 55px;
}

.nav {
  box-shadow: 0em 0em 5px 0px rgba(0, 0, 0, 0.35);
  background-color: var(--fs-beige);
  color: var(--primary);
}

::v-deep .navbar-light .nav_mainbar,
::v-deep .navbar-light .nav_sidebar .navbar-nav {
  display: flex;
  flex-direction: row;
  align-items: center;

  .nav-link {
    display: inline-flex;
    padding-right: 0.5rem;
    padding-left: 0.5rem;
    align-items: center;
    color: currentColor;

    .headline {
      margin-left: 0.25rem;
    }
  }
}

::v-deep .nav_mainbar {
  margin-left: 0;
  width: 100%;

  .dropdown {
    @media (max-width: 768px) {
      position: unset;
    }
  }

  .dropdown-menu {
    position: absolute;
    box-shadow: 0em 1em 5px -10px rgba(0, 0, 0, 0.35);

    @media(min-width: 768px) and (max-width: 1024px) {
      transform: translateX(-35%);
    }

    @media (max-width: 768px) {
      top: 45px;
      left: 0;
      width: 100%;
      min-width: 100%;
      max-width: 100%;
    }
  }

  @media (max-width: 768px) {
    justify-content: space-around;
  }

  @media (max-width: 1200px) {
    flex-grow: 1;
    width: auto;
    justify-content: space-around;
  }
}

::v-deep .nav_sidebar {
  justify-content: space-between;
  flex-grow: unset;

  & .navbar-nav {
    margin: 0;
  }
}

::v-deep #nav_sidebar.collapse.show {
  max-width: 100%;

  & .navbar-nav {
    padding: 0;
    margin: 1.5rem 0;
    overflow-x: auto;
    max-height: 80vh;
  }

  & .nav-item {
    margin-top: .5rem;
    border-radius: var(--border-radius);
    background-color: var(--white);
    align-items: center;
  }

  & .nav-link {
    display: flex;
    align-items: center;
    min-height: 2.5rem;
    padding: 0.25rem 0.5rem;
    color: currentColor;
    font-weight: bold;
  }

  & .dropdown-menu {
    min-width: initial;
    max-width: initial;
    border: 0;
  }

  & #search-results {
    box-shadow: none;
  }
}

::v-deep .dropdown-menu {
  min-width: 420px;
  max-width: 420px;

  .dropdown-action i {
    width: 1rem;
    margin-right: 0.5rem;
  }

  .dropdown-header  {
    // font-size: 1.05rem;
    font-weight: bold;
  }

  .dropdown-header,
  .list-group-item-action {
    color: var(--dark);

    &:active {
      color: var(--white);
    }
  }

  .dropdown-submenu {
    width: 100%;
  }
}

::v-deep .badge {
  position: absolute;
  top: -1px;
  left: 1.25rem;
  font-size: 0.75rem;
}

.navbar-toggler .badge-navbar-toggler {
    top: -5px;
    left: 1.8rem;
}

::v-deep .dropdown-action .badge {
  top: 4px;
  right: 1rem;
  left: auto;
}

::v-deep .headline {
  color: currentColor;
}

::v-deep .icon {
  color: currentColor;
  min-width: 1rem;
  font-size: 1rem;

  .collapse.show & {
    margin-right: .5rem;
    text-align: center;
    width: 34px;
  }
}

::v-deep .spacer {
  margin-right: 1rem;

  @media (max-width: 876px) {
    display: none;
  }
}
</style>
