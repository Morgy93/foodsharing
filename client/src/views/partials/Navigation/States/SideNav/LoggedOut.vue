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
    </ul>
    <ul class="mainnav">
      <Link
        :href="$url('joininfo')"
        :title="$i18n('register.topbar')"
      />
      <NavLogin />
    </ul>
  </ul>
</template>

<script>
// Store
import MetaNavData from '../../MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import NavLogin from '@/components/Navigation/Login/NavLogin.vue'

//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import ScrollMixin from '@/mixins/ScrollMixin'

export default {
  components: {
    Link,
    NavLogin,
  },
  mixins: [MediaQueryMixin, ScrollMixin],
  data () {
    return {
      metaNav: MetaNavData,
    }
  },
}
</script>
