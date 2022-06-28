<template>
  <Link
    v-if="!isDropdown"
    :title="$i18n(entry.title)"
    :icon="entry.icon"
    :badge="entry.badge"
    :href="$url(entry.url)"
    :class="{
      'text-warning font-weight-bold': entry.isHighlighted,
    }"
  />
  <Dropdown
    v-else
    :title="$i18n(entry.title)"
    :icon="entry.icon"
    :badge="entry.badge"
    :direction="entry.direction"
    :is-fixed-size="entry.isFixedSize"
    :is-scrollable="entry.isScrollable"
  >
    <template #content>
      <span
        v-for="(item, idx) in entry.items"
        :key="idx"
      >
        <div
          v-if="item.isDivider"
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
</template>

<script>
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
      type: Object,
      default: () => ({
        title: '',
        url: undefined,
        icon: '',
        direction: '',
        badge: '',
        isScrollable: false,
        isFixedSize: false,
        isInternal: false,
        isHighlighted: false,
        items: [],
      }),
    },
  },

  computed: {
    isDropdown () {
      return !this.entry.url && this.entry.items
    },
  },
}
</script>
