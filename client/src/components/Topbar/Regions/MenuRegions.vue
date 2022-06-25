<template>
  <!-- eslint-disable -->
  <NavDropdown
    :title="$i18n('terminology.regions')"
    icon="fa-globe"
    id="regions"
  >
    <template
      v-if="regions.length > 0"
      #content
    >
      <MenuRegionEntry
       v-for="region in regions"
        :key="region.id"
        :entry="region"
      />
    </template>
    <template
      v-else
      #content
    >
      <small
        role="menuitem"
        class="disabled dropdown-item"
        v-html="$i18n('region.none')"
      />
    </template>
    <template #actions>
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click="becomeBezirk()"
      >
        <i class="fas fa-plus" />
        {{ $i18n('menu.entry.joinregion') }}
      </button>
    </template>
  </NavDropdown>
</template>
<script>
// Store
import { getters } from '@/stores/regions'
// Components
import NavDropdown from '../_NavItems/NavDropdown'
import MenuRegionEntry from './MenuRegionEntry'
import { becomeBezirk } from '@/script'
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'

export default {
  name: 'MenuRegions',
  components: { NavDropdown, MenuRegionEntry },
  mixins: [ConferenceOpener],
  computed: {
    regions () {
      return getters.get()
    },
  },
  methods: {
    becomeBezirk,
  },
}
</script>
