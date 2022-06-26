<template>
  <div>
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
      </ul>
    </ul>
    <ul class="container nav-container">
      <ul class="mainnav">
        <Link
          :href="homeHref"
        >
          <template #text>
            <Logo small />
          </template>
        </Link>

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

      <div
        id="sidebar"
        class="collapse navbar-collapse"
      >
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
            <Dropdown>
              <NavLogin />
            </Dropdown>
          </ul>
        </ul>
      </div>
    </ul>
  </div>
</template>

<script>
// Store
import DataUser from '@/stores/user'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import Dropdown from '@/components/Navigation/_NavItems/NavDropdown'
import Logo from '@/components/Navigation/Logo'
//
import NavLogin from '@/components/Navigation/Login/NavLogin.vue'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    Link,
    Dropdown,
    NavLogin,
  },
  mixins: [MediaQueryMixin],
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
}
</script>
