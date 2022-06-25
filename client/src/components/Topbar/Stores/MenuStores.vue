<template>
  <NavDropdown
    id="dropdown-stores"
    :title="$i18n('menu.entry.stores')"
    icon="fa-shopping-cart"
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
        v-if="hasAddStorePermission"
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
  </NavDropdown>
</template>

<script>
// Stores
import DataUser from '@/stores/user'
import { getters } from '@/stores/stores'
// Components
import NavDropdown from '../_NavItems/NavDropdown'
import MenuStoresEntry from './MenuStoresEntry'

export default {
  components: { MenuStoresEntry, NavDropdown },
  computed: {
    stores () {
      return getters.get()
    },
    hasAddStorePermission () {
      return DataUser.getters.getPermissions().addStore || false
    },
  },
}
</script>
