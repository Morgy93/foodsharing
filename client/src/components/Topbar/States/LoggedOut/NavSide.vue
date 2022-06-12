<template>
  <b-navbar-nav>
    <menu-item
      :url="$url('joininfo')"
      icon="fa-hands-helping"
      :title="$i18n('register.topbar')"
      :show-title="true"
    />
    <menu-item
      id="login"
      :url="loginReferrerHref"
      icon="fa-sign-in-alt"
      :title="$i18n('login.topbar')"
      :show-title="true"
    />
  </b-navbar-nav>
</template>

<script>
import MenuItem from '../../Items/MenuItem'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: {
    MenuItem,
  },
  mixins: [TopBarMixin],
}

</script>
