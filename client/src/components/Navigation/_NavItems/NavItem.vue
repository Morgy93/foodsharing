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
        <b-dropdown-divider
          v-if="item.isDivider"
        />
        <b-dropdown-item
          v-else
          :href="item.url ? $url(item.url) : null"
          @click.prevent="item.modal ? $bvModal.show(item.modal) : null"
        >
          <i
            v-if="item.icon"
            class="icon-subnav fas"
            :class="item.icon"
          />
          {{ $i18n(item.title) }}
        </b-dropdown-item>
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
