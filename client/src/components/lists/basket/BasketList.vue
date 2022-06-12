<template>
  <List
    :tag="title"
    :title="$i18n(title)"
    :hide="defaultAmount >= list.length"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <BasketField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
    />
    <small
      v-if="filteredList.length === 0"
      class="list-group-item text-muted"
      v-html="$i18n('basket.no_nearby', {radius: 30})"
    />
  </List>
</template>
<script>
// Radius is in the DashboardController.php defined.

import ListToggleMixin from '@/mixins/ListToggleMixin'

import List from '../_List.vue'
import BasketField from './BasketField'

export default {
  components: {
    List,
    BasketField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'basket.nearby' },
  },
}
</script>
