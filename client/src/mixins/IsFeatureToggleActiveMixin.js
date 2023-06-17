import { fetchFeatureToggle } from '@/api/featuretoggles'

export default {
  methods: {
    async isFeatureToggleActive (featureToggleIdentifier) {
      const response = fetchFeatureToggle(featureToggleIdentifier)
      const data = await response.json()
      return data.isActive === true
    },
  },
}
