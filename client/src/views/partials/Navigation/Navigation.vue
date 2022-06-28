<template>
  <div
    class="bootstrap"
    :class="{ 'sticky': viewIsMD }"
  >
    <nav
      class="nav navbar navbar-expand-md"
      :class="{'nav-visible': isLoggedIn}"
    >
      <div class="metanav-container container">
        <MetaNavLoggedIn v-if="!viewIsMobile && isLoggedIn" />
        <MetaNavLoggedOut v-else-if="!viewIsMobile" />
      </div>
      <div class="container nav-container">
        <MainNavLoggedIn v-if="isLoggedIn" />
        <MainNavLoggedOut v-else />

        <div
          id="sidebar"
          class="collapse navbar-collapse"
        >
          <SideNavLoggedIn v-if="isLoggedIn" />
          <SideNavLoggedOut v-else />
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
// Store
import DataUser from '@/stores/user.js'
import DataBells from '@/stores/bells.js'
import DataStores from '@/stores/stores.js'
import DataBaskets from '@/stores/baskets.js'
import DataConversations from '@/stores/conversations.js'
import DataGroups from '@/stores/groups.js'
import DataRegions from '@/stores/regions.js'
// States
import MetaNavLoggedIn from './States/MetaNav/LoggedIn.vue'
import MetaNavLoggedOut from './States/MetaNav/LoggedOut.vue'
import MainNavLoggedIn from './States/MainNav/LoggedIn.vue'
import MainNavLoggedOut from './States/MainNav/LoggedOut.vue'
import SideNavLoggedIn from './States/SideNav/LoggedIn.vue'
import SideNavLoggedOut from './States/SideNav/LoggedOut.vue'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  name: 'Navigation',
  components: {
    MetaNavLoggedIn,
    MetaNavLoggedOut,
    MainNavLoggedIn,
    MainNavLoggedOut,
    SideNavLoggedIn,
    SideNavLoggedOut,
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
    isLoggedIn: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      navIsSmall: false,
    }
  },
  computed: {
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
    DataUser.mutations.setLoggedIn(this.isLoggedIn)

    // Load data
    if (this.isLoggedIn) {
      // TODO: NO APIS :(
      DataGroups.mutations.set(this.groups)
      DataRegions.mutations.set(this.regions)
      await DataUser.mutations.fetchDetails()
      await DataBaskets.mutations.fetchOwn()
      await DataBells.mutations.fetch()
      await DataConversations.mutations.fetchConversations()
    }
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

::v-deep .metanav,
::v-deep .mainnav,
::v-deep .sidenav {
  display: flex;
  align-items: center;
  width: 100%;
  margin: 0;
  padding: 0;
  color: var(--fs-color-primary-600);
}

::v-deep .metanav,
::v-deep .sidenav {
  justify-content: end;
}

::v-deep .metanav-container {
  border-bottom: 1px solid var(--fs-color-primary-alpha-10);

  & .metanav {
    font-size: 0.7rem;
    margin-top: .25rem;
    margin-bottom: .25rem;
    color: var(--fs-color-gray-500);
  }

  & .nav-link {
    padding: 0.25rem 1rem;
  }
}

::v-deep .mainnav {
  @media(max-width: 768px) {
    justify-content: space-between;
  }
}

::v-deep .nav .metanav-container,
::v-deep .nav .nav-container {
  align-items: center;
  padding-bottom: 0;
  margin-bottom: 0.25rem;

  padding-left: 0;
  padding-right: 0;

  @media(max-width: 768px ) {
    align-items: center;
  }
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
      }
    }

    & .metanav {
      margin-top: 1.5rem;
      padding-top: 1rem;
      border-top: 1px solid var(--fs-color-primary-200);
    }

    & .nav-link {
      & .icon-subnav {
        display: none;
      }
    }

    & .nav-item {
      width: 100%;

      & .dropdown-menu {
        position: unset;
        display: block;
      }

      & .dropdown-toggle {
        display: none;
      }

      & .nav-text {
        display: unset;
      }
    }
  }
}
</style>
