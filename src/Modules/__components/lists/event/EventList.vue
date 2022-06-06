<template>
  <List
    v-if="list.length > 0"
    :tag="title"
    :title="count > 1 ? $i18n('dashboard.events', { count }) : $i18n(title)"
    :hide="defaultAmount > list.length - 1"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <EventField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
    />
  </List>
</template>

<script>
import EventField from './EventField.vue'
import List from '../_List.vue'
export default {
  components: {
    List,
    EventField,
  },
  props: {
    title: { type: String, default: 'dashboard.event' },
    count: { type: Number, default: 0 },
    list: { type: Array, default: () => [] },
  },
  data () {
    const defaultAmount = 2
    return {
      defaultAmount: defaultAmount,
      amount: defaultAmount,
    }
  },
  computed: {
    filteredList () {
      return this.list.slice(0, this.amount)
    },
  },
  methods: {
    showFullList () {
      this.amount = this.list.length
    },
    reduceList () {
      this.amount = this.defaultAmount
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
