<template>
  <ul class="metanav-container container">
    <ul
      v-if="!viewIsMobile"
      class="metanav"
    >
      <Link
        v-for="(link, idx) of metaNav"
        :key="idx"
        :title="$i18n(link.title)"
        :href="$url(link.url)"
      />
      <NavAdmin />
    </ul>
  </ul>
</template>

<script>
// Data
import MetaNavData from '../../MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
import NavAdmin from '@/components/Navigation/Admin/NavAdmin'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Link,
    NavAdmin,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      navIsSmall: false,
      metaNav: MetaNavData,
    }
  },
}
</script>
