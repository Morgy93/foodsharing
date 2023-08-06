<template>
  <div>
    <GenderList />
    <AgeBandList />
    <PickupList />
  </div>
</template>

<script>
import GenderList from './GenderList.vue'
import AgeBandList from './AgeBandList.vue'
import PickupList from './PickupList.vue'

export default {
  name: 'Statistics',
  components: { PickupList, AgeBandList, GenderList },
}
</script>
