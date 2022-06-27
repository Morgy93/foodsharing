<template>
  <ul class="mainnav">
    <Link
      title="Foodsharing"
      :href="$url('home')"
    >
      <template #text>
        <Logo />
      </template>
    </Link>

    <Dropdown
      v-for="(category, idx) in mainNav"
      :key="idx"
      :title="$i18n(category.title)"
      :icon="category.icon"
    >
      <template #content>
        <a
          v-for="(entry, key) in category.items"
          :key="key"
          :href="$url(entry.url)"
          role="menuitem"
          class="dropdown-item dropdown-action"
          v-html="$i18n(entry.title)"
        />
      </template>
    </Dropdown>

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
import MainNavData from '../../Data/MainNavData.json'
//
import Dropdown from '@/components/Navigation/_NavItems/NavDropdown'
import Link from '@/components/Navigation/_NavItems/NavLink'
import Logo from '@/components/Navigation/Logo'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    Link,
    Dropdown,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      mainNav: MainNavData,
    }
  },
}
</script>
