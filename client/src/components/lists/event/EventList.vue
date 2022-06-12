<template>
  <List
    v-if="count > 0"
    :tag="title"
    :title="count > 1 ? $i18n(`${title}Count`, { count }) : $i18n(title)"
    :hide="defaultAmount >= count"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <EventField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
      :options="options"
    />
  </List>
</template>

<script>
import ListToggleMixin from '@/mixins/ListToggleMixin'

import EventField from './EventField.vue'
import List from '../_List.vue'
export default {
  components: {
    List,
    EventField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'dashboard.event' }, // dashboard.event -> dashboard.eventCount
    options: { type: Boolean, default: false },
  },
  data () {
    const defaultAmount = 2
    return {
      defaultAmount,
      amount: defaultAmount,
    }
  },
  computed: {
    count () {
      return this.list.length
    },
  },
}
</script>
