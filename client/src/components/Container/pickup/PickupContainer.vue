<template>
  <Container
    v-if="data.length > 0"
    :tag="title"
    :title="$i18n(title)"
    :toggle-visiblity="data.length > defaultAmount"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <PickupField
      v-for="(entry, key) in filteredList"
      :key="key"
      :entry="entry"
    />
  </Container>
</template>

<script>
// Stores
import { getters } from '@/stores/pickups'
// Components
import Container from '../Container.vue'
import PickupField from './PickupField'
// Mixin
import ListToggleMixin from '@/mixins/ContainerToggleMixin'

export default {
  name: 'RegionList',
  components: {
    Container,
    PickupField,
  },
  mixins: [ListToggleMixin],
  props: {
    title: { type: String, default: 'dashboard.pickupdates' },
  },
  computed: {
    data () {
      const data = getters.getRegistered()
      this.setList(data)
      return data
    },
  },
}
</script>
