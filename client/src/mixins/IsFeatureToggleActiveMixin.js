import { fetchFeatureToggle } from '@/api/featuretoggles'

export default {
  methods: {
    async isFeatureToggleActive (featureToggleIdentifier) {
      const response = await fetchFeatureToggle(featureToggleIdentifier)
      return response.isActive
    },
  },
}
