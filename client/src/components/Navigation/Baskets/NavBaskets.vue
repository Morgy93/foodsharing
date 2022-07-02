<template>
  <Dropdown
    :title="$i18n('menu.entry.baskets')"
    icon="fa-shopping-basket"
    :badge="basketsRequestCount"
    is-fixed-size
    is-scrollable
    class="testing-basket-dropdown"
  >
    <template
      v-if="basketsSorted.length > 0"
      #content
    >
      <BasketsEntry
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
        class="testing-basket-create dropdown-item dropdown-action"
        @click="openBasketCreationForm"
      >
        <i class="icon-subnav fas fa-plus" />
        {{ $i18n('basket.add') }}
      </button>
      <a
        :href="$url('baskets')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-list" />
        {{ $i18n('basket.all') }}
      </a>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import { getters } from '@/stores/baskets'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import BasketsEntry from './NavBasketsEntry'
// Others
import { ajreq } from '@/script'
import dateFnsCompareDesc from 'date-fns/compareDesc'

export default {
  components: { BasketsEntry, Dropdown },
  computed: {
    baskets () {
      return getters.getOwn()
    },
    basketsSorted () {
      return this.baskets.slice().sort((a, b) => dateFnsCompareDesc(a.updatedAt, b.updatedAt))
    },
    basketsRequestCount () {
      return getters.getRequestdCount()
    },
  },
  methods: {
    openBasketCreationForm () {
      ajreq('newBasket', { app: 'basket' })
    },
    openRemoveBasketForm (basketId, userId) {
      ajreq('removeRequest', {
        app: 'basket',
        id: basketId,
        fid: userId,
      })
    },
  },
}
</script>
