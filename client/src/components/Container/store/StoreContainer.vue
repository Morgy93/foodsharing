<template>
  <Container
    :tag="title"
    :title="$i18n(title)"
    :toggle-visiblity="data.length > defaultAmount"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <StoreField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
    />
    <small
      v-if="filteredList.length === 0"
      class="list-group-item text-muted"
      v-html="$i18n('store.noStores')"
    />
  </Container>
</template>

<script>
// Stores
import { getters } from '@/stores/stores'
// Components
import Container from '../Container.vue'
import StoreField from './StoreField'
// Mixin
import ListToggleMixin from '@/mixins/ContainerToggleMixin'

export default {
  name: 'StoreList',
  components: {
    Container,
    StoreField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'dashboard.my.stores' },
  },
  computed: {
    data () {
      const data = getters.get()
      this.setList(data)
      return data
    },
  },
}
</script>
