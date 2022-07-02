<template>
  <ul class="metanav">
    <Logo v-if="viewIsMD" />
    <NavItem
      v-for="(entry, idx) of metaNav"
      :key="idx"
      :entry="entry"
    />
    <NavAdmin />
  </ul>
</template>

<script>
// Data
import MetaNavData from '../../Data/MetaNavData.json'
//
import Logo from '@/components/Navigation/Logo'
import NavItem from '@/components/Navigation/_NavItems/NavItem'
import NavAdmin from '@/components/Navigation/Admin/NavAdmin'
//
import RouteAndDeviceCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
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
