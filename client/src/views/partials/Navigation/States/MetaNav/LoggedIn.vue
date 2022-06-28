<template>
  <div class="metanav-container container">
    <ul
      v-if="!viewIsMobile"
      class="metanav"
    >
      <NavItem
        v-for="(entry, idx) of metaNav"
        :key="idx"
        :entry="entry"
      />
      <NavAdmin />
    </ul>
  </div>
</template>

<script>
// Data
import MetaNavData from '../../Data/MetaNavData.json'
//
import NavItem from '@/components/Navigation/_NavItems/NavItem'
import NavAdmin from '@/components/Navigation/Admin/NavAdmin'
//
import RouteAndDeviceCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    NavItem,
    NavAdmin,
  },
  mixins: [MediaQueryMixin, RouteAndDeviceCheckMixin],
  data () {
    return {
      metaNav: MetaNavData,
    }
  },
}
</script>
