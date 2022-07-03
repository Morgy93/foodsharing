<template>
  <ul class="metanav">
    <Logo v-if="!viewIsMobile" />
    <NavItem
      v-for="(entry, idx) of metaNav"
      :key="idx"
      :entry="entry"
    />
  </ul>
</template>

<script>
// Data
import MetaNavData from '../../Data/MetaNavData.json'
//
import Logo from '@/components/Navigation/Logo'
import NavItem from '@/components/Navigation/_NavItems/NavItem'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Logo,
    NavItem,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      metaNav: MetaNavData.filter(m => !m.isInternal),
    }
  },
}
</script>
