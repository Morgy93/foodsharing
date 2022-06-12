<template>
  <List
    v-if="list.length > 0"
    :tag="title"
    :title="$i18n(title)"
    :hide="defaultAmount >= list.length"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <PickupField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
    />
  </list>
</template>

<script>
import ListToggleMixin from '@/mixins/ListToggleMixin'

import List from '../_List.vue'
import PickupField from './PickupField'

export default {
  components: {
    List,
    PickupField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'dashboard.pickupdates' },
  },
}
</script>
