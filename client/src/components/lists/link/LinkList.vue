<template>
  <List
    v-if="list.length > 0"
    :tag="title"
    :title="$i18n(title)"
    :hide="defaultAmount >= list.length"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <LinkField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
      :type="type"
    />
  </list>
</template>

<script>
import ListToggleMixin from '@/mixins/ListToggleMixin'

import List from '../_List.vue'
import LinkField from './LinkField.vue'

export default {
  components: {
    List,
    LinkField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'home.home' },
    type: { type: String, default: 'home' },
  },
}
</script>
