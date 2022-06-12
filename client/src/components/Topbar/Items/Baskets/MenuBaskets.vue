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
      <a
        href="#"
        role="menuitem"
        class="food-basket-create-test-class dropdown-item dropdown-action"
        @click="openBasketCreationForm"
      >
        <small>
          <i class="fas fa-plus" />
          {{ $i18n('basket.add') }}
        </small>
      </a>
      <a
        :href="$url('baskets')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <small>
          <i class="fas fa-list" />
          {{ $i18n('basket.all') }}
        </small>
      </a>
    </template>
  </fs-dropdown-menu>
</template>
<script>
import MenuBasketsEntry from './MenuBasketsEntry'
import basketStore from '@/stores/baskets'
import FsDropdownMenu from '../FsDropdownMenu'

import { ajreq } from '@/script'
import dateFnsCompareDesc from 'date-fns/compareDesc'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { MenuBasketsEntry, FsDropdownMenu },
  mixins: [TopBarMixin],
  computed: {
    baskets () {
      return basketStore.baskets
    },
    basketsSorted () {
      return this.baskets.slice().sort((a, b) => dateFnsCompareDesc(a.updatedAt, b.updatedAt))
    },
  },
  created () {
    basketStore.loadBaskets()
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
