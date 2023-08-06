<template>
  <div class="container">
    <NavRegionsLinkEntry
      v-if="!isWorkGroup"
      :entry="region"
    />
    <NavGroupsLinkEntry
      v-if="isWorkGroup"
      :entry="group"
    />
  </div>
</template>

<script>
import NavRegionsLinkEntry from '@/components/Navigation/Regions/NavRegionsLinkEntry.vue'
import NavGroupsLinkEntry from '@/components/Navigation/Groups/NavGroupsLinkEntry.vue'
import DataUser from '@/stores/user'
import regionsData from '@/stores/regions'
import groupsData from '@/stores/groups'

export default {
  components: { NavRegionsLinkEntry, NavGroupsLinkEntry },
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
    group () {
      const homeRegion = DataUser.getters.getHomeRegion()
      let entrys = []
      if (this.isWorkGroup) {
        entrys = groupsData.getters.get().slice().sort((a, b) => {
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
