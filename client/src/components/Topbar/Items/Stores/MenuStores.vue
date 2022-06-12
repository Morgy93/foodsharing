<template>
  <fs-dropdown-menu
    id="dropdown-stores"
    title="menu.entry.your_stores"
    icon="fa-shopping-cart"
    scrollbar
    lazy
  >
    <template
      v-if="!loaded"
      #content
    >
      <i class="fas fa-spinner fa-spin" />
    </template>
    <template
      v-else-if="stores.length > 0"
      #content
    >
      <menu-stores-entry
        v-for="store in stores"
        :key="store.id"
        :entry="store"
      />
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('store.noStores')"
      />
    </template>
    <template #actions>
      <a
        v-if="mayAddStore"
        :href="$url('storeAdd')"
        role="menuitem"
        class="dropdown-item"
      >
        <small>
          <i class="fas fa-plus" />
          {{ $i18n('storeedit.add-new') }}
        </small>
      </a>
      <a
        :href="$url('storeList')"
        role="menuitem"
        class="dropdown-item"
      >
        <small>
          <i class="fas fa-list" />
          {{ $i18n('store.all_of_my_stores') }}
        </small>
      </a>
    </template>
  </fs-dropdown-menu>
</template>

<script>
import MenuStoresEntry from './MenuStoresEntry'
import FsDropdownMenu from '../FsDropdownMenu'
import vueStore from '@/stores/stores'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuStoresEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  props: {
    mayAddStore: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      loaded: false,
    }
  },
  computed: {
    stores () {
      return vueStore.stores || []
    },
  },
  async created () {
    this.loadStores()
  },
  methods: {
    async loadStores () {
      if (vueStore.stores === null) {
        await vueStore.loadStores()
      }
      this.loaded = true
    },
  },
}
</script>
