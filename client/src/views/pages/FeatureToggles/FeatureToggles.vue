<template>
  <div class="container">
    <h4>FeatureToggles</h4>
    <ul class="list-group pt-2">
      <li
        v-for="featureToggle in featureToggles"
        :key="featureToggle.identifier"
        class="list-group-item"
      >
        <b-row>
          <b-col
            cols="12"
            md="10"
          >
            <h4
              class="text-break"
            >
              {{ featureToggle.identifier }}
              <span
                class="badge badge-secondary"
                :class="[featureToggle.isActive ? 'bg-success' : 'bg-danger']"
              >{{ toggleState(featureToggle.isActive) }}
              </span>
            </h4>
          </b-col>
          <b-col
            cols="12"
            md="2"
          >
            <button
              type="button"
              class="btn btn-secondary"
              :disabled="!featureToggle.isToggable"
              @click="toggle(featureToggle.identifier)"
            >
              Toggle
            </button>
          </b-col>
        </b-row>
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
    toggleState (value) {
      return value ? 'aktiv' : 'inaktiv'
    },
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
