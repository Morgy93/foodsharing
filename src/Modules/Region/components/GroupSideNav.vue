<template>
  <div class="container">
    <NavGroupsLinkEntry
      :entry="group"
    />
  </div>
</template>

<script>
import NavGroupsLinkEntry from '@/components/Navigation/Groups/NavGroupsLinkEntry.vue'
import DataUser from '@/stores/user'
import groupsData from '@/stores/groups'

export default {
  components: { NavGroupsLinkEntry },
  props: {
    groupId: { type: Number, required: true },
  },
  computed: {
    group () {
      const homeRegion = DataUser.getters.getHomeRegion()
      let entrys = []
      entrys = groupsData.getters.get().slice().sort((a, b) => {
        if (a.id === homeRegion) return -1
        if (b.id === homeRegion) return 1
        else return a.name.localeCompare(b.name)
      })
      return entrys.find(group => group.id === this.groupId)
    },
  },
}
</script>
