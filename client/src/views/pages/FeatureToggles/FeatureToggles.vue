<template>
  <div class="container">
    <ul class="list-group">
      <li
        v-for="featureToggle in featureToggles"
        :key="featureToggle.identifier"
        class="list-group-item"
      >
        {{ featureToggle.identifier }} / {{ featureToggle.isActive }}
        <button
          type="button"
          class="btn btn-primary"
          :disabled="!featureToggle.isToggable"
          @click="toggle(featureToggle.identifier)"
        >
          Toggle
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import { fetchAllFeatureToggles, switchFeatureToggleState } from '@/api/featuretoggles'

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
        const response = await fetchAllFeatureToggles()
        this.featureToggles = response.featureToggles
        console.log('test-featureToggles', this.featureToggles)
      } catch {

      }
    },
    async toggle (identifier) {
      try {
        await switchFeatureToggleState(identifier)
        await this.fetchAllFeatureToggles()
      } catch {

      }
    },
  },
}
</script>

<style scoped lang="scss">

</style>
