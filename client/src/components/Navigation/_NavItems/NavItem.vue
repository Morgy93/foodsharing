<template>
  <Dropdown
    v-if="entry.items"
    :title="$i18n(entry.title)"
    :icon="entry.icon"
    :badge="entry.badge"
    :direction="entry.direction"
    :is-fixed-size="entry.isFixedSize"
    :is-scrollable="entry.isScrollable"
  >
    <template #content>
      <span
        v-for="item in filteredItems"
        :key="item.href"
      >
        <div
          v-if="item.divider"
          class="dropdown-divider"
        />
        <a
          v-else
          :href="item.url ? $url(item.url) : $url('javascript', item.func)"
          role="menuitem"
          class="dropdown-item dropdown-action"
        >
          <i
            v-if="item.icon"
            class="icon-subnav fas"
            :class="item.icon"
          />
          {{ $i18n(item.title) }}
        </a>
      </span>
    </template>
  </Dropdown>
  <Link
    v-else
    :title="$i18n(entry.title)"
    :icon="entry.icon"
    :badge="entry.badge"
    :href="$url(entry.url)"
    :class="{
      'text-warning font-weight-bold': entry.highlight,
    }"
  />
</template>

<script>
// Stores
import DataUser from '@/stores/user'
// Components
import Dropdown from './NavDropdown.vue'
import Link from './NavLink.vue'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
export default {
  components: {
    Dropdown,
    Link,
  },
  mixins: [MediaQueryMixin],
  props: {
    entry: {
      type: {},
      default: () => ({
        title: '',
        url: '',
        icon: '',
        direction: '',
        badge: '',
        isScrollable: false,
        isFixedSize: false,
        items: [],
      }),
    },
  },
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
    filteredItems () {
      return this.entry.items.filter(item => !item.isInternal)
    },
  },
}
</script>
