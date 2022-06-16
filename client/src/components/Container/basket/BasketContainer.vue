<template>
  <Container
    :tag="hasCoords ? 'basket.nearby' : 'basket.recent'"
    :title="$i18n(hasCoords ? 'basket.nearby' : 'basket.recent')"
    :toggle-visiblity="data.length > defaultAmount"
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
      v-html="$i18n('basket.no_nearby', {radius})"
    />
  </Container>
</template>
<script>
// Stores
import { getters } from '@/stores/baskets'
import DataUser from '@/stores/user'
// Components
import Container from '../Container.vue'
import BasketField from './BasketField'
// Mixin
import ListToggleMixin from '@/mixins/ContainerToggleMixin'

export default {
  name: 'BasketList',
  components: {
    Container,
    BasketField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'dashboard.pickupdates' },
  },
  computed: {
    radius () {
      return getters.getRadius()
    },
    hasCoords () {
      return DataUser.getters.getCoordinates()
    },
    data () {
      const data = getters.getNearby()
      this.setList(data)
      return data
    },
  },
}
</script>
