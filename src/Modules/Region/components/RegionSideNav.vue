<template>
  <div class="container">
    <NavRegionsLinkEntry
      :entry="region"
    />
  </div>
</template>

<script>
import NavRegionsLinkEntry from '@/components/Navigation/Regions/NavRegionsLinkEntry.vue'
import DataUser from '@/stores/user'
import regionsData from '@/stores/regions'

export default {
  components: { NavRegionsLinkEntry },
  props: {
    regionId: { type: Number, required: true },
    isWorkGroup: { type: Boolean, required: true },
  },
  computed: {
    region () {
      const homeRegion = DataUser.getters.getHomeRegion()
      let entrys = []
      if (!this.isWorkGroup) {
        entrys = regionsData.getters.get().slice().sort((a, b) => {
          if (a.id === homeRegion) return -1
          if (b.id === homeRegion) return 1
          else return a.name.localeCompare(b.name)
        })
      }
      return entrys.find(region => region.id === this.regionId)
    },
  },
}
</script>
