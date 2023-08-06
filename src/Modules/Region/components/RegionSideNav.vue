<template>
  <container
    title="Bezirks-Menü"
    class="bg-white"
  >
    <NavRegionsLinkEntry
      :entry="region"
    />
  </container>
</template>
<script>
import NavRegionsLinkEntry from '@/components/Navigation/Regions/NavRegionsLinkEntry.vue'
import DataUser from '@/stores/user'
import regionsData from '@/stores/regions'
import Container from '@/components/Container/Container.vue'

export default {
  components: { NavRegionsLinkEntry, Container },
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
