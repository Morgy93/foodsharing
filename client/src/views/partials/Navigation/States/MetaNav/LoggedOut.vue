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
    </ul>
  </div>
</template>

<script>
// Data
import MetaNavData from '../../Data/MetaNavData.json'
//
import NavItem from '@/components/Navigation/_NavItems/NavItem'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    NavItem,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      metaNav: MetaNavData.filter(entry => !entry.internal),
    }
  },
}
</script>
