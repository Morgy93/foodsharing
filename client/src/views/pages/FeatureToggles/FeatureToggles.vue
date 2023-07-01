<template>
  <FoodsharingControllerPageWrapper>
    <div class="container">
      <h4>
        FeatureToggles
      </h4>
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
                >{{ getToggleStateDescription(featureToggle.isActive) }}
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
  </FoodsharingControllerPageWrapper>
</template>

<script>
import { fetchAllFeatureToggles, switchFeatureToggleState } from '@/api/featuretoggles'
import FoodsharingControllerPageWrapper from '@/views/pages/FoodsharingControllerPageWrapper.vue'

export default {
  components: { FoodsharingControllerPageWrapper },
  data () {
    return {
      featureToggles: [],
    }
  },
  async mounted () {
    await this.fetchAllFeatureToggles()
  },
  methods: {
    getToggleStateDescription (state) {
      return state ? 'on' : 'off'
    },
    async fetchAllFeatureToggles () {
      const response = await fetchAllFeatureToggles()
      this.featureToggles = response.featureToggles
    },
    async toggle (featureToggleIdentifier) {
      await switchFeatureToggleState(featureToggleIdentifier)
      await this.fetchAllFeatureToggles()
    },
  },
}
</script>
