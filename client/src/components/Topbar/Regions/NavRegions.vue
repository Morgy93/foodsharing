<template>
  <Dropdown
    :title="$i18n('terminology.regions')"
    icon="fa-globe"
  >
    <template
      v-if="regions.length > 0"
      #content
    >
      <RegionsEntry
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
  </Dropdown>
</template>
<script>
// Store
import { getters } from '@/stores/regions'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import RegionsEntry from './NavRegionsEntry'
import { becomeBezirk } from '@/script'

export default {
  name: 'MenuRegions',
  components: { Dropdown, RegionsEntry },
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
