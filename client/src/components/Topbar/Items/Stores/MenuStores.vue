<template>
  <fs-dropdown-menu
    id="dropdown-stores"
    title="menu.entry.your_stores"
    icon="fa-shopping-cart"
    scrollbar
    lazy
  >
    <template
      v-if="stores.length > 0"
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
        v-if="user.permissions.addStore"
        :href="$url('storeAdd')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-plus" />
        {{ $i18n('storeedit.add-new') }}
      </a>
      <a
        :href="$url('storeList')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-list" />
        {{ $i18n('store.all_of_my_stores') }}
      </a>
    </template>
  </fs-dropdown-menu>
</template>

<script>
// Stores
import { getters } from '@/stores/stores'
// Components
import MenuStoresEntry from './MenuStoresEntry'
import FsDropdownMenu from '../FsDropdownMenu'
// Mixin
import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuStoresEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  computed: {
    stores () {
      return getters.get()
    },
  },
}
</script>
