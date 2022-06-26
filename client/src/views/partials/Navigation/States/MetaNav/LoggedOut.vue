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
    </ul>
  </ul>
</template>

<script>
// Data
import MetaNavData from '../../MetaNavData.json'
//
import Link from '@/components/Navigation/_NavItems/NavLink'
//
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Link,
  },
  mixins: [MediaQueryMixin],
  data () {
    return {
      metaNav: MetaNavData,
    }
  },
}
</script>
