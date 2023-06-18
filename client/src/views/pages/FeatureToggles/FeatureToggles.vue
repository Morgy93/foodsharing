<template>
  <div class="container">
    New Page
  </div>
</template>

<script>
import { fetchAllFeatureToggles } from '@/api/featuretoggles'

export default {
  methods: {
    data () {
      return {
        featureToggles: null,
      }
    },
    async fetchAllFeatureToggles () {
      try {
        this.featureToggles = fetchAllFeatureToggles
      } catch {

      }
    },
  },
}
</script>

<style scoped lang="scss">

</style>
