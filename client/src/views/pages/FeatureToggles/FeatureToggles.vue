<template>
  <div class="container">
    {{ featureToggles }}
  </div>
</template>

<script>
import { fetchAllFeatureToggles } from '@/api/featuretoggles'

export default {
  data () {
    return {
      featureToggles: [],
    }
  },
  async mounted () {
    await this.fetchAllFeatureToggles()
  },
  methods: {
    async fetchAllFeatureToggles () {
      try {
        this.featureToggles = await fetchAllFeatureToggles()
        console.log('test-featureToggles', this.featureToggles)
      } catch {

      }
    },
  },
}
</script>

<style scoped lang="scss">

</style>
