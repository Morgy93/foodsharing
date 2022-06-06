<template>
  <List
    v-if="list.length > 0"
    :tag="title"
    :title="$i18n(title)"
    :hide="defaultAmount > list.length - 1"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <a
      v-for="(entry, key) in filteredList"
      :key="key"
      class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
      :href="$url(type, entry.id)"
    >
      <strong
        v-b-tooltip="entry.name.length > 30 ? entry.name : ''"
        class="mb-0 d-inline-block text-truncate"
        v-html="entry.name"
      />
    </a>
  </list>
</template>

<script>
import List from './_List.vue'
export default {
  components: {
    List,
  },
  props: {
    title: { type: String, default: 'dashboard.my.managing' },
    list: { type: Array, default: () => [] },
    type: { type: String, default: 'pickup' },
  },
  data () {
    const defaultAmount = 3
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
.list-group.information-list {
  margin-bottom: 1rem;
}

.list-group-item.showMore {
  padding: 0.5rem;
}

.list-group-item-action {
  cursor: pointer;
}
</style>
