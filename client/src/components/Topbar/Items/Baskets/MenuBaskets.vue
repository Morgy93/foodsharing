<template>
  <fs-dropdown-menu
    ref="dropdown"
    class="topbar-baskets"
    title="menu.entry.your_baskets"
    icon="fa-shopping-basket"
    :badge="basketsSorted.length "
    :show-title="showTitle"
    scrollbar
  >
    <template
      v-if="basketsSorted.length > 0"
      #content
    >
      <menu-baskets-entry
        v-for="basket in basketsSorted"
        :key="basket.id"
        :basket="basket"
        @basket-remove="openRemoveBasketForm"
      />
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('basket.my_list_empty')"
      />
    </template>
    <template #actions>
      <button
        role="menuitem"
        class="food-basket-create-test-class dropdown-item dropdown-action"
        @click="openBasketCreationForm"
      >
        <i class="fas fa-plus" />
        {{ $i18n('basket.add') }}
      </button>
      <a
        :href="$url('baskets')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-list" />
        {{ $i18n('basket.all') }}
      </a>
    </template>
  </fs-dropdown-menu>
</template>
<script>
// DataStore
import { getters } from '@/stores/baskets'
// Components
import MenuBasketsEntry from './MenuBasketsEntry'

import FsDropdownMenu from '../FsDropdownMenu'

import { ajreq } from '@/script'
import dateFnsCompareDesc from 'date-fns/compareDesc'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuBasketsEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  computed: {
    baskets () {
      return getters.getOwn()
    },
    basketsSorted () {
      return this.baskets.slice().sort((a, b) => dateFnsCompareDesc(a.updatedAt, b.updatedAt))
    },
  },
  methods: {
    openBasketCreationForm () {
      this.$refs.dropdown.visible = false
      ajreq('newBasket', { app: 'basket' })
    },
    openRemoveBasketForm (basketId, userId) {
      this.$refs.dropdown.visible = false
      ajreq('removeRequest', {
        app: 'basket',
        id: basketId,
        fid: userId,
      })
    },
  },
}
</script>
