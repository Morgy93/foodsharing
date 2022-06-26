<template>
  <Dropdown
    id="dropdown-groups"
    :title="$i18n('menu.entry.groups')"
    icon="fa-users"
    scrollable
  >
    <template
      v-if="groups.length > 0"
      #content
    >
      <GroupsEntry
        v-for="(group, idx) in groups"
        :key="idx"
        :entry="group"
        :is-alone="groups.length === 1"
      />
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
        :href="$url('workingGroups')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-users" />
        {{ $i18n('menu.entry.groups') }}
      </a>
    </template>
  </Dropdown>
</template>
<script>
// Store
import { getters } from '@/stores/groups'
// Components
import Dropdown from '../_NavItems/NavDropdown'
import GroupsEntry from './NavGroupsEntry'
// Mixins
import ConferenceOpener from '@/mixins/ConferenceOpenerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  name: 'MenuGroups',
  components: { Dropdown, GroupsEntry },
  mixins: [ConferenceOpener, MediaQueryMixin],
  computed: {
    groups () {
      return getters.get()
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../../scss/icon-sizes.scss';
</style>
