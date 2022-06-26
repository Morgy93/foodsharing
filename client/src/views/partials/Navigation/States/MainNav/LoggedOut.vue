<template>
  <ul class="mainnav">
    <Link
      :href="homeHref"
    >
      <template #text>
        <Logo />
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
</template>

<script>
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import Logo from '@/components/Navigation/Logo'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    Link,
  },
  mixins: [MediaQueryMixin],
}
</script>
