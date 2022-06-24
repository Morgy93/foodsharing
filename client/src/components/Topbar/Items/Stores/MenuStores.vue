<template>
  <fs-dropdown-menu
    id="dropdown-stores"
    title="menu.entry.your_stores"
    icon="fa-shopping-cart"
    scrollbar
  >
    <template
      v-if="getAllStores.length > 0 "
      #content
    >
      <div
        v-for="(store, key) in getStores"
        :key="key"
        class="store d-flex flex-column align-items-baseline"
      >
        <button
          v-if="getStores.length !== 1"
          v-b-toggle="toggleId(key)"
          role="menuitem"
          target="_self"
          class="dropdown-item dropdown-header text-truncate"
        >
          <span
            class="text-truncate"
            v-html="$i18n(store.name)"
          />
        </button>
        <b-collapse
          :id="toggleId(key)"
          class="dropdown-submenu"
          :accordion="$options.name"
          :visible="key === 0"
        >
          <MenuStoresEntry
            v-for="(entry, k) in store.list"
            :key="k"
            :entry="entry"
          />
        </b-collapse>
      </div>
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('groups.empty')"
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
// Store
import { getters } from '@/stores/stores'

// Components
import FsDropdownMenu from '../FsDropdownMenu'
import MenuStoresEntry from './MenuStoresEntry'

// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import TopBarMixin from '@/mixins/TopBarMixin'
import TruncateMixin from '@/mixins/TruncateMixin'

export default {
  name: 'MenuStores',
  components: { FsDropdownMenu, MenuStoresEntry },
  mixins: [ConferenceOpener, MediaQueryMixin, TopBarMixin, TruncateMixin],
  computed: {
    getAllStores () {
      return getters.get()
    },

    getStores () {
      return [
        {
          name: 'dashboard.my.managing_stores',
          list: getters.getManaging(),
        },
        {
          name: 'dashboard.my.stores',
          list: getters.getOthers(),
        },
        {
          name: 'dashboard.my.waiting_stores',
          list: getters.getWaiting(),
        },
      ].filter(e => e.list.length > 0)
    },
  },
  methods: {
    toggleId (id) {
      return this.$options.name + '_' + id
    },
  },
}
</script>
