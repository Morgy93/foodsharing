<template>
  <!-- eslint-disable -->
  <div class="bootstrap nav_spacing">
    <b-navbar class="nav" toggleable="xl" fixed="top">
      <b-container class="p-0">
        <div class="nav_mainbar">
          <b-navbar-brand class="text-center">
            <Logo :href="homeHref"/>
          </b-navbar-brand>
          <NavMainLoggedIn
            v-if="user"
            :user="user"
            :regions="regions"
            :groups="groups"
            :class="{
              'justify-content-around': isFoodsaver,
            }"
          />
          <NavMainLoggedOut v-else/>
        </div>

        <b-navbar-toggle
          target="nav_sidebar"
          class="nav_toggle"
          @click.stop="toggleMenu()"
        >
          <i
            class="fa fa-bars"
            :class="{ 'fa-bars': state,'fa-times': state }"
          />
        </b-navbar-toggle>
        <b-collapse :class="{'nav_sidebar': !isVisibleOnMobile}" id="nav_sidebar" is-nav>
            <NavSideLoggedIn
              v-if="user"
              :user="user"
            />
            <NavSideLoggedOut v-else/>
        </b-collapse>
      </b-container>
    </b-navbar>
  </div>
</template>

<script>
import Logo from '@/components/Topbar/Items/Logo'
// Logged Out
import NavMainLoggedOut from '@/components/Topbar/States/LoggedOut/NavMain'
import NavSideLoggedOut from '@/components/Topbar/States/LoggedOut/NavSide'
// Logged In
import NavMainLoggedIn from '@/components/Topbar/States/LoggedIn/NavMain'
import NavSideLoggedIn from '@/components/Topbar/States/LoggedIn/NavSide'

import TopBarMixin from '@/mixins/TopBarMixin'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'

export default {
  components: {
    Logo,
    NavMainLoggedOut,
    NavSideLoggedOut,
    NavMainLoggedIn,
    NavSideLoggedIn,
  },
  mixins: [TopBarMixin, StateTogglerMixin],
  watch: {
    isVisibleOnMobile (newVal) {
      if (this.state && newVal === false) {
        this.state = false
        this.toggleBody()
      }
    },
  },
  methods: {
    toggleMenu () {
      this.toggleState()
      this.toggleBody()
    },
    toggleBody () {
      if (this.state) {
        document.querySelector('#main').style = 'opacity: 0.25; pointer-events: none; user-select: none;'
        document.body.classList.add('overflow-hidden')
      } else {
        document.body.classList.remove('overflow-hidden')
        document.querySelector('#main').style = ''
      }
    },
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
}

::v-deep .nav_mainbar,
::v-deep .nav_sidebar .navbar-nav {
  display: flex;
  flex-direction: row;
  align-items: center;

  .nav-link {
    display: inline-flex;
    padding-right: 0.5rem;
    padding-left: 0.5rem;
    align-items: center;

    .headline {
      margin-left: 0.25rem;
    }
  }
}

::v-deep .nav_mainbar {
  margin-left: 0;
  width: 100%;

  .dropdown-menu {
    position: absolute;
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

.nav_toggle {
  padding: 0.25rem 0.5rem;
  & i {
    min-width: 20px;
  }
}

::v-deep .dropdown-menu {
  min-width:300px;
  max-width: 320px;

  .dropdown-item i {
    width: 1rem;
    margin-right: 0.5rem;
  }

  .dropdown-header  {
    font-size: 1.05rem;
    font-weight: bold;
  }

  .dropdown-submenu {
    width: 100%;
  }
}

::v-deep .badge {
  position: absolute;
  top: -1px;
  left: 18px;

  .collapse.show & {
    left: -10px;
  }
}

::v-deep .icon {
  color: var(--primary);
  min-width: 1rem;
  .collapse.show & {
    margin-right: .5rem;
    text-align: center;
    width: 34px;
  }
}

::v-deep .spacer {
  margin-right: 1rem;
}

@media (max-width: 576px) {

  .nav_mainbar {
    justify-content: space-around;
  }

  ::v-deep .spacer,
  ::v-deep .item a::after {
    margin-right: unset;
  }

  ::v-deep .dropdown {
    position: unset;
  }

  ::v-deep .dropdown-menu  {
    top: 45px;
    left: 0;
    width: 100%;
    max-width: 100%;
  }
}
</style>
